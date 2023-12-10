<?php

declare(strict_types=1);

namespace App\Entities\Enums;

enum SituacaoNFe: int
{
    case AUTORIZADO = 1;
    case DENEGADO = 2;
    case CANCELADA = 3;
}
