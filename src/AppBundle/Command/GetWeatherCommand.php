<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class GetWeatherCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:get_weather_command')
            ->setDescription('Load weather from AviationWeather.gov');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $stopwatch = new Stopwatch();
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $conn = $em->getConnection();

        $metarLink = "compress.zlib://https://aviationweather.gov/adds/dataserver_current/current/metars.cache.xml.gz";
        $metarFile = simplexml_load_file($metarLink);
        $output->writeln("<info>Got METAR file</info>");

        $tafLink = "compress.zlib://https://aviationweather.gov/adds/dataserver_current/current/tafs.cache.xml.gz";
        $tafFile = simplexml_load_file($tafLink);
        $output->writeln("<info>Got TAF file</info>");

        $conn->beginTransaction();

        $stopwatch->start('Processing');
        $output->writeln("<info>Processing METAR file</info>");

        foreach ($metarFile->data->METAR as $metar) {
            $stationID = (string)$metar->station_id;
            $rawWeather = (string)$metar->raw_text;
            $rawWeatherTime = $metar->observation_time;

            $stm = $conn->prepare("INSERT INTO AirportWeather SET"
                ." icao = :ICAO, metar = :raw_metar, metar_observation_time = :obs_time"
                ." ON DUPLICATE KEY UPDATE metar = :raw_metar, metar_observation_time = :obs_time");

            $stm->bindParam("ICAO", $stationID);
            $stm->bindParam("raw_metar", $rawWeather);
            $stm->bindParam("obs_time", $rawWeatherTime);
            $stm->execute();
        }

        $conn->commit();

        $conn->beginTransaction();

        $output->writeln("<info>Processing TAF file</info>");

        foreach ($tafFile->data->TAF as $taf) {
            $stationID = (string)$taf->station_id;
            $rawWeather = (string)$taf->raw_text;
            $rawWeatherTime = $taf->issue_time;

            $stm = $conn->prepare("INSERT INTO AirportWeather SET"
                ." icao = :ICAO, taf = :raw_taf, taf_observation_time = :obs_time"
                ." ON DUPLICATE KEY UPDATE taf = :raw_taf, taf_observation_time = :obs_time");
            $stm->bindParam("ICAO", $stationID);
            $stm->bindParam("raw_taf", $rawWeather);
            $stm->bindParam("obs_time", $rawWeatherTime);
            $stm->execute();
        }


        $conn->commit();
        $conn->close();

        $event = $stopwatch->stop('Processing');

        $output->writeln("");
        $output->writeln("<info>Files processed in " . $event->getDuration() . " ms</info>");
    }
}
