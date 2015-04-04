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

            if (empty($provider)) {
                $this->exportDataMapping();
            }

            if (!is_array($this->dataMapping)) {
                $this->dataMapping = [];
            }
        }

        //load provider mapping
        if (!empty($this->provider)) {
            $fileName = $this->getMappingPath($this->provider);

            $this->exportProviderMapping();

            if (file_exists($fileName)) {
                $this->providerMapping = json_decode(file_get_contents($fileName), true);

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

    public function exportDataMapping()
    {
        $fileName = $this->getMappingPath($this->dataProvider);

        $results = $this->entityManager
            ->getRepository('PhonesPhoneBundle:Phone')
            ->dumpForMapping();

        file_put_contents($fileName, json_encode($results));
    }

    private function exportProviderMapping()
    {
        $fileName = $this->getMappingPath($this->provider);

        //export mapping
        $results = $this->entityManager
            ->getRepository('PhonesPhoneBundle:Mapping')
            ->dumpForMapping($this->provider);
        file_put_contents($fileName, json_encode($results));
    }

    /**
     * @param string $id
     *
     * @return null|string
     */
    public function isProviderIdMapped($id)
    {
        $dataPhoneId = null;
        if (!empty($this->providerMapping[$id]) && isset($this->dataMapping[$this->providerMapping[$id]])) {
            $dataPhoneId = $this->providerMapping[$id];
        } elseif (isset($this->dataMapping[$id])) {
            //try to map dynamically
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
     * @param $providerName
     *
     * @return string
     */
    private function getMappingPath($providerName)
    {
        $fileName = '../app/data/map/' . $providerName . '.json';

        return $fileName;
    }
} 