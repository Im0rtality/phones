<?php

namespace Phones\PhoneBundle\Services\CurrencyService;

use Phones\PhoneBundle\Services\Downloader;

class CurrencyService
{
    /** @var string */
    protected $currenciesLink = 'http://themoneyconverter.com/rss-feed/EUR/rss.xml';
    /** @var string */
    protected $baseCurrency = 'EUR';
    /** @var string  */
    protected $pathToSave = '../app/data/currencies';
    /** @var string  */
    protected $fileName = 'currencies.json';
    /** @var  Downloader */
    protected $downloader;

    /** @var array */
    protected $currencies = [];

    /**
     * Save currencies
     */
    public function getCurrentCurrencies()
    {
        $content = $this->downloader->getContent($this->currenciesLink);

        if ($content) {
            $xml = simplexml_load_string($content);
            if ($xml) {
                /** @var \SimpleXmlElement $channel */
                $channel = $xml->{'channel'};
                /** @var \SimpleXmlElement $element */
                foreach ($channel->children() as $element) {
                    if ($element->getName() == 'item') {

                        $title = (string) $element->{'title'};
                        $titles = explode('/', $title);
                        $foreignCurrency = $titles[0];

                        $description = (string) $element->{'description'};
                        $startPos = strpos($description, '=') + 2;
                        $endPos = strpos($description, ' ', $startPos);
                        $price = substr($description, $startPos, $endPos-$startPos);
                        $price = (double) $price;

                        $this->currencies[$foreignCurrency] = 1 / $price;
                    }
                }
            }

            $this->saveCurrencies();
        }
    }

    /**
     * @param $dirName
     */
    protected function createDir($dirName)
    {
        if (!file_exists($dirName)) {
            mkdir($dirName, 0777, true);
        }
    }

    /**
     * Save downloaded currencies
     */
    public function saveCurrencies()
    {
        if (!empty($this->currencies)) {
            $this->createDir($this->pathToSave);
            file_put_contents(
                $this->pathToSave . DIRECTORY_SEPARATOR . $this->fileName,
                json_encode($this->currencies)
            );
        }
    }

    /**
     * @param string $currenciesLink
     */
    public function setCurrenciesLink($currenciesLink)
    {
        $this->currenciesLink = $currenciesLink;
    }

    /**
     * @param string $pathToSave
     */
    public function setPathToSave($pathToSave)
    {
        $this->pathToSave = $pathToSave;
    }

    /**
     * @param string $fileName
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * @param Downloader $downloader
     */
    public function setDownloader($downloader)
    {
        $this->downloader = $downloader;
    }

    /**
     * @return array
     */
    public function getCurrencies()
    {
        return $this->currencies;
    }
}
