<?php

namespace App;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class UpdateWeatherCommand extends Command
{
    private $weatherUpdater;

    public function __construct($name = null, WeatherUpdater $weatherUpdater)
    {
        $this->weatherUpdater = $weatherUpdater;

        parent::__construct($name);
    }

    public function configure()
    {
        $this->setName('update');
        $this->setDescription('Update METAR & TAF from AviationWeather.gov');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $stopwatch = new Stopwatch();
        $stopwatch->start('Processing');
        $this->weatherUpdater->update();
        $event = $stopwatch->stop('Processing');
        $output->writeln("<info>Files processed in " . $event->getDuration() . " ms</info>");
    }
}