<?php

namespace Phones\PhoneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CameraRate
 */
class CameraRate
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
     * @var integer
     */
    private $rate_percent;

    /**
     * @var \Phones\PhoneBundle\Entity\Phone
     */
    private $phone;


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
     * @return CameraRate
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
     * @return CameraRate
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
     * @return CameraRate
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
     * Set rate_percent
     *
     * @param integer $ratePercent
     * @return CameraRate
     */
    public function setRatePercent($ratePercent)
    {
        $this->rate_percent = $ratePercent;

        return $this;
    }

    /**
     * Get rate_percent
     *
     * @return integer 
     */
    public function getRatePercent()
    {
        return $this->rate_percent;
    }

    /**
     * Set phone
     *
     * @param \Phones\PhoneBundle\Entity\Phone $phone
     * @return CameraRate
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
}
