<?php

declare(strict_types=1);

namespace App\Entities\Enums;

enum Ambiente
{
    case PRODUCAO = 1;
    case HOMOLOGACAO = 2;
}
