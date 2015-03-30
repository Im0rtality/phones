<?php

namespace Phones\DataProviders\GsmArenaComBundle\Service;

use Phones\PhoneBundle\Services\Downloader;
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

    /** @var  PhoneConverter */
    private $phoneConverter;

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
     * @param array $availableOs
     */
    public function setAvailableOs($availableOs)
    {
        $this->availableOs = $availableOs;
    }

    /**
     * @param Downloader $downloader
     */
    public function setDownloader($downloader)
    {
        $this->downloader = $downloader;
    }

    /**
     * @param PhoneConverter $phoneConverter
     */
    public function setPhoneConverter($phoneConverter)
    {
        $this->phoneConverter = $phoneConverter;
    }

    /**
     * @param string $provider
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;
    }

    /**
     * @param $brand
     * @param $firsBrandPageLink
     */
    public function curlPhones($brand, $firsBrandPageLink)
    {
        $pages = $this->getAllPageLinks($firsBrandPageLink);
        $linksToPhones = $this->getLinksToPhones($pages);

        $phones = [];
        foreach ($linksToPhones as $phone) {
            if (isset($phone['link']) && isset($phone['name'])){
                $dom       = $this->getClearDom($phone['link']);

                $phoneSpecs = $this->getPhoneSpecs($dom);
                $phoneSpecs['image_link'] = $this->getImageLink($dom);
                $phoneSpecs['brand']      = $brand;
                $phoneSpecs['phone_name'] = $phone['name'];

    //            var_dump(file_put_contents('../testtttts.json', json_encode($phoneSpecs)));

                $phone = $this->phoneConverter->convert($phoneSpecs);
                $phoneId = !empty($phone) ? $phone->getPhoneId() : null;
                if (!empty($phoneId)) {
                    $phones[] = $phone;
                }

                var_dump($phone);
            }

            break;
        }
    }


    /**
     * @param \DomDocument $dom
     * @return string|null
     */
    private function getImageLink($dom)
    {
        $imageLink = null;

        $nodes     = $this->getDomByQuery($dom, "//*[contains(@id, 'specs-cp-pic')]");
        $aElements = $nodes->getElementsByTagName('img');

        /** @var \DomElement $element */
        foreach ($aElements as $element) {
            $imageLink = $element->getAttribute('src');
        }

        return $imageLink;
    }

    /**
     * @param $firsBrandPageLink
     * @return array
     */
    public function getAllPageLinks($firsBrandPageLink)
    {
        $links = [];

        $doc = $this->getClearDom($firsBrandPageLink);
        if ($doc != null) {
            //find pages
            $query = "//div[@class='nav-pages']";
            $innerHTML = $this->tidyService->getInnerHtmlByQuery($doc, $query);

            //find total pages count
            $links[] = $firsBrandPageLink;
            $xml = simplexml_load_string($innerHTML);
            /** @var \SimpleXmlElement $element */
            if (!empty($xml)) {
                foreach ($xml as $element) {
                    if ($element->getName() == 'a' && isset($element->attributes()['href'])) {
                        $links[] = $this->domain . (string)$element->attributes()['href'];
                    }
                }
            }
        }

        return $links;
    }

    /**
     * @param array $pages
     *
     * @return array
     */
    public function getLinksToPhones($pages)
    {
        $linksToPhones = [];

        foreach ($pages as $pageLink) {
            $doc = $this->getClearDom($pageLink);
            if ($doc != null) {
                //find links to phones
                $doc   = $this->getDomByQuery($doc, "//div[@id='main']");
                $nodes = $this->getNodesByQuery($doc, "//*[contains(@class, 'makers')]");

                /** @var \DOMElement $node */
                foreach ($nodes as $node) {
                    $aElements = $node->getElementsByTagName('a');
                    /** @var \DOMElement $aElement */
                    foreach ($aElements as $aElement) {
                        $link = $aElement->getAttribute('href');
                        if (!empty($link)) {
                            $linksToPhones[] = [
                                'link' => $this->domain . $aElement->getAttribute('href'),
                                'name' => preg_replace('/\s+/', ' ', trim($aElement->nodeValue))
                            ];
                        }
                    }
                }
            }
        }

        return $linksToPhones;
    }

    /**
     * @param \DOMDocument $dom
     * @return array
     */
    private function getPhoneSpecs($dom)
    {
        $phoneSpecs = [];

        if ($dom != null) {
            $tmpDom = $this->getDomByQuery($dom, "//div[@id='specs-list']");

            $simpleXml = simplexml_import_dom($tmpDom);
            /** @var \SimpleXmlElement $table */
            foreach ($simpleXml as $table) {
                $title = $this->getTableTitle($table);
                $values = $this->getValues($table);
                if (!empty($title) && !empty($values)) {
                    $phoneSpecs[$title] = $values;
                }
            }
        }

        return $phoneSpecs;
    }

    /**
     * @param $table
     * @return null|string
     */
    private function getValues($table)
    {
        $array = [];
        foreach ($table as $tBodyChild) {
            if (!empty($tBodyChild->{'td'})) {
                $subArray = [];
                foreach ($tBodyChild->{'td'} as $td) {
                    /** @var \SimpleXmlElement $td */
                    if (isset($td->attributes()['class'])) {
                        $classValue = strtolower((string)$td->attributes()['class']);
                        switch ($classValue) {
                            case "ttl":
                                $feedTitle = strtolower(trim((string)$td->{'a'}));
                                $feedTitle = preg_replace('/\s+/', ' ', trim($feedTitle));
                                $subArray['title'] = $feedTitle;
                                break;
                            case "nfo":
                                $titleValue = trim((string)$td);
                                if (empty($titleValue)) {
                                    $titleValue = trim((string)$td->{'a'});
                                }

                                $titleValue = preg_replace('/\s+/', ' ', trim($titleValue));
                                $subArray['info'] = $titleValue;
                                break;
                        }
                    }
                }
                if (isset($subArray['title']) && isset($subArray['info'])) {
                    $array[$subArray['title']] = $subArray['info'];
                }
            }
        }

        return $array;
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
     * @param $table
     *
     * @return null|string
     */
    private function getTableTitle($table)
    {
        $title = null;
        foreach ($table as $tBodyChild) {
            if (!empty($tBodyChild->{'th'})) {
                $title = strtolower(trim((string)$tBodyChild->{'th'}));
                break;
            }
        }

        return $title;
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