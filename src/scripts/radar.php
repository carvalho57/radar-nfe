<?php

declare(strict_types=1);

use App\Config;
use App\App;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . '/vendor/autoload.php';

$env = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$env->load();

$config = new Config($_ENV);
$app = new App($config);
$app->run();
