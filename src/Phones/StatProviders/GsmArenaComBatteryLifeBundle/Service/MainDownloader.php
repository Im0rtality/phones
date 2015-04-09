<?php

namespace Phones\StatProviders\GsmArenaComBatteryLifeBundle\Service;

use Doctrine\ORM\EntityManager;
use Phones\PhoneBundle\Entity\BatteryLife;
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
            $query = "//table[@class='keywords persist-area']";
            $ratesDom = $this->getDomByQuery($doc, $query);

            $trElements = $ratesDom->getElementsByTagName('tr');
            /** @var \DomElement $phoneData */
            foreach ($trElements as $phoneData) {
                $phoneName       = null;
                $enduranceRating = null;
                $talkTime        = null;
                $webBrowsing     = null;
                $videoPlayback   = null;

                $tdElements = $phoneData->getElementsByTagName('td');
                $i = 0;
                $values = [];
                /** @var \DomElement $tdElement */
                foreach ($tdElements as $tdElement) {
                    if ($tdElement->getAttribute('class') == 'lalign') {
                        $phoneName = preg_replace('/\s+/', ' ', trim($tdElement->nodeValue));
                    } else {
                        $value = preg_replace('/\s+/', ' ', trim($tdElement->nodeValue));
                        $values[$i] = $value;
                    }
                    $i++;
                }

                foreach ($values as $key => $value) {
                    if (preg_match('/(?<value>[\d:]+)h/i', $value, $matches)) {
                        $time        = explode(':', $matches['value']);
                        $hoursToMins = isset($time[0]) ? $time[0] * 60: 0;
                        $minutes     = isset($time[1]) ? $time[1]: 0;
                        $values[$key] = $hoursToMins + $minutes;
                    }
                }

                $enduranceRating = !empty($values[1]) ? (int)$values[1] : null;
                $talkTime        = !empty($values[2]) ? (int)$values[2] : null;
                $webBrowsing     = !empty($values[3]) ? (int)$values[3] : null;
                $videoPlayback   = !empty($values[4]) ? (int)$values[4] : null;

                if (!empty($phoneName) && !empty($enduranceRating)) {
                    $stat = new BatteryLife();
                    $stat->setProviderId($this->provider);
                    $stat->setOriginalPhoneName($phoneName);
                    $stat->setEnduranceRatingMin($enduranceRating);
                    $stat->setTalkTimeMin($talkTime);
                    $stat->setWebBrowsingMin($webBrowsing);
                    $stat->setVideoPlaybackMin($videoPlayback);

                    $mappedId = $this->mappingHelper->isProviderIdMapped($phoneName);
                    if ($mappedId != null) {
                        $stat->setPhoneId($mappedId);
                        $stats[] = $stat;
                    }
                }
            }
        }

        return $stats;
    }

    /**
     * @param BatteryLife[] $phoneStats
     */
    private function saveStats($phoneStats)
    {
        foreach ($phoneStats as $stat) {
            $this->entityManager
                ->getRepository('PhonesPhoneBundle:BatteryLife')
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