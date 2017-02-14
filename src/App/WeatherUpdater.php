<?php

namespace App;

use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;

class WeatherUpdater
{
    private $connection;
    private $logger;

    public function __construct(LoggerInterface $logger, Connection $connection)
    {
        $this->logger = $logger;
        $this->connection = $connection;
    }

    public function update()
    {
        $this->processMetarFile();
        $this->processTafFile();

        $this->connection->close();
    }

    private function processMetarFile()
    {
        $metarFile = $this->getMetarFile();

        $this->connection->beginTransaction();

        foreach ($metarFile->data->METAR as $metar) {
            $stationID = (string)$metar->station_id;
            $rawWeather = (string)$metar->raw_text;
            $rawWeatherTime = $metar->observation_time;

            $stm = $this->connection->prepare(
                "INSERT INTO AirportWeather SET"
                ." icao = :ICAO, metar = :raw_metar, metar_observation_time = :obs_time"
                ." ON DUPLICATE KEY UPDATE metar = :raw_metar, metar_observation_time = :obs_time"
            );

            $stm->bindParam("ICAO", $stationID);
            $stm->bindParam("raw_metar", $rawWeather);
            $stm->bindParam("obs_time", $rawWeatherTime);
            $stm->execute();
        }

        $this->connection->commit();
        $this->logger->notice("METAR Processed");
    }

    /**
     * @return \SimpleXMLElement
     */
    private function getMetarFile()
    {
        $metarLink = "compress.zlib://https://aviationweather.gov/adds/dataserver_current/current/metars.cache.xml.gz";
        $metarFile = simplexml_load_file($metarLink);

        $this->logger->notice("Got METAR file");

        return $metarFile;
    }


    private function processTafFile()
    {
        $tafFile = $this->getTafFile();

        $this->connection->beginTransaction();

        foreach ($tafFile->data->TAF as $taf) {
            $stationID = (string)$taf->station_id;
            $rawWeather = (string)$taf->raw_text;
            $rawWeatherTime = $taf->issue_time;
            $stm = $this->connection->prepare(
                "INSERT INTO AirportWeather SET"
                ." icao = :ICAO, taf = :raw_taf, taf_observation_time = :obs_time"
                ." ON DUPLICATE KEY UPDATE taf = :raw_taf, taf_observation_time = :obs_time"
            );
            $stm->bindParam("ICAO", $stationID);
            $stm->bindParam("raw_taf", $rawWeather);
            $stm->bindParam("obs_time", $rawWeatherTime);
            $stm->execute();
        }

        $this->connection->commit();

        $this->logger->notice("TAF processed");
    }

    /**
     * @return \SimpleXMLElement
     */
    private function getTafFile()
    {
        $tafLink = "compress.zlib://https://aviationweather.gov/adds/dataserver_current/current/tafs.cache.xml.gz";
        $tafFile = simplexml_load_file($tafLink);

        $this->logger->notice("Got TAF file");

        return $tafFile;
    }
}