<?php

namespace App\Entities;

use App\Entities\Enums\SituacaoNFe;
use App\Entities\Enums\TipoOperacao;
use PDOException;

class ResumoNFe extends Entity implements Schema
{
    private int $nsu;
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
    private Documento $documento;


    public static function fromXML(int $nsu, string $xml): static
    {
        echo $xml;

        $dom = new \DOMDocument();
        $dom->loadXML($xml);


        $node = $dom->getElementsByTagName('resNFe')->item(0);


        $resumo = new static();
        $resumo->nsu = $nsu;
        $resumo->cnpj = $node->getElementsByTagName('CNPJ')?->item(0)->nodeValue ?? '';
        $resumo->cpf = $node->getElementsByTagName('CPF')?->item(0)->nodeValue ?? '';
        $resumo->chaveAcesso = $node->getElementsByTagName('chNFe')->item(0)->nodeValue;
        $resumo->razaoSocial = $node->getElementsByTagName('xNome')->item(0)->nodeValue;
        $resumo->total = (float)$node->getElementsByTagName('vNF')->item(0)->nodeValue;
        $resumo->inscricaoEstadual = $node->getElementsByTagName('IE')->item(0)->nodeValue;
        $resumo->protocolo = $node->getElementsByTagName('nProt')->item(0)->nodeValue;
        $resumo->situacao = SituacaoNFe::from($node->getElementsByTagName('cSitNFe')->item(0)->nodeValue);
        $resumo->tipoOperacao = TipoOperacao::from($node->getElementsByTagName('tpNF')->item(0)->nodeValue);
        $resumo->dataEmissao = new \DateTime($node->getElementsByTagName('dhEmi')->item(0)->nodeValue);
        $resumo->dataRecebimento = new \DateTime($node->getElementsByTagName('dhRecbto')->item(0)->nodeValue);

        return $resumo;
    }
    public function processar(): bool
    {
        $query = 'INSERT INTO NFE (NSU, CHAVE_ACESSO, CNPJ, CPF, RAZAO_SOCIAL, IE, DATA_EMISSAO, DATA_AUTORIZACAO, TIPO_OPERACAO, VALOR_TOTAL, PROTOCOLO, SITUACAO_NFE, ID_DOCUMENTO)
                        VALUES(:NSU, :CHAVE_ACESSO, :CNPJ, :CPF, :RAZAO_SOCIAL, :IE, :DATA_EMISSAO, :DATA_AUTORIZACAO, :TIPO_OPERACAO, :VALOR_TOTAL, :PROTOCOLO, :SITUACAO_NFE, :ID_DOCUMENTO)';

        try {
            $statement = parent::pdo()->prepare($query);

            $statement->execute(
                [
                    ':NSU' => $this->nsu,
                    ':CHAVE_ACESSO' => $this->chaveAcesso,
                    ':CNPJ' => $this->cnpj,
                    ':CPF' => $this->cpf,
                    ':RAZAO_SOCIAL' => $this->razaoSocial,
                    ':IE' => $this->inscricaoEstadual,
                    ':DATA_EMISSAO' => $this->dataEmissao->format('Y-m-d H:i:s'),
                    ':DATA_AUTORIZACAO' => $this->dataRecebimento->format('Y-m-d H:i:s'),
                    ':TIPO_OPERACAO' => $this->tipoOperacao->value,
                    ':VALOR_TOTAL' => $this->total,
                    ':PROTOCOLO' => $this->protocolo,
                    ':SITUACAO_NFE' => $this->situacao->value,
                    ':ID_DOCUMENTO' => $this->documento?->id,
                ]
            );
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), $e->getCode());
        }

        $processed = $statement->rowCount() > 0;

        if (!$processed) {
            return false;
        }


        $this->id = parent::pdo()->lastInsertId();

        return $processed;
    }

    public function setDocumento(Documento $documento)
    {
        $this->documento = $documento;
    }
}
