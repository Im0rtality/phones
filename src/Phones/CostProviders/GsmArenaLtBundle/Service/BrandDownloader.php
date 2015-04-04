<?php

namespace Phones\CostProviders\GsmArenaLtBundle\Service;

use Doctrine\ORM\EntityManager;
use Phones\PhoneBundle\Entity\Cost;
use Phones\PhoneBundle\Services\Downloader;
use Phones\PhoneBundle\Services\MappingHelper;
use Phones\PhoneBundle\Services\TidyService;

class BrandDownloader
{
    /** @var  string */
    private $provider;

    /** @var  string */
    private $domain;

    /** @var  TidyService */
    private $tidyService;

    /** @var  Downloader */
    private $downloader;

    /** @var  string */
    private $brand;

    /** @var  MappingHelper */
    private $mappingHelper;

    /** @var  EntityManager */
    private $entityManager;

    /**
     * @param TidyService $tidyService
     */
    public function setTidyService($tidyService)
    {
        $this->tidyService = $tidyService;
    }

    /**
     * @param string $domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * @param Downloader $downloader
     */
    public function setDownloader($downloader)
    {
        $this->downloader = $downloader;
    }

    /**
     * @param MappingHelper $mappingHelper
     */
    public function setMappingHelper($mappingHelper)
    {
        $this->mappingHelper = $mappingHelper;
    }

    /**
     * @param string $provider
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;
    }

    /**
     * @param EntityManager $entityManager
     */
    public function setEntityManager($entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param $brand
     * @param $firsBrandPageLink
     *
     * @return array
     */
    public function curlPhones($brand, $firsBrandPageLink)
    {
        $this->brand = $brand;

        $pages = $this->getAllPageLinks($firsBrandPageLink);
        $costs = $this->getCosts($pages);

        return $costs;
    }
    /**
     * @param $firsBrandPageLink
     * @return array
     */
    private function getAllPageLinks($firsBrandPageLink)
    {
        $links = [];
        //there are no phones limit in first page, so there are no pages
        //don't know how long
        $links[] = $firsBrandPageLink;

        return $links;
    }

    /**
     * @param array $pages
     *
     * @return Cost[]
     */
    private function getCosts($pages)
    {
        $costs = [];

        foreach ($pages as $page) {
            $costs = array_merge($costs, $this->parseCosts($page));
        }

        return $costs;
    }

    /**
     * @param string $page
     * @return Cost[]
     */
    private function parseCosts($page)
    {
        $costs = [];

        $doc = $this->getClearDom($page);
        if ($doc != null) {
            //find pages
            $query = "//*[contains(@class, 'web-cnt-g-block')]";
            $nodesDom = $this->getDomByQuery($doc, $query);

            $query = "//div[@class='web-phone']";
            $phonesNodes = $this->getNodesByQuery($nodesDom, $query);

            /** @var \DomElement $phoneData */
            foreach ($phonesNodes as $phoneData) {
                $phoneName = null;
                $deepLink  = null;
                $costValue = null;

                //name
                $spanElements = $phoneData->getElementsByTagName('span');
                /** @var \DomElement $spanElement */
                foreach ($spanElements as $spanElement) {
                    if ($spanElement->getAttribute('class') == 'web-phone-name') {
                        $phoneName = preg_replace('/\s+/', ' ', trim($spanElement->nodeValue));
                        if (preg_match('/'.$this->brand.'/i', $phoneName, $matches)) {
                            $phoneName = trim(str_replace($matches[0], '', $phoneName));
                        }
                    }
                }
                //link
                $spanElements = $phoneData->getElementsByTagName('a');
                /** @var \DomElement $spanElement */
                foreach ($spanElements as $spanElement) {
                    $deepLink = $spanElement->getAttribute('href');
                    if (!empty($deepLink)) {
                        $deepLink = rtrim($this->domain, '/') . '/' . ltrim($deepLink, '/');
                        break;
                    }
                }
                //cost
                $spanElements = $phoneData->getElementsByTagName('div');
                /** @var \DomElement $spanElement */
                foreach ($spanElements as $spanElement) {
                    if ($spanElement->getAttribute('class') == 'web-phone-prc') {
                        $costData = preg_replace('/\s+/', ' ', trim($spanElement->nodeValue));
                        $costData = utf8_decode($costData);

                        if (preg_match('/(?<cost>[\d.]+)\*?\s[\?|€]/i', $costData, $matches)) {
                            $costValue = $matches['cost'];
                        }
                    }
                }
                if (!empty($costValue) && !empty($phoneName) && !empty($deepLink)) {
                    $feedPhoneId = $this->brand . ' ' . $phoneName;
                    $cost = new Cost();
                    $cost->setProviderId($this->provider);
                    $cost->setOriginalPhoneName($feedPhoneId);
                    $cost->setCost((float)$costValue);
                    $cost->setDeepLink($deepLink);

                    $mappedId = $this->mappingHelper->isProviderIdMapped($feedPhoneId);
                    if ($mappedId != null) {
                        $cost->setPhoneId($mappedId);
                        $costs[] = $cost;
                    }
                }
            }
        }

        return $costs;
    }

    /**
     * @param \DOMDocument $domDoc
     * @param string       $query
     *
     * @return \DOMDocument
     */
    private function getDomByQuery($domDoc, $query)
    {
        $nodes = $this->getNodesByQuery($domDoc, $query);

        $tmpDom = new \DOMDocument();
        foreach ($nodes as $node) {
            $tmpDom->appendChild($tmpDom->importNode($node, true));
        }

        return $tmpDom;
    }

    /**
     * @param \DOMDocument $domDoc
     * @param string       $query
     *
     * @return \DOMDocument
     */
    private function getNodesByQuery($domDoc, $query)
    {
        $finder = new \DomXPath($domDoc);
        $nodes = $finder->query($query);

        return $nodes;
    }

    /**
     * @param $link
     *
     * @return \DOMDocument|null
     */
    private function getClearDom($link)
    {
        $doc = null;

        $content = $this->downloader->getContent($link);

        if (!empty($content)) {
            //tide the html content
            $content = $this->tidyService->tidyTheContent($content);

            //create dom doc from content
            $doc = new \DOMDocument();
            @$doc->loadHTML($content);
        }

        return $doc;
    }
}