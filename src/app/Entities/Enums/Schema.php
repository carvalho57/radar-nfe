<?php

declare(strict_types=1);

namespace App\Entities\Enums;

enum Schema: string
{
    case RESUMO_NFE = 'resNFe';
    case PROC_NFE = 'procNFe';
    case RESUMO_EVENTO = 'resEvento';
    case PROC_EVENTO = 'procEventoNFe';
}
