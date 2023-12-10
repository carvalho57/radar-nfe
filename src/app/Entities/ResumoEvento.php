<?php

namespace App\Entities;

use PDOException;

class ResumoEvento extends Entity implements DocumentProcessable
{
    private int $nsu;
    private string $cnpj;
    private string $cpf;
    private string $chaveAcesso;
    private string $protocolo;
    private int $sequencia;
    private string $tipoEvento;
    private string $descricaoEvento;
    private \DateTime $dataEmissao;
    private \DateTime $dataRecebimento;
    private Documento $documento;

    public function __construct(Documento $documento)
    {
        parent::__construct();
        $this->documento = $documento;
        $this->nsu = $documento->nsu;
        $this->loadXML($this->documento->extrairConteudo());
    }

    public function loadXML(string $xml): static
    {
        $dom = new \DOMDocument();
        $dom->loadXML($xml);

        $node = $dom->getElementsByTagName('resEvento')->item(0);

        $this->cnpj = $node->getElementsByTagName('CNPJ')?->item(0)->nodeValue ?? '';
        $this->cpf = $node->getElementsByTagName('CPF')?->item(0)->nodeValue ?? '';
        $this->chaveAcesso = $node->getElementsByTagName('chNFe')->item(0)->nodeValue;
        $this->sequencia = $node->getElementsByTagName('nSeqEvento')->item(0)->nodeValue;
        $this->tipoEvento = $node->getElementsByTagName('tpEvento')->item(0)->nodeValue;
        $this->descricaoEvento = $node->getElementsByTagName('xEvento')->item(0)->nodeValue;
        $this->protocolo = $node->getElementsByTagName('nProt')->item(0)->nodeValue;
        $this->dataEmissao = new \DateTime($node->getElementsByTagName('dhEvento')->item(0)->nodeValue);
        $this->dataRecebimento = new \DateTime($node->getElementsByTagName('dhRecbto')->item(0)->nodeValue);

        return $this;
    }

    public function processar(): bool
    {
        $query = 'INSERT INTO EVENTO ( NSU, CHAVE_ACESSO, CNPJ, CPF, DATA_EVENTO, TIPO_EVENTO, SEQUENCIA, DESCRICAO_EVENTO, DATA_AUTORIZACAO, PROTOCOLO, ID_DOCUMENTO) 
                    VALUE (:NSU, :CHAVE_ACESSO, :CNPJ, :CPF, :DATA_EVENTO, :TIPO_EVENTO, :SEQUENCIA, :DESCRICAO_EVENTO, :DATA_AUTORIZACAO, :PROTOCOLO, :ID_DOCUMENTO)';

        try {
            $statement = $this->pdo()->prepare($query);
            $statement->execute(
                [
                    ':NSU' => $this->nsu,
                    ':CHAVE_ACESSO' => $this->chaveAcesso,
                    ':CNPJ' => $this->cnpj,
                    ':CPF' => $this->cpf,
                    ':DATA_EVENTO' => $this->dataEmissao->format('Y-m-d H:i:s'),
                    ':TIPO_EVENTO' => $this->tipoEvento,
                    ':SEQUENCIA' => $this->sequencia,
                    ':DESCRICAO_EVENTO' => $this->descricaoEvento,
                    ':DATA_AUTORIZACAO' => $this->dataRecebimento->format('Y-m-d H:i:s'),
                    ':PROTOCOLO' => $this->protocolo,
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
}
