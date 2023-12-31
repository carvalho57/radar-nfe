<?php

declare(strict_types=1);

namespace App\Handlers;

use App\Entities\Documento;
use App\Entities\Enums\EventoManifestacao;
use App\Entities\Enums\RespostaSEFAZ;
use App\Entities\Enums\Schema;
use App\Entities\Historico;
use App\Entities\Manifestacao;
use App\Entities\ProcEvento;
use App\Entities\ProcNFe;
use App\Entities\Resposta;
use App\Entities\ResumoEvento;
use App\Entities\ResumoNFe;
use App\Entities\RetornoDistDFe;
use App\Services\ISefazDistDFeService;
use Exception;
use NFePHP\NFe\Common\Standardize;
use NFePHP\NFe\Tools;

class RadarNFeHandler
{
    private Tools $tools;
    private ISefazDistDFeService $sefazDistDFeService;

    public function __construct(ISefazDistDFeService $sefazDistDFeService)
    {
        $this->sefazDistDFeService = $sefazDistDFeService;
    }

    public function buscarDocumentos(int $nsu): Resposta
    {
        try {
            $resposta = $this->sefazDistDFeService->consultaUltimoNSU($nsu);
        } catch (Exception $e) {
            return Resposta::falha("Erro ao comunicar com a SEFAZ, tente novamente mais tarde. Erro: {$e->getMessage()}");
        }

        $retornoDistDFe = RetornoDistDFe::fromXML($resposta);

        Historico::createFromDistDFe($nsu, $retornoDistDFe)->gravar();

        if ($retornoDistDFe->status === RespostaSEFAZ::NENHUM_DOCUMENTO_LOCALIZADO->value) {
            return Resposta::falha('Nenhum documento localizado', $retornoDistDFe);
        }

        if ($retornoDistDFe->status === RespostaSEFAZ::CONSUMO_INDEVIDO->value) {
            return Resposta::falha('Consumo indevido', $retornoDistDFe);
        }

        $documentos = $retornoDistDFe->mapearDocumentos();

        if (empty($documentos)) {
            return Resposta::falha('Não foi possível recuperar os documentos da resposta');
        }

        $salvo = Documento::saveDocumentos($documentos);

        if (!$salvo) {
            return Resposta::falha('Erro ao salvar os documentos');
        }

        $processado = $this->processarDocumentos($documentos);

        if (!$processado) {
            return Resposta::falha('Houve erro no processamento dos documentos, tente novamente', $retornoDistDFe);
        }

        return Resposta::sucesso('Documentos localizados', $retornoDistDFe);
    }

    /**
     * Processa os documentos informados
     * @param Documento[] $documentos
     * @throws \Exception
     * @return bool
     */
    public function processarDocumentos(array $documentos): bool
    {
        if (empty($documentos)) {
            return false;
        }

        $processouTodosDocumentos = true;

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

            $documentoSchema = new $entity($documento);
            #TODO Armazenar o estado do processamento
            #TODO Quais documentos não foram processados
            $processado = $documentoSchema->processar();

            if ($processado) {
                $documento->setProcessado();
                $documento->update();
            }

            if ($processouTodosDocumentos && !$processado) {
                $processouTodosDocumentos = false;
            }
        }


        return $processouTodosDocumentos;
    }


    public function manifestarNota(ResumoNFe $nfe, EventoManifestacao $evento, string $justificativa = ''): Resposta
    {
        if ($evento === EventoManifestacao::NAO_REALIZADA && empty($justificativa)) {
            return Resposta::falha('É necessário informar a justificativa para o Evento de Operação não realizada');
        }

        $resposta = $this->sefazDistDFeService->manifestar($nfe->chaveAcesso, $evento->value, $justificativa);

        $eventoManitesfacao = new Standardize($resposta);
        $eventoManitesfacao = $eventoManitesfacao->toStd();

        # Lote processado
        if ($eventoManitesfacao->cStat !== 128) {
            return Resposta::falha("Rejeição: {$eventoManitesfacao->cStat} - {$eventoManitesfacao->xMotivo}");
        }

        $manifestacao = Manifestacao::createFromXML($resposta);

        $nfe->vincularManifestacao($manifestacao);

        return Resposta::sucesso('Manifestação realizada com sucesso', $nfe->nsu);
    }
}
