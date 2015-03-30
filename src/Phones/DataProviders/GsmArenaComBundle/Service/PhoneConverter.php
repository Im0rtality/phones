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
     * @param string $provider
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;
    }

    /**
     * @param $phoneSpecs
     * @return null|Phone
     */
    public function convert($phoneSpecs)
    {
        //http://phonerocket.com/
        $phone = null;

        if (!empty($phoneSpecs)) {
            $phone = new Phone();

            $phone->setTechnology($this->getArrayValue($phoneSpecs, 'network/technology'));

            $phone->setBrand($this->getArrayValue($phoneSpecs, 'brand'));

            //phoneId
            //image

            /* Weight */
            $feedWeights = $this->getArrayValue($phoneSpecs, 'body/weight');
            if (preg_match_all('/\b\d+(?:\.\d{0,}|)/', $feedWeights, $matches)) {
                $phone->setWeight(max($matches[0]));
            }

            /* OS */
            $feedOs = (string)$this->getArrayValue($phoneSpecs, 'platform/os');
            $os = 'other';
            foreach ($this->availableOs as $availOs) {
                if (strpos(strtolower($feedOs), strtolower($availOs))) {
                    $os = $availOs;
                    break;
                }
            }
            $phone->setOs($os);


            /* CPU freq */
            //cpu freq to string?
            $feedCpuData = (string)$this->getArrayValue($phoneSpecs, 'platform/cpu');
            if (preg_match_all('/(?<freq>[\d.]+)\s*(?<unit>G?Hz)/i', $feedCpuData, $matches)) {
                $availableFreqs = '';
                foreach ($matches['freq'] as $freq) {
                    $availableFreqs .= $freq . '/';
                }
                $phone->setCpuFreq($availableFreqs);
            }

            $cpuCoresMap = [
                'dual-core' => 2,
                'quad-core' => 4,
            ];

            /* CPU cores */
            if (preg_match_all('/(?<cores>[a-z]+-core)/i', $feedCpuData, $matches)) {
                $cores = 0;
                foreach ($matches['cores'] as $core) {
                    $lower = strtolower($core);
                    if (isset($cpuCoresMap[$lower])) {
                        $cores += $cpuCoresMap[$lower];
                    }
                }
                $phone->setCpuCores($cores);
            }

            /* RAM */
            $ramMgMap = [
                'GB' => 1024,
                'MB' => 1,
            ];

            $feedRamData = (string)$this->getArrayValue($phoneSpecs, 'memory/internal');
            if (preg_match('/(?<value>[\d.]+)\s*(?<unit>G?M?B) RAM/i', $feedRamData, $matches)) {
                $value = $matches['value'];
                $unit  = strtoupper($matches['unit']);
                if (isset($ramMgMap[$unit])) {
                    $phone->setRamMb($value * $ramMgMap[$unit]);
                }
            }

            /* External sd */
            $feedCardSlot = (string)$this->getArrayValue($phoneSpecs, 'memory/card slot');
            if (preg_match('/microSD|up to|G?M?B|Yes/i', $feedCardSlot)) {
                $phone->setExternalSd(1);
            }

            /* Display size */
            $feedDisplay = (string)$this->getArrayValue($phoneSpecs, 'display/size');
            if (preg_match('/(?<value>[\d.]+)\s*inches/i', $feedDisplay, $matches)) {
                $phone->setDisplaySize((float)$matches['value']);
            }

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

            //video p

            /* External sd */
            if (preg_match('/LED|flash/i', $feedCamera)) {
                $phone->setFlash(1);
            }

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

        return $phone;
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