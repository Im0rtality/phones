<?php

namespace Phones\PhoneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BatteryChargingTime
 */
class BatteryChargingTime
{
    /**
     * @var string
     */
    private $phoneId;

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
    private $charging_min;

    /**
     * @var float
     */
    private $grade;

    /**
     * @var \Phones\PhoneBundle\Entity\Phone
     */
    private $phone;


    /**
     * Set phoneId
     *
     * @param string $phoneId
     * @return BatteryChargingTime
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
     * Set provider_id
     *
     * @param string $providerId
     * @return BatteryChargingTime
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
     * @return BatteryChargingTime
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
     * Set charging_min
     *
     * @param integer $chargingMin
     * @return BatteryChargingTime
     */
    public function setChargingMin($chargingMin)
    {
        $this->charging_min = $chargingMin;

        return $this;
    }

    /**
     * Get charging_min
     *
     * @return integer 
     */
    public function getChargingMin()
    {
        return $this->charging_min;
    }

    /**
     * Set grade
     *
     * @param float $grade
     * @return BatteryChargingTime
     */
    public function setGrade($grade)
    {
        $this->grade = $grade;

        return $this;
    }

    /**
     * Get grade
     *
     * @return float 
     */
    public function getGrade()
    {
        return $this->grade;
    }

    /**
     * Set phone
     *
     * @param \Phones\PhoneBundle\Entity\Phone $phone
     * @return BatteryChargingTime
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
