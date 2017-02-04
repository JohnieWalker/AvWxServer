<?php


namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class AirportWeather
 * @package AppBundle\Entity
 * @ORM\Entity
 * @ORM\Table(name="AirportWeather")
 */
class AirportWeather
{
    /**
    * @ORM\Column(type="integer")
    * @ORM\Id
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    private $id;

    /**
     * @ORM\Column(type="string", length=4, unique=true)
     */
    private $ICAO;
    /**
     * @var
     * @ORM\Column(type="text", nullable=true)
     */
    private $METAR;

    /**
     * @var
     * @ORM\Column(type="datetime", name="metar_observation_time", nullable=true)
     */
    private $METARObservationTime;

    /**
     * @return mixed
     */
    public function getMETARObservationTime()
    {
        return $this->METARObservationTime;
    }

    /**
     * @param mixed $METARObservationTime
     * @return AirportWeather
     */
    public function setMETARObservationTime($METARObservationTime)
    {
        $this->METARObservationTime = $METARObservationTime;
        return $this;
    }


    /**
     * @var
     * @ORM\Column(type="text", nullable=true)
     */
    private $TAF;

    /**
     * @var
     * @ORM\Column(type="datetime", nullable=true, name="taf_observation_time")
     */
    private $TAFObservationTime;

    /**
     * @return mixed
     */
    public function getTAFObservationTime()
    {
        return $this->TAFObservationTime;
    }

    /**
     * @param mixed $TAFObservationTime
     * @return AirportWeather
     */
    public function setTAFObservationTime($TAFObservationTime)
    {
        $this->TAFObservationTime = $TAFObservationTime;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return AirportWeather
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getICAO()
    {
        return $this->ICAO;
    }

    /**
     * @param mixed $ICAO
     * @return AirportWeather
     */
    public function setICAO($ICAO)
    {
        $this->ICAO = $ICAO;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMETAR()
    {
        return $this->METAR;
    }

    /**
     * @param mixed $METAR
     * @return AirportWeather
     */
    public function setMETAR($METAR)
    {
        $this->METAR = $METAR;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTAF()
    {
        return $this->TAF;
    }

    /**
     * @param mixed $TAF
     * @return AirportWeather
     */
    public function setTAF($TAF)
    {
        $this->TAF = $TAF;
        return $this;
    }


}
