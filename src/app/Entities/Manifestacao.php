<?php

namespace App\Entities;

class Manifestacao extends Entity
{
    public readonly string $status;
    public readonly string $motivo;
    public readonly string $tipoEvento;
    public readonly string $descricaoEvento;
    public readonly string $chaveAcesso;
    public readonly string $protocolo;
    public readonly \DateTime $dataRecebimento;
    public readonly int $sequenciaEvento;

    public function __construct()
    {
        parent::__construct();
    }

    public static function createFromXML(string $xml): static
    {
        return new static();
    }
}
