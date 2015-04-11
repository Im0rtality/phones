<?php

namespace Phones\StatProviders\PhoneArenaComCameraSpeedBundle\Service;

use Doctrine\ORM\EntityManager;
use Phones\FrontEndBundle\Service\QueryHelper;
use Phones\PhoneBundle\Entity\CameraSpeed;
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
                //camera speed block
                if ($i == 2) {
                    $phoneName = null;
                    /** @var \DomElement $node */
                    foreach($rootNode->childNodes as $phoneData) {
                        /** @var \DomElement $element */
                        $values = [];
                        $order  = 0;
                        foreach($phoneData->childNodes as $element) {
                            if ($element->nodeName == 'th' &&
                                $element->getAttribute('class') == 'benchmark_searchable') {
                                $phoneName = preg_replace('/\s+/', ' ', trim($element->nodeValue));
                            } elseif ($element->nodeName == 'td' ) {
                                $numbers = $element->getElementsByTagName('strong');
                                $numbers = iterator_to_array($numbers);
                                if (isset($numbers[0])) {
                                    $value = preg_replace('/\s+/', ' ', trim($numbers[0]->nodeValue));
                                    $values[$order++] = is_numeric($value) ? $value : null;
                                }
                            }
                        }

                        if (!empty($phoneName) && !empty($values[0])) {
                            $stat = new CameraSpeed();
                            $stat->setProviderId($this->provider);
                            $stat->setOriginalPhoneName($phoneName);
                            $stat->setTakingAPicInSec((float)$values[0]);

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
     * @param \DomDocument $ratesDom
     *
     * @return \DomElement|null
     */
    private function getCameraSpeedRatesDom($ratesDom)
    {
        $cameraSpeedDom = null;

        /** @var \DomElement $node */
        foreach ($ratesDom->childNodes as $ulNode) {
            /** @var \DomElement $node */
            foreach($ulNode->childNodes as $node) {
                if ($node->nodeName == 'li' && $this->isCameraSpeedRates($node)) {
                    $cameraSpeedDom = $node;
                    break;
                }
            }
        }

        return $cameraSpeedDom;
    }

    /**
     * @param \DomElement $node
     * @return bool
     */
    private function isCameraSpeedRates($node)
    {
        $result = false;

        $spanElements = $node->getElementsByTagName('span');
        /** @var \DomElement $element */
        foreach ($spanElements as $element) {
            $value = preg_replace('/\s+/', ' ', trim($element->nodeValue));
            if ($element->getAttribute('class') == 'title' &&
                preg_match('/Camera Speed/i', $value)
            ) {
                $result = true;
                break;
            }
        }

        return $result;
    }

    /**
     * @param CameraSpeed[] $phoneStats
     */
    private function saveStats($phoneStats)
    {
        foreach ($phoneStats as $stat) {
            $this->entityManager
                ->getRepository('PhonesPhoneBundle:CameraSpeed')
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