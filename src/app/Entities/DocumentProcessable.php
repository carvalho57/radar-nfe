<?php

namespace App\Entities;

interface DocumentProcessable
{
    public function __construct(Documento $documento);
    public function processar(): bool;
}
