<?php

namespace App\Entities;

class ProcNFe extends Entity
{
    protected int $nsu;

    public function fromXML($xml): static
    {
        return (new static());
    }

    public function processar(Documento $documento): bool
    {
        return false;
    }
}
