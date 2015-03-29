<?php

namespace Phones\DataProviders\GsmArenaComBundle\Tests\Service;

use Phones\DataProviders\GsmArenaComBundle\Service\BrandDownloader;
use Phones\PhoneBundle\Entity\Phone;

class BrandDownloaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function getTestParsePhoneData()
    {
        $out = [];

        // case #0
        $phone = new Phone();
        $phone->setTechnology('ss');

        $out[] = [
            'phone_specs.json',
            $phone
        ];

        return $out;
    }

    /**
     * @dataProvider getTestParsePhoneData
     *
     * @param $fixture
     * @param $expected
     */
    public function testParsePhone($fixture, $expected)
    {
        $service = new BrandDownloader();

        $phoneSpecs = json_decode(file_get_contents($this->getFixture($fixture)), true);
        $actual = $service->parsePhone($phoneSpecs);

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
 