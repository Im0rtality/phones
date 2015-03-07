<?php

namespace Phones\PhoneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cost
 */
class Cost
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $phone_id;

    /**
     * @var string
     */
    private $provider_id;

    /**
     * @var string
     */
    private $original_phone_name;

    /**
     * @var float
     */
    private $cost;

    /**
     * @var string
     */
    private $deep_link;

    /**
     * @var \Phones\PhoneBundle\Entity\Phone
     */
    private $phone;

    /**
     * @var \Phones\PhoneBundle\Entity\Provider
     */
    private $provider;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set phone_id
     *
     * @param string $phoneId
     * @return Cost
     */
    public function setPhoneId($phoneId)
    {
        $this->phone_id = $phoneId;

        return $this;
    }

    /**
     * Get phone_id
     *
     * @return string 
     */
    public function getPhoneId()
    {
        return $this->phone_id;
    }

    /**
     * Set provider_id
     *
     * @param string $providerId
     * @return Cost
     */
    public function setProviderId($providerId)
    {
        $this->provider_id = $providerId;

        return $this;
    }

    /**
     * Get provider_id
     *
     * @return string 
     */
    public function getProviderId()
    {
        return $this->provider_id;
    }

    /**
     * Set original_phone_name
     *
     * @param string $originalPhoneName
     * @return Cost
     */
    public function setOriginalPhoneName($originalPhoneName)
    {
        $this->original_phone_name = $originalPhoneName;

        return $this;
    }

    /**
     * Get original_phone_name
     *
     * @return string 
     */
    public function getOriginalPhoneName()
    {
        return $this->original_phone_name;
    }

    /**
     * Set cost
     *
     * @param float $cost
     * @return Cost
     */
    public function setCost($cost)
    {
        $this->cost = $cost;

        return $this;
    }

    /**
     * Get cost
     *
     * @return float 
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * Set deep_link
     *
     * @param string $deepLink
     * @return Cost
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
     * Set phone
     *
     * @param \Phones\PhoneBundle\Entity\Phone $phone
     * @return Cost
     */
    public function setPhone(\Phones\PhoneBundle\Entity\Phone $phone = null)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return \Phones\PhoneBundle\Entity\Phone 
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set provider
     *
     * @param \Phones\PhoneBundle\Entity\Provider $provider
     * @return Cost
     */
    public function setProvider(\Phones\PhoneBundle\Entity\Provider $provider = null)
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * Get provider
     *
     * @return \Phones\PhoneBundle\Entity\Provider 
     */
    public function getProvider()
    {
        return $this->provider;
    }
}
