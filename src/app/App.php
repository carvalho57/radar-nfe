<?php

namespace App;

use App\Database\DB;

class App
{
    private static DB $db;

    public function __construct(Config $config)
    {
        static::$db = new DB($config->db());
    }

    public static function db(): DB
    {
        return static::$db;
    }
}
