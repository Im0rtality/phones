<?php

namespace Phones\StatProviders\DxOMarkComBundle\Service;

use Doctrine\ORM\EntityManager;
use Phones\PhoneBundle\Entity\CameraRate;
use Phones\PhoneBundle\Services\Downloader;
use Phones\PhoneBundle\Services\MappingHelper;
use Phones\PhoneBundle\Services\TidyService;

class MainDownloader
{
    /** @var  array */
    private $statsLinks;

    /** @var  EntityManager */
    private $entityManager;

    /** @var  MappingHelper */
    private $mappingHelper;

    /** @var  Downloader */
    private $downloader;

    /** @var  TidyService */
    private $tidyService;

    /**
     * @param array $statsLinks
     */
    public function setStatsLinks($statsLinks)
    {
        $this->statsLinks = $statsLinks;
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
     * @param Downloader $downloader
     */
    public function setDownloader($downloader)
    {
        $this->downloader = $downloader;
    }

    /**
     * @param TidyService $tidyService
     */
    public function setTidyService($tidyService)
    {
        $this->tidyService = $tidyService;
    }

    /**
     * @return array
     */
    public function download()
    {
        if (!empty($this->statsLinks)) {
            foreach ($this->statsLinks as $link) {
                $phoneStats = $this->curlData($link);
                $this->saveStats($phoneStats);
            }
        }
    }

    private function curlData($link)
    {
        $stats = [];
        $doc = $this->getClearDom($link);

        if ($doc != null) {
            $query = "//div[@class='block-camera']";
            $nodesDom = $this->getDomByQuery($doc, $query);

            $query = "//li[@class='block-type-11']";
            $nodes = $this->getNodesByQuery($nodesDom, $query);

            foreach ($nodes as $node) {
                var_dump($node);
            }
        }

        return $stats;
    }

    /**
     * @param CameraRate[] $phoneStats
     */
    private function saveStats($phoneStats)
    {
//        foreach ($phoneStats as $stat) {
//            $this->entityManager
//                ->getRepository('PhonesPhoneBundle:CameraRate')
//                ->save($stat);
//        }
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