<?php

declare(strict_types=1);

namespace App\Entities\Enums;

enum TipoOperacao: int
{
    case ENTRADA = 0;
    case SAIDA = 1;
}
