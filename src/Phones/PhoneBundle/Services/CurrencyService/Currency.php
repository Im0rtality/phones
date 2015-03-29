<?php

namespace Phones\PhoneBundle\Services\CurrencyService;

class Currency
{
    /** @var string */
    protected $baseCurrency = 'EUR';
    /** @var string */
    protected $pathToLoad = '../app/data/currencies/currencies.json';
    /** @var array */
    protected $currencies = [];

    /** @var  double */
    protected $convertedPrice;
    /** @var  string */
    protected $convertedCurrency;

    /**
     * @return array|string
     */
    protected function loadCurrencies()
    {
        if (file_exists($this->pathToLoad)) {
            $this->currencies = json_decode(file_get_contents($this->pathToLoad), true);
        }
    }

    /**
     * @param $price
     * @param $currency
     */
    public function convertPrice($price, $currency)
    {
        if (empty($this->currencies)) {
            $this->loadCurrencies();
        }

        $calculatedPrice    = null;
        $calculatedCurrency = null;

        if (isset($this->currencies[$currency])) {
            $calculatedPrice = $price * $this->currencies[$currency];
            $calculatedPrice = round($calculatedPrice, 2);
            $calculatedCurrency = $this->baseCurrency;
        }

        $this->convertedPrice = $calculatedPrice;
        $this->convertedCurrency = $calculatedCurrency;
    }

    /**
     * @param string $baseCurrency
     */
    public function setBaseCurrency($baseCurrency)
    {
        $this->baseCurrency = $baseCurrency;
    }

    /**
     * @param string $pathToLoad
     */
    public function setPathToLoad($pathToLoad)
    {
        $this->pathToLoad = $pathToLoad;
    }

    /**
     * @return string
     */
    public function getConvertedCurrency()
    {
        return $this->convertedCurrency;
    }

    /**
     * @return float
     */
    public function getConvertedPrice()
    {
        return $this->convertedPrice;
    }
}
