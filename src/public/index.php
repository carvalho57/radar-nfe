<?php

declare(strict_types=1);

use App\Config;
use App\App;
use App\Entities\Documento;
use App\Entities\ResumoEvento;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . '/vendor/autoload.php';

$env = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$env->load();

$config = new Config($_ENV);
$app = new App($config);


$documento = new Documento((int) '000000000099109', \App\Entities\Enums\Schema::from('resEvento'), 'H4sIAAAAAAAEAIWRUW+CMBSF/wrhHdrbglBybbK4+bBMZ6YPe0WoQqKtK52Y/fpVx8j25NvpyXfvOW3Rqu7prLQzweV40F1x6epp2Dh3Kgjp+z7ueWzsnjBKgbwvXtZVo45lOMLtfThqdedKXakwOCvblWYaQkxh2PFv/mSsKw+7tqvKQ9zqXby1RO9UKLF6tfvSSAFIBomz5epZcj7JOQBQH0kzJDcTq2Y5V5KnjAOd5AljCYgrkaVpegUppylkPAfBeSJEJoRfe5vBuvl5Dsko4xHQiGUbSIpUFJBHlBeUIhkZdKdBTXwQJEhGA/VafQzal/5zwssgFo/zSAUPn87Y9qusTVCZYzDbRArJL+LrvKlqe6/OwKBeWeNkLoDxnIkkY8Jf2IffbCTjX8tvY5JZB/cBAAA=');


Documento::saveDocumento($documento);

$resumoEvento = ResumoEvento::fromXML($documento->nsu, $documento->extrairConteudo());
$resumoEvento->setDocumento($documento);


$processed = $resumoEvento->processar();

var_dump($processed);
