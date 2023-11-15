<?php

namespace App\Entities;

class Historico extends Entity
{
    private string $resposta;

    public function __construct(
        public int $nsuConsultado,
        public int $ultimoNSU,
        public int $maiorNSU,
        public string $status,
        public string $motivo,
        public \DateTime $dataResposta,
        public string $ambiente,
        string $resposta
    ) {
        parent::__construct();
        $this->resposta = $resposta;
    }

    public static function createFromDistDFe(int $nsuConsultado, RetornoDistDFe $retornoDistDFe): static
    {
        $novoHistorico = new static(
            $nsuConsultado,
            $retornoDistDFe->ultimoNSU,
            $retornoDistDFe->maxNSU,
            $retornoDistDFe->status,
            $retornoDistDFe->motivo,
            new \DateTime($retornoDistDFe->dataResposta),
            $retornoDistDFe->ambiente,
            $retornoDistDFe->resposta
        );

        return $novoHistorico;
    }

    public function getResposta()
    {
        return gzdecode(base64_decode($this->resposta));
    }

    public function gravar(): bool
    {
        $query = <<<QUERY
                INSERT INTO
                        HISTORICO (
                            NSU_CONSULTADO,
                            ULTIMO_NSU,
                            MAIOR_NSU,
                            STATUS,
                            MOTIVO,
                            DATA_RESPOSTA,
                            AMBIENTE,
                            RESPOSTA
                        )
                    values (
                        :NSU_CONSULTADO,
                        :ULTIMO_NSU,
                        :MAIOR_NSU,
                        :STATUS,
                        :MOTIVO,
                        :DATA_RESPOSTA,
                        :AMBIENTE,
                        :RESPOSTA
                    );
        QUERY;

        try {
            $statement = $this->pdo()->prepare($query);
            $statement->execute([
                ':NSU_CONSULTADO' => $this->nsuConsultado,
                ':ULTIMO_NSU' => $this->ultimoNSU,
                ':MAIOR_NSU' => $this->maiorNSU,
                ':STATUS' => $this->status,
                ':MOTIVO' => $this->motivo,
                ':DATA_RESPOSTA' => $this->dataResposta->format('Y-m-d H:i:s'),
                ':AMBIENTE' => $this->ambiente,
                ':RESPOSTA' => base64_encode(gzencode($this->resposta)),
            ]);
        } catch (\PDOException $e) {
            throw $e;
        }

        $this->id = $this->pdo()->lastInsertId();

        return $this->id > 0;
    }
}
