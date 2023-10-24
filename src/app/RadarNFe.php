<?php

declare(strict_types=1);

namespace App;

use App\Entities\Enums\RespostaSEFAZ;
use App\Entities\Enums\Schema;
use App\Entities\EventoResumoModel;
use App\Entities\NFeResumoModel;
use App\Entities\RetornoDistDFe;
use App\Models\Resposta;
use NFePHP\Common\Certificate;
use NFePHP\NFe\Tools;

class RadarNFe
{
    private Tools $tools;

    public function __construct(Config $config)
    {
        $configJson = json_encode([
            'tpAmb' => 1,
            'razaosocial' => 'SUA RAZAO SOCIAL LTDA',
            'cnpj' => $config->empresa('cnpj'),
            'siglaUF' => $config->empresa('uf'),
            'versao' => '4.00',
        ]);

        $contentCertificado = file_get_contents($config->certificado('path'));

        if (empty($contentCertificado)) {
            throw new \Exception('Não foi possível ler o certificado');
        }

        $this->tools = new Tools($configJson, Certificate::readPfx($contentCertificado, $config->certificado('password')));
        $this->tools->model(55);
    }

    public function buscaDocumentos(int $ultimoNSU): Resposta
    {
        try {
            $resposta = $this->tools->sefazDistDFe($ultimoNSU);
        } catch (\Exception $e) {
            // log()
            return Resposta::falha("Erro ao comunicar com a SEFAZ, tente novamente mais tarde. Erro: {$e->getMessage()}");
        }

        $retornoDistDFe = RetornoDistDFe::fromXML($resposta);

        if ($retornoDistDFe->status === RespostaSEFAZ::NENHUM_DOCUMENTO_LOCALIZADO) {
            return Resposta::sucesso('Nenhum documento localizado', $retornoDistDFe);
        }

        if ($retornoDistDFe->status === RespostaSEFAZ::CONSUMO_INDEVIDO) {
            return Resposta::falha('Consumo indevido', $retornoDistDFe);
        }

        $documentos = $retornoDistDFe->lote->getElementsByTagName('docZip');


        $documentosProcessados = array_map(function ($documento) {
            $nsu = $documento->getAttribute('NSU');
            $schema = explode('_', $documento->getAttribute('schema'))[0];
            $content = gzdecode(base64_decode($documento->nodeValue));

            return $this->processaDocumento($nsu, $schema, $content);
        }, $documentos);

        return Resposta::sucesso('Encontrado documentos', $documentosProcessados);
    }

    private function processaDocumento(int $nsu, string $schema, string $content)
    {
        $documento = match ($schema) {
            Schema::RESUMO_NFE => NFeResumoModel::fromXML($nsu, $content),
            Schema::RESUMO_EVENTO => EventoResumoModel::fromXML($nsu, $content),
        };

        return $documento;
    }

    public function manifestarNota(NFeResumoModel $nota)
    {
    }
}
