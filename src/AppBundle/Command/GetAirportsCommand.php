<?php

namespace AppBundle\Command;

use AppBundle\Entity\AirportWeather;
use Ddeboer\DataImport\Reader\CsvReader;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GetAirportsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:get_airports_command')
            ->setDescription('Load airprots from OurAirports');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $batchSize = 50;
        $i = 0;

        $em = $this->getContainer()->get('doctrine')->getManager();
        $csvLink = "compress.zlib://http://ourairports.com/data/airports.csv";

        $file = new \SplFileObject($csvLink);
        $csvReader = new CsvReader($file);

        $csvReader->setHeaderRowNumber(0);
        $csvReader->rewind();

        $output->writeln('<info>CSV fetched from OurAirports. Filtering.</info>');

        $progress = new ProgressBar($output, $csvReader->count());
        $progress->start();

        while ($csvReader->current()) {
            if (in_array($csvReader->current()["type"], array("small_airport", "medium_airport", "large_airport")) &&
                (!is_null($csvReader->current()["iata_code"]) && strlen(trim($csvReader->current()["iata_code"])) > 0) &&
                (!is_null($csvReader->current()["gps_code"]) && strlen(trim($csvReader->current()["gps_code"])) == 4)
            ) {
                $airportWeather = new AirportWeather();
                $airportWeather->setICAO($csvReader->current()["gps_code"]);

                $em->persist($airportWeather);
                if (($i % $batchSize) === 0) {
                    $em->flush();
                    $em->clear();
                }
                ++$i;
            }
            $csvReader->next();
            $progress->advance();
        }

        $output->writeln('<info>'.$i.' airports imported</info>');

        $em->flush();
        $progress->finish();
    }
}
