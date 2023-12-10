<?php

declare(strict_types=1);

namespace App\Handlers;

use App\Config;
use App\Entities\Historico;
use App\Services\SefazDistDFeService;
use App\Entities\RetornoDistDFe;

class ConsultaRadarNFeHandler
{
    private RadarNFeHandler $radarHandler;
    public function __construct(private Config $config)
    {
        $distDFeService = new SefazDistDFeService($config);
        $this->radarHandler = new RadarNFeHandler($distDFeService);
    }
    public function handle()
    {
        $count = 0;
        $limiteConsulta = $this->config->empresa('limiteConsulta');
        $ultimoNSUConsultado = Historico::getUltimoNSU();

        while ($count < $limiteConsulta) {
            $resposta = $this->radarHandler->buscarDocumentos($ultimoNSUConsultado);

            /** @var RetornoDistDFe*/
            $retornoDistDfe = $resposta->data;

            $ultimoNSUConsultado = $retornoDistDfe->ultimoNSU;

            $count++;
        }
    }
}
