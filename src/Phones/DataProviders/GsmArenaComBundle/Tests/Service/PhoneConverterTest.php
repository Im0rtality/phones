<?php

namespace Phones\DataProviders\GsmArenaComBundle\Tests\Service;

use Phones\DataProviders\GsmArenaComBundle\Service\PhoneConverter;
use Phones\PhoneBundle\Entity\Phone;

class PhoneConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function getTestConvertData()
    {
        $out = [];

        // case #0
        $phone = new Phone();
        $phone->setPhoneId('Microsoft Lumia 640 XL LTE');
        $phone->setBrand('Microsoft');
        $phone->setImage('www.domain.dom/link/test');

        $phone->setTechnology('GSM / HSPA');
        $phone->setWeight(127.9);
        $phone->setOs('Windows Phone');
        $phone->setCpuFreq(2.0);
        $phone->setCpuCores(2 + 4 + 4);
        $phone->setRamMb(1228.8);
        $phone->setExternalSd(1);
        $phone->setDisplaySize(4.0);
        $phone->setCameraMpx(2.0);
        $phone->setVideoP(480);
        $phone->setFlash(1);
        $phone->setGps('A-GPS, GLONASS');
        $phone->setWlan('Wi-Fi 802.11 b/g/n, hotspot');
        $phone->setBluetoothVersion(4.0);
        $phone->setBatteryStandByH(730);
        $phone->setBatteryTalkTime(13);

        $out[] = [
            'phone_specs.json',
            $phone
        ];

        return $out;
    }

    /**
     * @dataProvider getTestConvertData
     *
     * @param $fixture
     * @param $expected
     */
    public function testConvert($fixture, $expected)
    {
        $phoneSpecs = json_decode(file_get_contents($this->getFixture($fixture)), true);

        $service = new PhoneConverter();
        $service->setAvailableOs(
            [
                'Android',
                'Windows Phone',
                'Symbian'
            ]
        );

        $actual = $service->convert($phoneSpecs);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @return string
     */
    protected function getFixturePath()
    {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR;
    }

    /**
     * @param string $fixture
     *
     * @return string
     */
    protected function getFixture($fixture)
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        return realpath(dirname($backtrace[0]['file'])) . '/fixtures/' . $fixture;
    }
}
 