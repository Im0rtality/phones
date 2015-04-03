<?php

namespace Phones\PhoneBundle\Services;

class MappingHelper
{
    /** @var string  */
    private $dataProvider = 'gsmArenaCom';

    /** @var array  */
    private $dataMapping = [];

    /** @var string */
    private $provider;

    /** @var array */
    private $providerMapping = [];

    /**
     * @param string $provider
     */
    function __construct($provider)
    {
        //load data mapping
        $fileName = $this->getMappingPath($this->dataProvider);
        if (file_exists($fileName)) {
            $this->dataMapping = json_decode(file_get_contents($fileName), true);

            //make a copy
            $copyFileName = $this->getMappingPath($this->dataProvider . '_' . date('Y-m-d-h-m-s'));
            file_put_contents($copyFileName, json_encode($this->dataMapping));

            if (!is_array($this->dataMapping)) {
                $this->dataMapping = [];
            }
        }

        //load provider mapping
        if (!empty($provider)) {
            $fileName = $this->getMappingPath($this->provider);
            if (file_exists($fileName)) {
                $this->dataMapping = json_decode(file_get_contents($fileName), true);
                if (!is_array($this->dataMapping)) {
                    $this->dataMapping = [];
                }
            }
        }
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    public function isIdImported($id)
    {
        return isset($this->dataMapping[$id]);
    }

    /**
     * @param string $id
     *
     * @return null|string
     */
    public function isProviderIdMapped($id)
    {
        $dataPhoneId = null;
        if (isset($this->providerMapping[$id])) {
            $dataPhoneId = $this->providerMapping[$id];
        } else {
            $logFileName = $this->getLogPath($this->provider);
            file_put_contents($logFileName, "\n" . $id);
        }

        return $dataPhoneId;
    }

    /**
     * @param string $id
     */
    public function updateDataMapping($id)
    {
        $this->dataMapping = array_merge($this->dataMapping, [$id => 1]);
    }

    public function saveDataMapping()
    {
        var_dump($this->dataMapping);
        $fileName = $this->getMappingPath($this->dataProvider);
        file_put_contents($fileName, json_encode($this->dataMapping));
    }

    /**
     * @param $providerName
     *
     * @return string
     */
    private function getMappingPath($providerName)
    {
        $fileName = '../app/data/map/' . $providerName . '.json';

        return $fileName;
    }

    /**
     * @param $providerName
     *
     * @return string
     */
    private function getLogPath($providerName)
    {
        $fileName = '../app/logs/provider/' . $providerName . '.json';

        return $fileName;
    }
} 