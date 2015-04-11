<?php

namespace Phones\PhoneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Phone
 */
class Phone
{
    /**
     * @var string
     */
    private $phoneId;

    /**
     * @var string
     */
    private $brand;

    /**
     * @var string
     */
    private $image;

    /**
     * @var integer
     */
    private $weight;

    /**
     * @var string
     */
    private $os;

    /**
     * @var float
     */
    private $cpu_freq;

    /**
     * @var integer
     */
    private $cpu_cores;

    /**
     * @var integer
     */
    private $ram_mb;

    /**
     * @var boolean
     */
    private $external_sd;

    /**
     * @var float
     */
    private $display_size;

    /**
     * @var float
     */
    private $camera_mpx;

    /**
     * @var integer
     */
    private $video_p;

    /**
     * @var boolean
     */
    private $flash;

    /**
     * @var string
     */
    private $technology;

    /**
     * @var string
     */
    private $gps;

    /**
     * @var string
     */
    private $wlan;

    /**
     * @var float
     */
    private $bluetooth_version;

    /**
     * @var integer
     */
    private $battery_stand_by_h;

    /**
     * @var integer
     */
    private $battery_talk_time;

    /**
     * @var \Phones\PhoneBundle\Entity\CameraRate
     */
    private $cameraRate;

    /**
     * @var \Phones\PhoneBundle\Entity\CameraSpeed
     */
    private $cameraSpeed;

    /**
     * @var \Phones\PhoneBundle\Entity\BatteryLife
     */
    private $batteryLife;

    /**
     * @var \Phones\PhoneBundle\Entity\BatteryChargingTime
     */
    private $batteryChargingTime;

    /**
     * @var \Phones\PhoneBundle\Entity\AudioOutput
     */
    private $audioOutput;

    /**
     * @var \Phones\PhoneBundle\Entity\BasemarkX
     */
    private $basemarkX;

    /**
     * @var \Phones\PhoneBundle\Entity\BasemarkOSII
     */
    private $basemarkOSII;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $costs;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $stats;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->costs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->stats = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set phoneId
     *
     * @param string $phoneId
     * @return Phone
     */
    public function setPhoneId($phoneId)
    {
        $this->phoneId = $phoneId;

        return $this;
    }

    /**
     * Get phoneId
     *
     * @return string 
     */
    public function getPhoneId()
    {
        return $this->phoneId;
    }

    /**
     * Set brand
     *
     * @param string $brand
     * @return Phone
     */
    public function setBrand($brand)
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * Get brand
     *
     * @return string 
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * Set image
     *
     * @param string $image
     * @return Phone
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return string 
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set weight
     *
     * @param integer $weight
     * @return Phone
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * Get weight
     *
     * @return integer 
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * Set os
     *
     * @param string $os
     * @return Phone
     */
    public function setOs($os)
    {
        $this->os = $os;

        return $this;
    }

    /**
     * Get os
     *
     * @return string 
     */
    public function getOs()
    {
        return $this->os;
    }

    /**
     * Set cpu_freq
     *
     * @param float $cpuFreq
     * @return Phone
     */
    public function setCpuFreq($cpuFreq)
    {
        $this->cpu_freq = $cpuFreq;

        return $this;
    }

    /**
     * Get cpu_freq
     *
     * @return float 
     */
    public function getCpuFreq()
    {
        return $this->cpu_freq;
    }

    /**
     * Set cpu_cores
     *
     * @param integer $cpuCores
     * @return Phone
     */
    public function setCpuCores($cpuCores)
    {
        $this->cpu_cores = $cpuCores;

        return $this;
    }

    /**
     * Get cpu_cores
     *
     * @return integer 
     */
    public function getCpuCores()
    {
        return $this->cpu_cores;
    }

    /**
     * Set ram_mb
     *
     * @param integer $ramMb
     * @return Phone
     */
    public function setRamMb($ramMb)
    {
        $this->ram_mb = $ramMb;

        return $this;
    }

    /**
     * Get ram_mb
     *
     * @return integer 
     */
    public function getRamMb()
    {
        return $this->ram_mb;
    }

    /**
     * Set external_sd
     *
     * @param boolean $externalSd
     * @return Phone
     */
    public function setExternalSd($externalSd)
    {
        $this->external_sd = $externalSd;

        return $this;
    }

    /**
     * Get external_sd
     *
     * @return boolean 
     */
    public function getExternalSd()
    {
        return $this->external_sd;
    }

    /**
     * Set display_size
     *
     * @param float $displaySize
     * @return Phone
     */
    public function setDisplaySize($displaySize)
    {
        $this->display_size = $displaySize;

        return $this;
    }

    /**
     * Get display_size
     *
     * @return float 
     */
    public function getDisplaySize()
    {
        return $this->display_size;
    }

    /**
     * Set camera_mpx
     *
     * @param float $cameraMpx
     * @return Phone
     */
    public function setCameraMpx($cameraMpx)
    {
        $this->camera_mpx = $cameraMpx;

        return $this;
    }

    /**
     * Get camera_mpx
     *
     * @return float 
     */
    public function getCameraMpx()
    {
        return $this->camera_mpx;
    }

    /**
     * Set video_p
     *
     * @param integer $videoP
     * @return Phone
     */
    public function setVideoP($videoP)
    {
        $this->video_p = $videoP;

        return $this;
    }

    /**
     * Get video_p
     *
     * @return integer 
     */
    public function getVideoP()
    {
        return $this->video_p;
    }

    /**
     * Set flash
     *
     * @param boolean $flash
     * @return Phone
     */
    public function setFlash($flash)
    {
        $this->flash = $flash;

        return $this;
    }

    /**
     * Get flash
     *
     * @return boolean 
     */
    public function getFlash()
    {
        return $this->flash;
    }

    /**
     * Set technology
     *
     * @param string $technology
     * @return Phone
     */
    public function setTechnology($technology)
    {
        $this->technology = $technology;

        return $this;
    }

    /**
     * Get technology
     *
     * @return string 
     */
    public function getTechnology()
    {
        return $this->technology;
    }

    /**
     * Set gps
     *
     * @param string $gps
     * @return Phone
     */
    public function setGps($gps)
    {
        $this->gps = $gps;

        return $this;
    }

    /**
     * Get gps
     *
     * @return string 
     */
    public function getGps()
    {
        return $this->gps;
    }

    /**
     * Set wlan
     *
     * @param string $wlan
     * @return Phone
     */
    public function setWlan($wlan)
    {
        $this->wlan = $wlan;

        return $this;
    }

    /**
     * Get wlan
     *
     * @return string 
     */
    public function getWlan()
    {
        return $this->wlan;
    }

    /**
     * Set bluetooth_version
     *
     * @param float $bluetoothVersion
     * @return Phone
     */
    public function setBluetoothVersion($bluetoothVersion)
    {
        $this->bluetooth_version = $bluetoothVersion;

        return $this;
    }

    /**
     * Get bluetooth_version
     *
     * @return float 
     */
    public function getBluetoothVersion()
    {
        return $this->bluetooth_version;
    }

    /**
     * Set battery_stand_by_h
     *
     * @param integer $batteryStandByH
     * @return Phone
     */
    public function setBatteryStandByH($batteryStandByH)
    {
        $this->battery_stand_by_h = $batteryStandByH;

        return $this;
    }

    /**
     * Get battery_stand_by_h
     *
     * @return integer 
     */
    public function getBatteryStandByH()
    {
        return $this->battery_stand_by_h;
    }

    /**
     * Set battery_talk_time
     *
     * @param integer $batteryTalkTime
     * @return Phone
     */
    public function setBatteryTalkTime($batteryTalkTime)
    {
        $this->battery_talk_time = $batteryTalkTime;

        return $this;
    }

    /**
     * Get battery_talk_time
     *
     * @return integer 
     */
    public function getBatteryTalkTime()
    {
        return $this->battery_talk_time;
    }

    /**
     * Set cameraRate
     *
     * @param \Phones\PhoneBundle\Entity\CameraRate $cameraRate
     * @return Phone
     */
    public function setCameraRate(\Phones\PhoneBundle\Entity\CameraRate $cameraRate = null)
    {
        $this->cameraRate = $cameraRate;

        return $this;
    }

    /**
     * Get cameraRate
     *
     * @return \Phones\PhoneBundle\Entity\CameraRate 
     */
    public function getCameraRate()
    {
        return $this->cameraRate;
    }

    /**
     * Set cameraSpeed
     *
     * @param \Phones\PhoneBundle\Entity\CameraSpeed $cameraSpeed
     * @return Phone
     */
    public function setCameraSpeed(\Phones\PhoneBundle\Entity\CameraSpeed $cameraSpeed = null)
    {
        $this->cameraSpeed = $cameraSpeed;

        return $this;
    }

    /**
     * Get cameraSpeed
     *
     * @return \Phones\PhoneBundle\Entity\CameraSpeed 
     */
    public function getCameraSpeed()
    {
        return $this->cameraSpeed;
    }

    /**
     * Set batteryLife
     *
     * @param \Phones\PhoneBundle\Entity\BatteryLife $batteryLife
     * @return Phone
     */
    public function setBatteryLife(\Phones\PhoneBundle\Entity\BatteryLife $batteryLife = null)
    {
        $this->batteryLife = $batteryLife;

        return $this;
    }

    /**
     * Get batteryLife
     *
     * @return \Phones\PhoneBundle\Entity\BatteryLife 
     */
    public function getBatteryLife()
    {
        return $this->batteryLife;
    }

    /**
     * Set batteryChargingTime
     *
     * @param \Phones\PhoneBundle\Entity\BatteryChargingTime $batteryChargingTime
     * @return Phone
     */
    public function setBatteryChargingTime(\Phones\PhoneBundle\Entity\BatteryChargingTime $batteryChargingTime = null)
    {
        $this->batteryChargingTime = $batteryChargingTime;

        return $this;
    }

    /**
     * Get batteryChargingTime
     *
     * @return \Phones\PhoneBundle\Entity\BatteryChargingTime 
     */
    public function getBatteryChargingTime()
    {
        return $this->batteryChargingTime;
    }

    /**
     * Set audioOutput
     *
     * @param \Phones\PhoneBundle\Entity\AudioOutput $audioOutput
     * @return Phone
     */
    public function setAudioOutput(\Phones\PhoneBundle\Entity\AudioOutput $audioOutput = null)
    {
        $this->audioOutput = $audioOutput;

        return $this;
    }

    /**
     * Get audioOutput
     *
     * @return \Phones\PhoneBundle\Entity\AudioOutput 
     */
    public function getAudioOutput()
    {
        return $this->audioOutput;
    }

    /**
     * Set basemarkX
     *
     * @param \Phones\PhoneBundle\Entity\BasemarkX $basemarkX
     * @return Phone
     */
    public function setBasemarkX(\Phones\PhoneBundle\Entity\BasemarkX $basemarkX = null)
    {
        $this->basemarkX = $basemarkX;

        return $this;
    }

    /**
     * Get basemarkX
     *
     * @return \Phones\PhoneBundle\Entity\BasemarkX 
     */
    public function getBasemarkX()
    {
        return $this->basemarkX;
    }

    /**
     * Set basemarkOSII
     *
     * @param \Phones\PhoneBundle\Entity\BasemarkOSII $basemarkOSII
     * @return Phone
     */
    public function setBasemarkOSII(\Phones\PhoneBundle\Entity\BasemarkOSII $basemarkOSII = null)
    {
        $this->basemarkOSII = $basemarkOSII;

        return $this;
    }

    /**
     * Get basemarkOSII
     *
     * @return \Phones\PhoneBundle\Entity\BasemarkOSII 
     */
    public function getBasemarkOSII()
    {
        return $this->basemarkOSII;
    }

    /**
     * Add costs
     *
     * @param \Phones\PhoneBundle\Entity\Cost $costs
     * @return Phone
     */
    public function addCost(\Phones\PhoneBundle\Entity\Cost $costs)
    {
        $this->costs[] = $costs;

        return $this;
    }

    /**
     * Remove costs
     *
     * @param \Phones\PhoneBundle\Entity\Cost $costs
     */
    public function removeCost(\Phones\PhoneBundle\Entity\Cost $costs)
    {
        $this->costs->removeElement($costs);
    }

    /**
     * Get costs
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCosts()
    {
        return $this->costs;
    }

    /**
     * Add stats
     *
     * @param \Phones\PhoneBundle\Entity\Stat $stats
     * @return Phone
     */
    public function addStat(\Phones\PhoneBundle\Entity\Stat $stats)
    {
        $this->stats[] = $stats;

        return $this;
    }

    /**
     * Remove stats
     *
     * @param \Phones\PhoneBundle\Entity\Stat $stats
     */
    public function removeStat(\Phones\PhoneBundle\Entity\Stat $stats)
    {
        $this->stats->removeElement($stats);
    }

    /**
     * Get stats
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getStats()
    {
        return $this->stats;
    }
}
