<?php

declare(strict_types=1);

namespace App\Entities;

class RetornoDistDFe
{
    public readonly string $status;
    public readonly string $motivo;
    public readonly string $dataResposta;
    public readonly string $ultimoNSU;
    public readonly string $maxNSU;
    public $lote;

    public static function fromXML(string $xml): RetornoDistDFe
    {
        $dom = new \DOMDocument();
        $dom->loadXML($xml);

        $node = $dom->getElementsByTagName('retDistDFeInt')->item(0);

        $retorno = new RetornoDistDFe();
        $retorno->status = $node->getElementsByTagName('cStat')->item(0)->nodeValue;
        $retorno->motivo = $node->getElementsByTagName('xMotivo')->item(0)->nodeValue;
        $retorno->dataResposta = $node->getElementsByTagName('dhResp')->item(0)->nodeValue;
        $retorno->ultimoNSU = $node->getElementsByTagName('ultNSU')->item(0)->nodeValue;
        $retorno->maxNSU = $node->getElementsByTagName('maxNSU')->item(0)->nodeValue;
        $retorno->lote = $node->getElementsByTagName('loteDistDFeInt')->item(0);

        return $retorno;
    }
}
