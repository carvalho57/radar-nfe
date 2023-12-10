<?php

declare(strict_types=1);

namespace App\Services;

interface ISefazDistDFeService
{
    public function consultaUltimoNSU(int $ultimoNSU): string;

    public function consultaNSU(int $numeroNSU): string;

    public function consultaChave(string $chave): string;

    public function manifestar(string $chave, int $tipoEvento, string $justificativa = ''): string;
}
