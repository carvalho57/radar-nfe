<?php

namespace App\Entities;

use App\Entities\Enums\Schema;

class Documento extends Entity
{
    public readonly int $nsu;
    public readonly Schema $schema;
    public readonly string $conteudo;

    public function __construct(int $nsu, Schema $schema, string $conteudo)
    {
        $this->nsu = $nsu;
        $this->schema = $schema;
        $this->conteudo = $conteudo;
    }

    public function extrairConteudo(): string
    {
        return gzdecode(base64_decode($this->conteudo));
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
                    ':schema' => $documento->schema,
                    ':conteudo' => $documento->conteudo,
                ]);
            }
        } catch (\PDOException $e) {
            parent::pdo()->rollBack();
        }


        return parent::pdo()->commit();
    }

    public static function saveDocumento(Documento $documento): bool
    {
        $query = 'INSERT INTO DOCUMENTO (NSU, DOC_SCHEMA, CONTEUDO) 
                            VALUES(:nsu, :schema, :conteudo)';

        try {
            $statement = static::pdo()->prepare($query);
            $statement->execute([
                ':nsu' => $documento->nsu,
                ':schema' => $documento->schema->value,
                ':conteudo' => $documento->conteudo,
            ]);
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), $e->getCode());
        }

        $documento->id = parent::pdo()->lastInsertId();

        return $documento->id > 0;
    }

    public static function getByNSURange(int $inicioNSU, int $finalNSU): array
    {
        if ($inicioNSU > $finalNSU) {
            return [];
        }

        $query = 'SELECT IDDOCMENTO, NSU, DOC_SCHEMA, CONTEUDO, PROCESSADO, DATA_CRIACAO
                        FROM DOCUMENTO
                    WHERE NSU >= :inicioNSU AND NSU <= :finalNSU';

        $statement = parent::pdo()->prepare($query);
        $statement->execute([':inicioNSU' => $inicioNSU, ':finalNSU' => $finalNSU]);

        $documentos = [];
        foreach ($statement->fetchAll() as $data) {
            $documentos = self::createFromArray($data);
        }

        return $documentos;
    }

    public static function getById(int $id): ?Documento
    {
        if ($id == 0) {
            return null;
        }

        $query = 'SELECT IDDOCMENTO, NSU, DOC_SCHEMA, CONTEUDO, PROCESSADO, DATA_CRIACAO
                        FROM DOCUMENTO
                    WHERE IDDOCUMENTO = :id';

        $statement = parent::pdo()->prepare($query);
        $statement->execute([':id' => $id]);

        $data = $statement->fetch();

        return self::createFromArray($data);
    }

    private static function createFromArray(array $data)
    {
        $newDocumento = new Documento($data['NSU'], $data['DOC_SCHEMA'], $data['CONTEUDO']);
        $newDocumento->id = $data['IDDOCUMENTO'];

        return $newDocumento;
    }
}
