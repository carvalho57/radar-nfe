<?php

declare(strict_types=1);

namespace App\Entities;

class EventoResumoModel
{
    private int $nsu;
    private string $cnpj;
    private string $cpf;
    private string $chaveAcesso;
    private \DateTime $dataEmissao;
    private \DateTime $dataRecebimento;
    private string $tipoEvento;
    private int $sequencial;
    private string $descricaoEvento;
    private string $protocolo;

    public static function fromXML(int $nsu, string $xml): static
    {
        return new EventoResumoModel();
    }
}
