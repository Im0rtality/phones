<?php

namespace Phones\DataProviders\GsmArenaComBundle\Service;

use Phones\PhoneBundle\Entity\Phone;

class PhoneConverter
{
    /** @var  string */
    private $provider;

    /** @var  string */
    private $domain;

    /** @var  array */
    private $availableOs;

    /** @var array  */
    private $cpuCoresMap = [
        'dual-core' => 2,
        'quad-core' => 4,
    ];

    /** @var array  */
    private $ramUnitMap = [
        'GB' => 1024,
        'MB' => 1,
    ];

    /** @var array  */
    private $freqMapping = [
        'MHZ' => 1,
        "GHZ" => 1000
    ];

    /**
     * @param string $provider
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;
    }

    /**
     * @param string $domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * @param array $availableOs
     */
    public function setAvailableOs($availableOs)
    {
        $this->availableOs = $availableOs;
    }

    /**
     * @param $phoneSpecs
     * @return null|Phone
     */
    public function convert($phoneSpecs)
    {
        $phone = null;

        if (!empty($phoneSpecs)) {
            $phone = new Phone();
            $this->loadBasicData($phone, $phoneSpecs);
            $this->loadPlatform($phone, $phoneSpecs);
            $this->loadMemory($phone, $phoneSpecs);
            $this->loadCamera($phone, $phoneSpecs);
            $this->loadCommon($phone, $phoneSpecs);
            $this->loadBattery($phone, $phoneSpecs);
        }

        return $phone;
    }

    /**
     * @param Phone $phone
     * @param array $phoneSpecs
     */
    private function loadBasicData($phone, $phoneSpecs)
    {
        /* Phone name and Brand*/
        $phoneName = $this->getArrayValue($phoneSpecs, 'phone_name');
        $brand     = $this->getArrayValue($phoneSpecs, 'brand');
        if (!empty($phoneName) && !empty($brand)) {
            $phone->setBrand($brand);
            $phone->setPhoneId($phone->getBrand() . ' ' . $phoneName);
        }

        /* Image link */
        $imageLink = $this->getArrayValue($phoneSpecs, 'image_link');
        $phone->setImage($imageLink);

        /* Technology */
        $phone->setTechnology($this->getArrayValue($phoneSpecs, 'network/technology'));

        /* Weight */
        $feedWeights = $this->getArrayValue($phoneSpecs, 'body/weight');
        if (preg_match_all('/\b\d+(?:\.\d{0,}|)/', $feedWeights, $matches)) {
            $phone->setWeight(max($matches[0]));
        }

        /* Display size */
        $feedDisplay = (string)$this->getArrayValue($phoneSpecs, 'display/size');
        if (preg_match('/(?<value>[\d.]+)\s*inches/i', $feedDisplay, $matches)) {
            $phone->setDisplaySize((float)$matches['value']);
        }
    }

    /**
     * @param Phone $phone
     * @param array $phoneSpecs
     */
    private function loadPlatform($phone, $phoneSpecs)
    {
        /* OS */
        $feedOs = (string)$this->getArrayValue($phoneSpecs, 'platform/os');
        $os = 'other';
        $osPattern = '';
        foreach ($this->availableOs as $availOs) {
            $osPattern .= !empty($osPattern) ? '|' : '';
            $osPattern .= $availOs;
        }
        if (preg_match('/'.$osPattern.'/i', $feedOs, $matches)) {
            if (isset($this->availableOs[strtolower($matches[0])])) {
                $os = ($this->availableOs[strtolower($matches[0])]);
            }
        }
        $phone->setOs($os);

        /* CPU freq */
        $feedCpuData = (string)$this->getArrayValue($phoneSpecs, 'platform/cpu');
        if (preg_match_all('/(?<freqUnit>[\d.]+\s*[G|M]Hz)/i', $feedCpuData, $matches)) {
            $frequencies = [];
            foreach ($matches['freqUnit'] as $freqAndUnit) {
                if (preg_match('/(?<value>[\d.]+)\s*(?<unit>[G|M]Hz)/i', $freqAndUnit, $childMatches)) {
                    $value = $childMatches['value'];
                    $unit  = $childMatches['unit'];
                    if (isset($this->freqMapping[strtoupper($unit)])) {
                        $frequencies[] = $this->freqMapping[strtoupper($unit)] * $value;
                    }
                }
            }
            if (!empty($frequencies)) {
                $phone->setCpuFreq((float)max($frequencies));
            }
        }

        /* CPU cores */
        if (preg_match_all('/(?<cores>[a-z]+-core)/i', $feedCpuData, $matches)) {
            $cores = 0;
            foreach ($matches['cores'] as $core) {
                $lower = strtolower($core);
                if (isset($this->cpuCoresMap[$lower])) {
                    $cores += $this->cpuCoresMap[$lower];
                }
            }
            $phone->setCpuCores($cores);
        }

        $cpuCores = $phone->getCpuCores();
        $cpuFreq  = $phone->getCpuFreq();
        if (empty($cpuCores) && !empty($cpuFreq)) {
            $phone->setCpuCores(1);
        }
    }

    /**
     * @param Phone $phone
     * @param array $phoneSpecs
     */
    private function loadMemory($phone, $phoneSpecs)
    {
        /* RAM */
        $feedRamData = (string)$this->getArrayValue($phoneSpecs, 'memory/internal');
        if (preg_match('/(?<value>[\d.]+)\s*(?<unit>G?M?B) RAM/i', $feedRamData, $matches)) {
            $value = $matches['value'];
            $unit  = strtoupper($matches['unit']);
            if (isset($this->ramUnitMap[$unit])) {
                $phone->setRamMb($value * $this->ramUnitMap[$unit]);
            }
        }

        /* External sd */
        $feedCardSlot = (string)$this->getArrayValue($phoneSpecs, 'memory/card slot');
        if (preg_match('/microSD|up to|G?M?B|Yes/i', $feedCardSlot)) {
            $phone->setExternalSd(1);
        } else {
            $phone->setExternalSd(0);
        }
    }

    /**
     * @param Phone $phone
     * @param array $phoneSpecs
     */
    private function loadCamera($phone, $phoneSpecs)
    {
        /* Camera mpx */
        $feedCamera = (string)$this->getArrayValue($phoneSpecs, 'camera/primary');
        if (preg_match('/(?<value>[\d.]+)\s*MP|(?<vga>VGA)/i', $feedCamera, $matches)) {
            $mpx = null;
            if (isset($matches['vga'])) {
                $mpx = 0.3;
            } elseif (isset($matches['value'])) {
                $mpx = (float)$matches['value'];
            }
            $phone->setCameraMpx($mpx);
        }

        /* Video p */
        $feedVideo = (string)$this->getArrayValue($phoneSpecs, 'camera/video');
        if (preg_match('/(?<p>[\d.]+)p@?/i', $feedVideo, $matches)) {
            $phone->setVideoP((int)$matches['p']);
        }

        /* Flash */
        if (preg_match('/LED|flash/i', $feedCamera)) {
            $phone->setFlash(1);
        }
    }

    /**
     * @param Phone $phone
     * @param array $phoneSpecs
     */
    private function loadCommon($phone, $phoneSpecs)
    {
        /* GPS */
        $feedGps = $this->getArrayValue($phoneSpecs, 'comms/gps');
        if (!empty($feedGps) && strtolower($feedGps) != 'no') {
            $phone->setGps($feedGps);
        }

        /* WLAN */
        $feedWlan = $this->getArrayValue($phoneSpecs, 'comms/wlan');
        if (!empty($feedWlan) && strtolower($feedWlan) != 'no') {
            $phone->setWlan($feedWlan);
        }

        /* Bluetooth version */
        $feedBluetooth = (string)$this->getArrayValue($phoneSpecs, 'comms/bluetooth');
        if (preg_match('/v(?<value>[\d.]+)\s*/i', $feedBluetooth, $matches)) {
            $phone->setBluetoothVersion((float)$matches['value']);
        }
    }
    /**
     * @param Phone $phone
     * @param array $phoneSpecs
     */
    private function loadBattery($phone, $phoneSpecs)
    {
        /* Battery stand by in h */
        //stand by to float
        $feedBatteryStandBy = (string)$this->getArrayValue($phoneSpecs, 'battery/stand-by');
        if (preg_match_all('/(?<h>[\d.]+)\s*h/i', $feedBatteryStandBy, $matches)) {
            $phone->setBatteryStandByH(max($matches['h']));
        }

        /* Battery talk time by in h */
        //talk time to float
        $feedBatteryTalkTime = (string)$this->getArrayValue($phoneSpecs, 'battery/talk time');
        if (preg_match_all('/(?<h>[\d.]+)\s*h/i', $feedBatteryTalkTime, $matches)) {
            $phone->setBatteryTalkTime(max($matches['h']));
        }
    }

    /**
     * @param array $array
     * @param string $path
     *
     * @return mixed|null
     */
    private function getArrayValue($array, $path)
    {
        $value = $array;
        $path = explode('/', $path);
        foreach ($path as $name) {
            if (isset($value[$name])) {
                $value = $value[$name];
            } else {
                return null;
            }
        }

        return $value;
    }
}