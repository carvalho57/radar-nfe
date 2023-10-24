<?php

declare(strict_types=1);

namespace App;

class Config
{
    private array $db = [];
    private array $certificado = [];
    private array $empresa = [];

    public function __construct(array $config)
    {
        $this->db = [
            'username' => $config['DB_USERNAME'],
            'password' => $config['DB_PASSWORD'],
            'database' => $config['DB_DATABASE'],
            'host' => $config['DB_HOST'],
        ];

        $this->empresa = [
            'cnpj' => $config['CNPJ'],
            'uf' => $config['UF'],
        ];

        $this->certificado = [
            'path' => $config['CERTIFICATE_PATH'],
            'password' => $config['CERTIFICATE_PASSWORD'],
        ];
    }

    public function db(string $propriedade)
    {
        if (!empty($propriedade)) {
            return $this->db[$propriedade] ?? '';
        }

        return $this->db;
    }
    public function certificado(string $propriedade): string|array
    {
        if (!empty($propriedade)) {
            return $this->db[$propriedade] ?? '';
        }

        return $this->db;
    }

    public function empresa(string $propriedade)
    {
        if (!empty($propriedade)) {
            return $this->empresa[$propriedade] ?? '';
        }
        return $this->empresa;
    }
}
