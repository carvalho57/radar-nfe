<?php

namespace App;

use App\Database\DB;
use App\Handlers\RadarNFeHandler;
use App\Services\SefazDistDFeService;

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
        $distDFeService = new SefazDistDFeService($this->config);
        $radar = new RadarNFeHandler($distDFeService);

        $resposta = $radar->buscarDocumentos(0);


        echo '<pre>';
        if ($resposta->isSucesso()) {
            var_dump("Sucesso: {$resposta->mensagem}");
        } else {
            var_dump("Erro: {$resposta->mensagem}");
        }
    }
}
