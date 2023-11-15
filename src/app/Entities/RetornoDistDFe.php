<?php

declare(strict_types=1);

namespace App\Entities;

use App\Entities\Enums\Schema;

class RetornoDistDFe
{
    public readonly string $ambiente;
    public readonly string $status;
    public readonly string $motivo;
    public readonly string $dataResposta;
    public readonly int $ultimoNSU;
    public readonly int $maxNSU;
    public readonly string $resposta;
    private $lote;

    private array $documentosMapeados;

    public function __construct()
    {
        $this->documentosMapeados = [];
    }

    public static function fromXML(string $xml): RetornoDistDFe
    {
        $dom = new \DOMDocument();
        $dom->loadXML($xml);

        $node = $dom->getElementsByTagName('retDistDFeInt')->item(0);

        $retorno = new RetornoDistDFe();
        $retorno->ambiente = $node->getElementsByTagName('tpAmb')->item(0)->nodeValue;
        $retorno->status = $node->getElementsByTagName('cStat')->item(0)->nodeValue;
        $retorno->motivo = $node->getElementsByTagName('xMotivo')->item(0)->nodeValue;
        $retorno->dataResposta = $node->getElementsByTagName('dhResp')->item(0)->nodeValue;
        $retorno->ultimoNSU = (int)$node->getElementsByTagName('ultNSU')->item(0)->nodeValue;
        $retorno->maxNSU = (int)$node->getElementsByTagName('maxNSU')->item(0)->nodeValue;
        $retorno->lote = $node->getElementsByTagName('loteDistDFeInt')?->item(0);
        $retorno->resposta = $xml;
        return $retorno;
    }

    public function mapearDocumentos(): array
    {
        if (!empty($this->documentosMapeados)) {
            return $this->documentosMapeados;
        }

        $documentos = $this->lote->getElementsByTagName('docZip');

        if ($documentos === null) {
            return [];
        }

        $documentosProcessados = [];

        foreach ($documentos as $documento) {
            $nsu = (int)$documento->getAttribute('NSU');
            $schema = explode('_', $documento->getAttribute('schema'))[0];
            $content = $documento->nodeValue;

            $documentosProcessados[] = new Documento($nsu, Schema::from($schema), $content);
        }

        $this->documentosMapeados = $documentosProcessados;

        return $documentosProcessados;
    }

    public function getDocumentos()
    {
        return $this->documentosMapeados;
    }
}
