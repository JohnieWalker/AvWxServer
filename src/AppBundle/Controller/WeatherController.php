<?php

namespace AppBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpKernel\Exception\HttpException;

class WeatherController extends FOSRestController
{
    /**
     * @Rest\Get("/weather")
     * @Rest\QueryParam(name="airports", description="Airports to get weather for", nullable=false)
     * @Rest\View()
     *
     * @param string $airports
     * @return array
     */
    public function getAction($airports)
    {
        $returnAirports = array();

        if(!$airports){
            throw new HttpException(400, "Wrong request");
        }

        $airports = $this->getDoctrine()->getRepository("AppBundle:AirportWeather")->findBy(array(
            "ICAO" => explode(",", $airports)
        ));

        foreach ($airports as $airport) {
            $returnAirports[$airport->getICAO()] = array(
                "ICAO" => $airport->getICAO(),
                "metar" => $airport->getMETAR(),
                "metar_obs_time" => $airport->getMETARObservationTime(),
                "taf" => $airport->getTAF(),
                "taf_obs_time" => $airport->getTAFObservationTime()
            );
        }

        return $returnAirports;
    }
}
