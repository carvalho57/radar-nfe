<?php

namespace App\Entities;

use App\Entities\Enums\SituacaoNFe;
use App\Entities\Enums\TipoOperacao;
use PDOException;

class ResumoNFe extends Entity implements DocumentProcessable
{
    public readonly int $nsu;
    public readonly string $chaveAcesso;
    public readonly string $razaoSocial;
    public readonly string $cnpj;
    public readonly string $cpf;
    public readonly string $inscricaoEstadual;
    public readonly \DateTime $dataEmissao;
    public readonly \DateTime $dataRecebimento;
    public readonly float $total;
    public readonly string $protocolo;
    public readonly SituacaoNFe $situacao;
    public readonly TipoOperacao $tipoOperacao;
    public readonly Documento $documento;


    public function __construct(Documento $documento)
    {
        parent::__construct();
        $this->documento = $documento;
        $this->nsu = $documento->nsu;
        $this->loadXML($this->documento->extrairConteudo());
    }

    public function loadXML(string $xml): void
    {
        $dom = new \DOMDocument();
        $dom->loadXML($xml);

        $node = $dom->getElementsByTagName('resNFe')->item(0);

        $this->cnpj = $node->getElementsByTagName('CNPJ')?->item(0)->nodeValue ?? '';
        $this->cpf = $node->getElementsByTagName('CPF')?->item(0)->nodeValue ?? '';
        $this->chaveAcesso = $node->getElementsByTagName('chNFe')->item(0)->nodeValue;
        $this->razaoSocial = $node->getElementsByTagName('xNome')->item(0)->nodeValue;
        $this->total = (float)$node->getElementsByTagName('vNF')->item(0)->nodeValue;
        $this->inscricaoEstadual = $node->getElementsByTagName('IE')->item(0)->nodeValue;
        $this->protocolo = $node->getElementsByTagName('nProt')->item(0)->nodeValue;
        $this->situacao = SituacaoNFe::from($node->getElementsByTagName('cSitNFe')->item(0)->nodeValue);
        $this->tipoOperacao = TipoOperacao::from($node->getElementsByTagName('tpNF')->item(0)->nodeValue);
        $this->dataEmissao = new \DateTime($node->getElementsByTagName('dhEmi')->item(0)->nodeValue);
        $this->dataRecebimento = new \DateTime($node->getElementsByTagName('dhRecbto')->item(0)->nodeValue);
    }

    public function processar(): bool
    {
        $insertNFe = 'INSERT INTO NFE (NSU, CHAVE_ACESSO, CNPJ, CPF, RAZAO_SOCIAL, IE, DATA_EMISSAO, DATA_AUTORIZACAO, TIPO_OPERACAO, VALOR_TOTAL, PROTOCOLO, SITUACAO_NFE, ID_DOCUMENTO)
                        VALUES(:NSU, :CHAVE_ACESSO, :CNPJ, :CPF, :RAZAO_SOCIAL, :IE, :DATA_EMISSAO, :DATA_AUTORIZACAO, :TIPO_OPERACAO, :VALOR_TOTAL, :PROTOCOLO, :SITUACAO_NFE, :ID_DOCUMENTO)';

        try {
            $statement = parent::pdo()->prepare($insertNFe);

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

    public function vincularManifestacao(Manifestacao $manifestacao)
    {
        $insertManifestacao = 'INSERT INTO NFE (NSU, CHAVE_ACESSO, CNPJ, CPF, RAZAO_SOCIAL, IE, DATA_EMISSAO, DATA_AUTORIZACAO, TIPO_OPERACAO, VALOR_TOTAL, PROTOCOLO, SITUACAO_NFE, ID_DOCUMENTO)
                        VALUES(:NSU, :CHAVE_ACESSO, :CNPJ, :CPF, :RAZAO_SOCIAL, :IE, :DATA_EMISSAO, :DATA_AUTORIZACAO, :TIPO_OPERACAO, :VALOR_TOTAL, :PROTOCOLO, :SITUACAO_NFE, :ID_DOCUMENTO)';

        try {
            $statement = parent::pdo()->prepare($insertManifestacao);

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
    }
}
