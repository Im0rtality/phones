<?php

namespace Phones\DataProviders\GsmArenaComBundle\Service;

use Doctrine\ORM\EntityManager;
use Phones\PhoneBundle\Entity\Phone;
use Phones\PhoneBundle\Services\MappingHelper;

class MainDownloader
{
    /** @var  array */
    private $phoneBrandLinkMap;

    /** @var  BrandDownloader */
    private $brandDownloader;

    /** @var  EntityManager */
    private $entityManager;

    /** @var  MappingHelper */
    private $mappingHelper;

    /**
     * @param BrandDownloader $brandDownloader
     */
    public function setBrandDownloader($brandDownloader)
    {
        $this->brandDownloader = $brandDownloader;
    }

    /**
     * @param array $phoneBrandLinkMap
     */
    public function setPhoneBrandLinkMap($phoneBrandLinkMap)
    {
        $this->phoneBrandLinkMap = $phoneBrandLinkMap;
    }

    /**
     * @param EntityManager $entityManager
     */
    public function setEntityManager($entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param MappingHelper $mappingHelper
     */
    public function setMappingHelper($mappingHelper)
    {
        $this->mappingHelper = $mappingHelper;
    }

    /**
     * @return array
     */
    public function download()
    {
        $this->brandDownloader->setMappingHelper($this->mappingHelper);

        if (!empty($this->phoneBrandLinkMap)) {
            foreach ($this->phoneBrandLinkMap as $brand => $brandFirstLink) {
                $brandPhones = $this->brandDownloader->curlPhones($brand, $brandFirstLink);
                $this->saveBrandPhones($brandPhones);
            }
        }

        $this->mappingHelper->saveDataMapping();
    }

    /**
     * @param Phone[] $brandPhones
     */
    private function saveBrandPhones($brandPhones)
    {
        foreach ($brandPhones as $phone) {
            $this->entityManager
                ->getRepository('PhonesPhoneBundle:Phone')
                ->save($phone);
        }
    }
}