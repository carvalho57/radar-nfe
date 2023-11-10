<?php

declare(strict_types=1);

namespace App\Services;

use App\Config;
use NFePHP\Common\Certificate;
use NFePHP\NFe\Tools;

class SefazDistDFeService implements ISefazDistDFeService
{
    private ?Tools $tools = null;

    public function __construct(Config $config)
    {
        $configJson = json_encode([
            'tpAmb' => (int)$config->empresa('ambiente'),
            'razaosocial' => $config->empresa('razaoSocial'),
            'cnpj' => $config->empresa('cnpj'),
            'siglaUF' => $config->empresa('uf'),
            'schemes' => 'PL_009_V4',
            'versao' => '4.00',
        ]);

        $caminhoCertificado = $config->certificado('path');
        $senhaCertificado = $config->certificado('password');


        if (!file_exists($caminhoCertificado) || !is_readable($caminhoCertificado)) {
            throw new \Exception('Não foi possível ler o certificado');
        }

        $conteudoCertificado = file_get_contents($caminhoCertificado);

        $this->tools = new Tools($configJson, Certificate::readPfx($conteudoCertificado, $senhaCertificado));
        $this->tools->model(55);
    }
    public function consultaUltimoNSU(int $ultimoNSU): string
    {
        return $this->tools->sefazDistDFe($ultimoNSU);
    }

    public function consultaNSU(int $numeroNSU): string
    {
        return $this->tools->sefazDistDFe(numNSU: $numeroNSU);
    }

    public function consultaChave(string $chave): string
    {
        return $this->tools->sefazDistDFe(chave: $chave);
    }

    public function manifestar(string $chave, int $tipoEvento, string $justificativa = ''): string
    {
        return $this->tools->sefazManifesta($chave, $tipoEvento, $justificativa);
    }
}
