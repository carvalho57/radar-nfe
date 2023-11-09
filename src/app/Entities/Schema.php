<?php

namespace App\Entities;

interface Schema
{
    public static function fromXML(int $nsu, string $xml): static;
    public function processar(): bool;
}
