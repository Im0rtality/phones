<?php

namespace Phones\StatProviders\PhoneArenaComChargingBundle\Service;

use Doctrine\ORM\EntityManager;
use Phones\FrontEndBundle\Service\QueryHelper;
use Phones\PhoneBundle\Entity\BatteryChargingTime;
use Phones\PhoneBundle\Services\Downloader;
use Phones\PhoneBundle\Services\MappingHelper;
use Phones\PhoneBundle\Services\TidyService;

class MainDownloader
{
    /** @var  string */
    private $provider;

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

    /** @var  QueryHelper */
    private $queryHelper;

    /**
     * @param string $provider
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;
    }

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
     * @param QueryHelper $queryHelper
     */
    public function setQueryHelper($queryHelper)
    {
        $this->queryHelper = $queryHelper;
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
            $this->queryHelper->updatePoints($this->provider);
        }
    }

    private function curlData($link)
    {
        $stats = [];
        $doc = $this->getClearDom($link);

        if ($doc != null) {
            $query = "//ul[@class='benchmark_topaccordeon accordeon acc_expanded']";
            $ratesDom = $this->getDomByQuery($doc, $query);

            $query = "//tbody[@class='benchmark_scroll_area']";
            $ratesDom = $this->getDomByQuery($ratesDom, $query);

            /** @var \DomElement $node */
            $i = 0;
            foreach ($ratesDom->childNodes as $rootNode) {
                //battery charging block
                if ($i == 4) {
                    $phoneName = null;
                    /** @var \DomElement $node */
                    foreach($rootNode->childNodes as $phoneData) {
                        /** @var \DomElement $element */
                        $values = [];
                        $order  = 0;
                        foreach($phoneData->childNodes as $element) {
                            if ($element->nodeName == 'td' &&
                                $element->getAttribute('class') == 'benchmark_searchable') {
                                $phoneName = preg_replace('/\s+/', ' ', trim($element->nodeValue));
                            } elseif ($element->nodeName == 'td' ) {
                                $value = preg_replace('/\s+/', ' ', trim($element->nodeValue));
                                $values[$order++] = is_numeric($value) ? $value : null;
                            }
                        }
                        if (!empty($phoneName) && !empty($values[0])) {
                            $stat = new BatteryChargingTime();
                            $stat->setProviderId($this->provider);
                            $stat->setOriginalPhoneName($phoneName);
                            $stat->setChargingMin((float)$values[0]);

                            $mappedId = $this->mappingHelper->isProviderIdMapped($phoneName);
                            if ($mappedId != null) {
                                $stat->setPhoneId($mappedId);
                                $stats[] = $stat;
                            }
                        }
                    }
                }
                $i++;
            }
        }

        return $stats;
    }

    /**
     * @param BatteryChargingTime[] $phoneStats
     */
    private function saveStats($phoneStats)
    {
        foreach ($phoneStats as $stat) {
            $this->entityManager
                ->getRepository('PhonesPhoneBundle:BatteryChargingTime')
                ->save($stat);
        }
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