<?php

namespace Phones\CostProviders\MobiliLinijaBundle\Service;

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

//        $doc = $this->getClearDom($page);
//        if ($doc != null) {
//        }

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