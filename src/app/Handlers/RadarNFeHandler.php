<?php

declare(strict_types=1);

namespace App\Handlers;

use App\Entities\Documento;
use App\Entities\Enums\EventoManifestacao;
use App\Entities\Enums\RespostaSEFAZ;
use App\Entities\Enums\Schema;
use App\Entities\ProcEvento;
use App\Entities\ProcNFe;
use App\Entities\ResumoEvento;
use App\Entities\ResumoNFe;
use App\Entities\RetornoDistDFe;
use App\Models\Resposta;
use App\Services\ISefazDistDFeService;
use Exception;
use NFePHP\NFe\Tools;

class RadarNFeHandler
{
    private Tools $tools;
    private ISefazDistDFeService $sefazDistDFeService;

    public function __construct(
        ISefazDistDFeService $sefazDistDFeService,
    ) {
        $this->sefazDistDFeService = $sefazDistDFeService;
    }

    public function buscarDocumentos(int $nsu): Resposta
    {
        try {
            $resposta = $this->sefazDistDFeService->consultaNSU($nsu);
        } catch (Exception $e) {
            return Resposta::falha("Erro ao comunicar com a SEFAZ, tente novamente mais tarde. Erro: {$e->getMessage()}");
        }

        $retornoDistDFe = RetornoDistDFe::fromXML($resposta);

        if ($retornoDistDFe->status === RespostaSEFAZ::NENHUM_DOCUMENTO_LOCALIZADO) {
            return Resposta::falha('Nenhum documento localizado', $retornoDistDFe);
        }

        if ($retornoDistDFe->status === RespostaSEFAZ::CONSUMO_INDEVIDO) {
            return Resposta::falha('Consumo indevido', $retornoDistDFe);
        }

        $documentos = $retornoDistDFe->mapearDocumentos();

        if (empty($documentos)) {
            return Resposta::falha('Não foi possível recuperar os documentos da resposta');
        }

        # Multiplos inserts, já salva todos os documentos
        // $this->documentoRepository->salvarDocumentos($documentos);
        Documento::saveDocumentos($documentos);

        # Processa os documentos, salvando e vinculando eles
        $processado = $this->processarDocumentos($nsu, $retornoDistDFe->ultimoNSU);

        if (!$processado) {
            return Resposta::sucesso('Documentos localizados', $retornoDistDFe);
        }

        return Resposta::sucesso('Documentos localizados', $retornoDistDFe);
    }

    //Fazer as validações nos documentos
    //Salvar no banco caso não exista
    //Fazer os vinculos resumo_nfe e proc_nfe

    public function processarDocumentos(int $inicioNSU, int $finalNSU): bool
    {
        //Faz validações
        if ($inicioNSU > $finalNSU) {
            return false;
        }

        $documentos = Documento::getByNSURange($inicioNSU, $finalNSU);

        if (empty($documentos)) {
            return false;
        }

        /** @var Documento $documento */
        foreach ($documentos as $documento) {
            $entity = match ($documento->schema) {
                Schema::RESUMO_NFE => ResumoNFe::class,
                Schema::PROC_NFE => ProcNFe::class,
                Schema::RESUMO_EVENTO => ResumoEvento::class,
                Schema::PROC_EVENTO => ProcEvento::class,
                default => null
            };

            if ($entity === null) {
                throw new Exception('Não foi possível carregar o documento');
            }

            $documentoExtraido = $entity::fromXML($documento->nsu, $documento->extrairConteudo());

            $processed = $documentoExtraido->processar($documento);

            if (!$processed) {
                return false;
            }
        }


        return true;
    }


    public function manifestarNota(Documento $documento, EventoManifestacao $evento)
    {
    }
}
