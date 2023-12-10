<?php

namespace App\Entities;

use PDO;

class ProcEvento extends Entity implements DocumentProcessable
{
    protected int $nsu;
    protected string $chaveAcesso;
    protected string $protocolo;
    private Documento $documento;

    public function __construct(Documento $documento)
    {
        parent::__construct();
        $this->documento = $documento;
        $this->loadXML($this->documento->extrairConteudo());
    }

    public function loadXML(string $xml)
    {
        $dom = new \DOMDocument();
        $dom->loadXML($xml);

        $retEvento = $dom->getElementsByTagName('retEvento')->item(0);
        $infProt = $retEvento->getElementsByTagName('infEvento')->item(0);

        $this->chaveAcesso = $infProt->getElementsByTagName('chNFe')->item(0)->nodeValue;
        $this->protocolo = $infProt->getElementsByTagName('nProt')->item(0)->nodeValue;
    }


    public function processar(): bool
    {
        $statement = $this->pdo()->query("SELECT IDEVENTO FROM EVENTO WHERE CHAVE_ACESSO = {$this->chaveAcesso}");
        $row = $statement->fetch();

        if ($statement->rowCount() === 0) {
            return false;
        }

        try {
            $updateNFe = 'UPDATE EVENTO SET XML = :xml WHERE IDEVENTO = :id';

            $statement = $this->pdo()->prepare($updateNFe);
            $statement->bindValue(':xml', $this->documento->conteudo, PDO::PARAM_STR);
            $statement->bindValue(':id', $row['IDEVENTO'], PDO::PARAM_INT);

            return $statement->execute();
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), $e->getCode());
        }
    }
}
