<?php

namespace Phones\CostProviders\GsmArenaLtBundle\Service;

use Doctrine\ORM\EntityManager;
use Phones\PhoneBundle\Entity\Cost;
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
        $this->brandDownloader->setEntityManager($this->entityManager);

        if (!empty($this->phoneBrandLinkMap)) {
            foreach ($this->phoneBrandLinkMap as $brand => $brandFirstLink) {
                $brandPhones = $this->brandDownloader->curlPhones($brand, $brandFirstLink);
                $this->saveBrandPhoneCosts($brandPhones);
            }
        }
    }

    /**
     * @param Cost[] $brandPhones
     */
    private function saveBrandPhoneCosts($brandPhones)
    {
        foreach ($brandPhones as $cost) {
            $this->entityManager
                ->getRepository('PhonesPhoneBundle:Cost')
                ->save($cost);
        }
    }
}