<?php

declare(strict_types=1);

namespace App\Entities\Enums;

enum RespostaSEFAZ
{
    case NENHUM_DOCUMENTO_LOCALIZADO = '137';
    case DOCUMENTO_LOCALIZADO = '138';
    case CONSUMO_INDEVIDO = '656';
}
