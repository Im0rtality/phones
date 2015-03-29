<?php

namespace Phones\DataProviders\GsmArenaComBundle\Service;

use Phones\PhoneBundle\Entity\Phone;
use Phones\PhoneBundle\Services\Downloader;
use Phones\PhoneBundle\Services\TidyService;

class BrandDownloader
{
    /** @var  string */
    private $provider;

    /** @var  string */
    private $domain;

    /** @var  array */
    private $availableOs;

    /** @var  TidyService */
    protected $tidyService;

    /** @var  Downloader */
    protected $downloader;

    /** @var  string */
    private $brand;

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
     * @param string $provider
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;
    }

    /**
     * @param string $brand
     */
    public function setBrand($brand)
    {
        $this->brand = $brand;
    }

    public function curlPhones($brand, $firsBrandPageLink)
    {
        $this->brand = $brand;

        $pages = $this->getAllPageLinks($firsBrandPageLink);
        $linksToPhones = $this->getLinksToPhones($pages);

        $phones = [];
        foreach ($linksToPhones as $phoneLink) {
            $dom = $this->getClearDom($phoneLink);
            $phoneSpecs = $this->getPhoneSpecs($dom);
//            var_dump(file_put_contents('../testtttts.json', json_encode($phoneSpecs)));

            $phone = $this->parsePhone($phoneSpecs);

            if (!empty($phone)) {
                $phones[] = $phone;
            }
            break;
        }
    }

    /**
     * @param $phoneSpecs
     * @return null|Phone
     */
    public function parsePhone($phoneSpecs)
    {
        $phone = null;

        if (!empty($phoneSpecs)) {
            $phone = new Phone();

            $phone->setTechnology($this->getArrayValue($phoneSpecs, 'network/technology'));

            $phone->setBrand($this->brand);

            //phoneId
            //image

            $feedWeights = $this->getArrayValue($phoneSpecs, 'body/weight');
            if (preg_match_all('/\b\d+(?:\.\d{0,}|)/', $feedWeights, $matches)) {
                $phone->setWeight(max($matches[0]));
            }

            $feedOs = (string)$this->getArrayValue($phoneSpecs, 'platform/os');
            $os = 'other';
            foreach ($this->availableOs as $availOs) {
                if (strpos($feedOs, $availOs)) {
                    $os = $availOs;
                    break;
                }
            }
            $phone->setOs($os);


            //to fix
            $feedCpuData = (string)$this->getArrayValue($phoneSpecs, 'platform/cpu');
            if (preg_match('/(?<freq>\d\.\d) G*Hz/', $feedCpuData, $matches)) {
                if (isset($matches['freq'])) {
                    $phone->setCpuFreq($matches['freq']);
                }
            }
            $cpuCoresMap = [
                'Dual-core',
                'Quad-core',
            ];
            if (preg_match('/[^-\s].+-core/', $feedCpuData, $matches)) {
                var_dump($matches);
//                $phone->setCpuCores(isset($cpuCoresMap[$matches[0]]) ? $cpuCoresMap[$matches[0]] : null);
            }

        }

        return $phone;
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
            $className = 'nav-pages';
            $query = "//div[@class='" . $className . "']";
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
                $query = "//div[@id='main']";
                $phonesBlock = $this->tidyService->getInnerHtmlByQuery($doc, $query);

                //create dom doc from content
                $doc = new \DOMDocument();
                $doc->loadHTML($phonesBlock);

                $finder = new \DomXPath($doc);
                $nodes = $finder->query("//*[contains(@class, 'makers')]");
                /** @var \DOMElement $node */
                foreach ($nodes as $node) {
                    $aElements = $node->getElementsByTagName('a');
                    /** @var \DOMElement $aElement */
                    foreach ($aElements as $aElement) {
                        $link = $aElement->getAttribute('href');
                        if (!empty($link)) {
                            $linksToPhones[] = $this->domain . $aElement->getAttribute('href');
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
            $finder = new \DomXPath($dom);
            $query = "//div[@id='specs-list']";
            $nodes = $finder->query($query);
            $tmpDom = new \DOMDocument();
            foreach ($nodes as $node) {
                $tmpDom->appendChild($tmpDom->importNode($node, true));
            }

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

    /**
     * @param \SimpleXmlElement $element
     * @param string            $path
     *
     * @return \SimpleXmlElement
     */
    private function getElement(\SimpleXmlElement $element, $path)
    {
        $path = explode('/', $path);
        foreach ($path as $name) {
            $element = $element->{$name};
            if (!$element) {
                // does not exist, returns empty \SimpleXMLElement object
                return $element;
            }
        }
        return $element;
    }

    /**
     * @param array $array
     * @param string $path
     *
     * @return mixed|null
     */
    private function getArrayValue($array, $path)
    {
        $value = $array;
        $path = explode('/', $path);
        foreach ($path as $name) {
            if (isset($value[$name])) {
                $value = $value[$name];
            } else {
                return null;
            }
        }

        return $value;
    }
}