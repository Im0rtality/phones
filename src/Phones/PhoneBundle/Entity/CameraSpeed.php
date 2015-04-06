<?php

namespace Phones\PhoneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CameraSpeed
 */
class CameraSpeed
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
     * @var float
     */
    private $taking_a_pic_in_sec;

    /**
     * @var float
     */
    private $taking_an_hdr_pic_in_sec;

    /**
     * @var integer
     */
    private $camera_speed_score;

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
     * @return CameraSpeed
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
     * @return CameraSpeed
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
     * @return CameraSpeed
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
     * Set taking_a_pic_in_sec
     *
     * @param float $takingAPicInSec
     * @return CameraSpeed
     */
    public function setTakingAPicInSec($takingAPicInSec)
    {
        $this->taking_a_pic_in_sec = $takingAPicInSec;

        return $this;
    }

    /**
     * Get taking_a_pic_in_sec
     *
     * @return float 
     */
    public function getTakingAPicInSec()
    {
        return $this->taking_a_pic_in_sec;
    }

    /**
     * Set taking_an_hdr_pic_in_sec
     *
     * @param float $takingAnHdrPicInSec
     * @return CameraSpeed
     */
    public function setTakingAnHdrPicInSec($takingAnHdrPicInSec)
    {
        $this->taking_an_hdr_pic_in_sec = $takingAnHdrPicInSec;

        return $this;
    }

    /**
     * Get taking_an_hdr_pic_in_sec
     *
     * @return float 
     */
    public function getTakingAnHdrPicInSec()
    {
        return $this->taking_an_hdr_pic_in_sec;
    }

    /**
     * Set camera_speed_score
     *
     * @param integer $cameraSpeedScore
     * @return CameraSpeed
     */
    public function setCameraSpeedScore($cameraSpeedScore)
    {
        $this->camera_speed_score = $cameraSpeedScore;

        return $this;
    }

    /**
     * Get camera_speed_score
     *
     * @return integer 
     */
    public function getCameraSpeedScore()
    {
        return $this->camera_speed_score;
    }

    /**
     * Set grade
     *
     * @param float $grade
     * @return CameraSpeed
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
     * @return CameraSpeed
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
