<?php

namespace Phones\PhoneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Mapping
 */
class Mapping
{
    /**
     * @var string
     */
    private $uniqId;

    /**
     * @var string
     */
    private $original_provider_phone_name;

    /**
     * @var string
     */
    private $phoneId;

    /**
     * @var string
     */
    private $providerId;


    /**
     * Set uniqId
     *
     * @param string $uniqId
     * @return Mapping
     */
    public function setUniqId($uniqId)
    {
        $this->uniqId = $uniqId;

        return $this;
    }

    /**
     * Get uniqId
     *
     * @return string 
     */
    public function getUniqId()
    {
        return $this->uniqId;
    }

    /**
     * Set original_provider_phone_name
     *
     * @param string $originalProviderPhoneName
     * @return Mapping
     */
    public function setOriginalProviderPhoneName($originalProviderPhoneName)
    {
        $this->original_provider_phone_name = $originalProviderPhoneName;

        return $this;
    }

    /**
     * Get original_provider_phone_name
     *
     * @return string 
     */
    public function getOriginalProviderPhoneName()
    {
        return $this->original_provider_phone_name;
    }

    /**
     * Set phoneId
     *
     * @param string $phoneId
     * @return Mapping
     */
    public function setPhoneId($phoneId)
    {
        $this->phoneId = $phoneId;

        return $this;
    }

    /**
     * Get phoneId
     *
     * @return string 
     */
    public function getPhoneId()
    {
        return $this->phoneId;
    }

    /**
     * Set providerId
     *
     * @param string $providerId
     * @return Mapping
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
}
