<?php

declare(strict_types=1);

use App\Config;
use App\App;
use App\Entities\Documento;
use App\Entities\ResumoNFe;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . '/vendor/autoload.php';

$env = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$env->load();

$config = new Config($_ENV);
$app = new App($config);

$documento = new Documento((int)'000000000099110', \App\Entities\Enums\Schema::RESUMO_NFE, 'H4sIAAAAAAAEAIVSbWvCMBD+K6XfTe5Sm1Y5A27qqFgV3yb7VmvUQm21LdP9+0XrBu7LICT3HM/LcYQKXY4H2roe06xsX8ttxz5U1anN+eVyYReH5cWeCwDk63A0jw/6GNm/5OR/ciPJyirKYm1bn7ooo7xjIwN8eDzpT3lRRekuKeMoZUm2Y5uCZzttK4oPZkTVROEgeFI6rvRbYGJawnUBHFMK13cEIgL4TYnSIV5r6HU8HapnDfF7k67j/KjVhK1Yj1lBOJ3MFt3eZNa1+lYvmC9mwcsyuOPRotclXtMp6CsEBNlC9IyVgbQ99I+JEiCcBkJDeAt02wDmNMAxN/GaQNVpPFBI/P7S5w2gBwwk8RugbbJfRakK9XIq0/DtvB4Ml+H2fX7m14+195Xs9h3jVZNM6EzHmyr/m4tPuQ8OZdMirxTeVgjCd0XTR+kTr9sUz5Pqti0z209JvP4Y6htm+uYoIQIAAA==');

$saved = Documento::saveDocumento($documento);

$resumo = ResumoNFe::fromXML((int)'000000000099110', $documento->extrairConteudo());
$resumo->setDocumento($documento);

$processed = $resumo->processar();

var_dump($processed);
