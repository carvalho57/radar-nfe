<?php

namespace App\Entities;

use App\Entities\Enums\Schema;
use App\Helpers\EncodeHelper;

class Documento extends Entity
{
    public readonly int $nsu;
    public readonly Schema $schema;
    public readonly string $conteudo;
    private bool $processado;

    public function __construct(int $nsu, Schema $schema, string $conteudo)
    {
        parent::__construct();
        $this->nsu = $nsu;
        $this->schema = $schema;
        $this->conteudo = $conteudo;
        $this->processado = false;
    }

    public function extrairConteudo(): string
    {
        return EncodeHelper::decode($this->conteudo);
    }

    public function setProcessado(): static
    {
        $this->processado = true;
        return $this;
    }

    public function update(): bool
    {
        $query = 'UPDATE DOCUMENTO 
                    SET NSU = :nsu, 
                        DOC_SCHEMA = :schema, 
                        CONTEUDO = :conteudo, 
                        PROCESSADO = :processado
                    WHERE IDDOCUMENTO = :id';

        try {
            $statement = $this->pdo()->prepare($query);
            $statement->execute([
                ':id' => $this->id,
                ':nsu' => $this->nsu,
                ':schema' => $this->schema->value,
                ':conteudo' => $this->conteudo,
                ':processado' => (int)$this->processado,
            ]);
        } catch (\PDOException $e) {
            throw $e;
        }

        return $statement->rowCount() > 0;
    }

    public function insert(): bool
    {
        $query = 'INSERT INTO DOCUMENTO (NSU, DOC_SCHEMA, CONTEUDO, PROCESSADO) 
                            VALUES(:nsu, :schema, :conteudo, :processado)';

        try {
            $statement = $this->pdo()->prepare($query);
            $statement->execute([
                ':nsu' => $this->nsu,
                ':schema' => $this->schema->value,
                ':conteudo' => $this->conteudo,
                ':processado' => (int)$this->processado,
            ]);
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), $e->getCode());
        }

        $this->id = $this->pdo()->lastInsertId();

        return $this->id > 0;
    }


    /**
     * @param Documento[] $documentos
     * @return bool
     */
    public static function saveDocumentos(array $documentos)
    {
        $query = 'INSERT INTO DOCUMENTO (NSU, DOC_SCHEMA, CONTEUDO) 
                            VALUES(:nsu, :schema, :conteudo)';

        parent::pdo()->beginTransaction();

        try {
            $statement = parent::pdo()->prepare($query);
            foreach ($documentos as $documento) {
                if ($documento->id > 0) {
                    continue;
                }

                $statement->execute([
                    ':nsu' => $documento->nsu,
                    ':schema' => $documento->schema->value,
                    ':conteudo' => $documento->conteudo,
                ]);

                $documento->id = parent::pdo()->lastInsertId();
            }
        } catch (\PDOException $e) {
            parent::pdo()->rollBack();
            return false;
        }


        return parent::pdo()->commit();
    }



    public static function getByNSURange(int $inicioNSU, int $finalNSU)
    {
        if ($inicioNSU > $finalNSU) {
            return [];
        }

        $query = 'SELECT IDDOCUMENTO, NSU, DOC_SCHEMA, CONTEUDO, PROCESSADO, DATA_CRIACAO
                        FROM DOCUMENTO
                    WHERE NSU >= :inicioNSU AND NSU <= :finalNSU';

        $statement = parent::pdo()->prepare($query);
        $statement->execute([':inicioNSU' => $inicioNSU, ':finalNSU' => $finalNSU]);

        $documentos = [];
        foreach ($statement->fetchAll() as $data) {
            $documentos[] = self::createFromArray($data);
        }

        return $documentos;
    }

    public static function getById(int $id): ?Documento
    {
        if ($id == 0) {
            return null;
        }

        $query = 'SELECT IDDOCUMENTO, NSU, DOC_SCHEMA, CONTEUDO, PROCESSADO
                        FROM DOCUMENTO
                    WHERE IDDOCUMENTO = :id';

        $statement = parent::pdo()->prepare($query);
        $statement->execute([':id' => $id]);

        $data = $statement->fetch();

        return self::createFromArray($data);
    }

    private static function createFromArray(array $data)
    {
        $newDocumento = new Documento($data['NSU'], Schema::from($data['DOC_SCHEMA']), $data['CONTEUDO']);
        $newDocumento->id = $data['IDDOCUMENTO'];
        $newDocumento->processado = (bool) $data['PROCESSADO'];

        return $newDocumento;
    }
}
