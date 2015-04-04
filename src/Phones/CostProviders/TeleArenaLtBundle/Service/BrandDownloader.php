<?php

namespace Phones\CostProviders\TeleArenaLtBundle\Service;

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

        var_dump($costs);
        return $costs;
    }
    /**
     * @param $firsBrandPageLink
     * @return array
     */
    private function getAllPageLinks($firsBrandPageLink)
    {
        $links = [];
//        $firsBrandPageLink = 'http://www.telearena.lt/telefonai/lg/visi/';
        $doc = $this->getClearDom($firsBrandPageLink);
        if ($doc != null) {
            //find pages
            $query     = "//ul[@class='pagination pagination-sm']";
            $nodes     = $this->getDomByQuery($doc, $query);
            $aElements = $nodes->getElementsByTagName('a');

            //find total pages count
            /** @var \DomElement $element */
            foreach ($aElements as $element) {
                $link = trim($element->getAttribute('href'));
                if ($link != '#' && !empty($link)) {
                    $links[] = $this->domain . $link;
                }
            }
            $links[] = $firsBrandPageLink;
        }

        $links = array_unique($links);

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
            $query = "//*[contains(@class, 'row product-list')]";
            $nodes = $this->getDomByQuery($doc, $query);
            $aElements = $nodes->getElementsByTagName('p');

            /** @var \DomElement $element */
            foreach ($aElements as $element) {
                $data = preg_replace('/\s+/', ' ', trim($element->nodeValue));
                $data = utf8_decode($data);
                $data = str_replace('?', '', $data);

                $costValue = null;
                $name      = null;
                $deepLink  = null;
                if (preg_match('/(?<cost>[\d.]+\s*€)/i', $data, $matches)) {
                    if (preg_match('/[\d.]+/', $matches['cost'], $subMatches)) {
                        $costValue = $subMatches[0];
                    }
                }

                $aTags = $element->getElementsByTagName('a');
                /** @var \DomElement $aTag */
                foreach ($aTags as $aTag) {
                    $name     = $aTag->getAttribute('title');
                    if (preg_match('/'.$this->brand.'/i', $name, $matches)) {
                        $name = trim(str_replace($matches[0], '', $name));
                    }

                    $deepLink = $aTag->getAttribute('href');
                    if (!empty($deepLink)) {
                        $deepLink = rtrim($this->domain, '/') . '/' . ltrim($deepLink, '/');
                    }
                }

                if (!empty($costValue) && !empty($name) && !empty($deepLink)) {
                    $feedPhoneId = $this->brand . ' ' . $name;
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