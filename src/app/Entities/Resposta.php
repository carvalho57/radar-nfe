<?php

declare(strict_types=1);

namespace App\Models;

readonly class Resposta
{
    public function __construct(
        public bool $sucesso,
        public string $mensagem,
        public mixed $data
    ) {

    }

    public static function sucesso(string $mensagem, mixed $data = null) {
        return new static(true, $mensagem, $data);
    }

    public static function falha(string $mensagem, mixed $data = null) {
        return new static(false, $mensagem, $data);
    }

    public function isSucesso() {
        return $this->sucesso;
    }

    public function isFalha() {
        return !$this->sucesso;
    }
}

