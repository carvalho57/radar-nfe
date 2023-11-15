<?php

namespace App\Entities;

use PDO;

class ProcNFe extends Entity implements DocumentProcessable
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

        $infProt = $dom->getElementsByTagName('infProt')->item(0);
        $this->chaveAcesso = $infProt->getElementsByTagName('chNFe')->item(0)->nodeValue;
        $this->protocolo = $infProt->getElementsByTagName('nProt')->item(0)->nodeValue;
    }

    public function processar(): bool
    {
        $statement = $this->pdo()->query("SELECT IDNFE FROM NFE WHERE CHAVE_ACESSO = '{$this->chaveAcesso}'");
        $row = $statement->fetch();

        if ($statement->rowCount() === 0) {
            return false;
        }

        try {
            $updateNFe = 'UPDATE NFE SET XML = :xml WHERE IDNFE = :id';

            $statement = $this->pdo()->prepare($updateNFe);
            $statement->bindValue(':xml', $this->documento->conteudo, PDO::PARAM_STR);
            $statement->bindValue(':id', $row['IDNFE'], PDO::PARAM_INT);


            return $statement->execute();
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), $e->getCode());
        }
    }
}
