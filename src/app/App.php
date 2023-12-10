<?php

namespace App;

use App\Database\DB;
use App\Handlers\ConsultaRadarNFeHandler;

class App
{
    private static DB $db;
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
        static::$db = new DB($config->db());
    }

    public static function db(): DB
    {
        return static::$db;
    }

    public function run()
    {
        $consultaRadar = new ConsultaRadarNFeHandler($this->config);
        $consultaRadar->handle();
    }
}
