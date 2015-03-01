<?php

namespace Phones\PhoneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Provider
 */
class Provider
{
    /**
     * @var string
     */
    private $providerId;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $deep_link;

    /**
     * @var string
     */
    private $info;


    /**
     * Set providerId
     *
     * @param string $providerId
     * @return Provider
     */
    public function setProviderId($providerId)
    {
        $this->providerId = $providerId;

        return $this;
    }

    /**
     * Get providerId
     *
     * @return string 
     */
    public function getProviderId()
    {
        return $this->providerId;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Provider
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set deep_link
     *
     * @param string $deepLink
     * @return Provider
     */
    public function setDeepLink($deepLink)
    {
        $this->deep_link = $deepLink;

        return $this;
    }

    /**
     * Get deep_link
     *
     * @return string 
     */
    public function getDeepLink()
    {
        return $this->deep_link;
    }

    /**
     * Set info
     *
     * @param string $info
     * @return Provider
     */
    public function setInfo($info)
    {
        $this->info = $info;

        return $this;
    }

    /**
     * Get info
     *
     * @return string 
     */
    public function getInfo()
    {
        return $this->info;
    }
}
