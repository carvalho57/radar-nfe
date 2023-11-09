<?php

declare(strict_types=1);

namespace App\Entities\Enums;

enum EventoManifestacao
{
    case CIENCIA = '210210';
    case CONFIRMACAO = '210200';
    case DESCONHECIMENTO = '210220';
    case NAO_REALIZADA = '210240';
}
