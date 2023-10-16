<?php

declare(strict_types=1);

namespace App\Entities;

use App\Entities\Enums\SituacaoNFe;
use App\Entities\Enums\TipoOperacao;

class NFeResumoModel
{
    private string $chaveAcesso;
    private string $razaoSocial;
    private string $cnpj;
    private string $cpf;
    private string $inscricaoEstadual;
    private \DateTime $dataEmissao;
    private \DateTime $dataRecebimento;
    private float $total;
    private string $protocolo;
    private SituacaoNFe $situacao;
    private TipoOperacao $tipoOperacao;
}
