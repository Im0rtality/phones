<?php

namespace Phones\DataProviders\GsmArenaComBundle\Service;

class MainDownloader
{
    /** @var  array */
    private $phoneBrandLinkMap;

    /** @var  BrandDownloader */
    private $brandDownloader;

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
     * @return array
     */
    public function download()
    {
        foreach ($this->phoneBrandLinkMap as $brand => $brandFirstLink) {
            $this->brandDownloader->curlPhones($brand, $brandFirstLink);
        }
    }
}