<?php

namespace Phones\DataProviders\GsmArenaComBundle\Service;

use Doctrine\ORM\EntityManager;
use Phones\PhoneBundle\Entity\Phone;

class MainDownloader
{
    /** @var  array */
    private $phoneBrandLinkMap;

    /** @var  BrandDownloader */
    private $brandDownloader;

    /** @var  EntityManager */
    private $entityManager;

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
     * @return array
     */
    public function download()
    {
        if (!empty($this->phoneBrandLinkMap)) {
            foreach ($this->phoneBrandLinkMap as $brand => $brandFirstLink) {
                $brandPhones = $this->brandDownloader->curlPhones($brand, $brandFirstLink);
                $this->saveBrandPhones($brandPhones);
            }
        }
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