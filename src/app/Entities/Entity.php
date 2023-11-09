<?php

namespace App\Entities;

use App\App;
use App\Database\DB;

abstract class Entity
{
    protected int $id;

    protected static ?DB $db = null;

    public function __construct()
    {
        static::$db = App::db();
    }

    protected static function pdo(): \PDO
    {
        if (static::$db === null) {
            static::$db = App::db();
        }

        return static::$db->pdo();
    }
}
