<?php

namespace Phones\PhoneBundle\Services;

use Doctrine\ORM\EntityManager;
use Phones\PhoneBundle\Entity\Mapping;

class MappingHelper
{
    /** @var string  */
    private $dataProvider = 'gsmArenaCom';

    /** @var string */
    private $provider;

    /** @var array  */
    private $dataMapping = [];

    /** @var array */
    private $providerMapping = [];

    /** @var EntityManager */
    private $entityManager;

    /**
     * @param string        $provider
     * @param EntityManager $entityManager
     */
    function __construct($provider, $entityManager)
    {
        $this->provider      = $provider;
        $this->entityManager = $entityManager;

        //load data mapping
        $fileName = $this->getMappingPath($this->dataProvider);
        if (file_exists($fileName)) {
            $this->dataMapping = json_decode(file_get_contents($fileName), true);

            //make a copy
            if (empty($provider)) {
                $copyFileName = $this->getMappingPath($this->dataProvider . '_' . date('Y-m-d-h-m-s'));
                file_put_contents($copyFileName, json_encode($this->dataMapping));
            }

            if (!is_array($this->dataMapping)) {
                $this->dataMapping = [];
            }
        }

        //load provider mapping
        if (!empty($provider)) {
            $fileName = $this->getMappingPath($this->provider);
            if (file_exists($fileName)) {
                $this->providerMapping = json_decode(file_get_contents($fileName), true);

                //make backup
                $copyFileName = $this->getMappingPath($this->provider . '_' . date('Y-m-d-h-m-s'));
                file_put_contents($copyFileName, json_encode($this->providerMapping));

                if (!is_array($this->providerMapping)) {
                    $this->providerMapping = [];
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
        } elseif (isset($this->dataMapping[$id])) {
            //try to map dynamically
//            $this->providerMapping[$id] = $id;
            $dataPhoneId = $id;
        } else {
            $allMatches = [];
            foreach (array_keys($this->dataMapping) as $dataFieldId) {
                if (preg_match('/'.$dataFieldId.'/i', $id)) {
                    $allMatches[] = $dataFieldId;
                }
            }
            if (!empty($allMatches)) {
                $dataPhoneId = max($allMatches);
            }

//            $this->providerMapping[$id] = $dataPhoneId;
//            if ($dataPhoneId == null) {
//                $logFileName = $this->getLogPath($this->provider);
//                file_put_contents($logFileName, "\n" . $id, FILE_APPEND);
//            }
        }

        $mapping = new Mapping();
        $mapping->setUniqId($this->provider . '-' . $id);
        $mapping->setOriginalProviderPhoneName($id);
        $mapping->setPhoneId($dataPhoneId);
        $mapping->setProviderId($this->provider);
        $this->entityManager->getRepository('PhonesPhoneBundle:Mapping')->save($mapping);

        return $dataPhoneId;
    }

    /**
     * @param string $id
     */
    public function updateDataMapping($id)
    {
        $this->dataMapping[$id] = 1;
    }

    public function saveDataMapping()
    {
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
        $fileName = '../app/logs/provider/' . $providerName . '.log';

        return $fileName;
    }
} 