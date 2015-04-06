<?php

namespace Phones\PhoneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BatteryLife
 */
class BatteryLife
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
    private $endurance_rating_min;

    /**
     * @var integer
     */
    private $talk_time_min;

    /**
     * @var integer
     */
    private $web_browsing_min;

    /**
     * @var integer
     */
    private $video_playback_min;

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
     * @return BatteryLife
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
     * @return BatteryLife
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
     * @return BatteryLife
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
     * Set endurance_rating_min
     *
     * @param integer $enduranceRatingMin
     * @return BatteryLife
     */
    public function setEnduranceRatingMin($enduranceRatingMin)
    {
        $this->endurance_rating_min = $enduranceRatingMin;

        return $this;
    }

    /**
     * Get endurance_rating_min
     *
     * @return integer 
     */
    public function getEnduranceRatingMin()
    {
        return $this->endurance_rating_min;
    }

    /**
     * Set talk_time_min
     *
     * @param integer $talkTimeMin
     * @return BatteryLife
     */
    public function setTalkTimeMin($talkTimeMin)
    {
        $this->talk_time_min = $talkTimeMin;

        return $this;
    }

    /**
     * Get talk_time_min
     *
     * @return integer 
     */
    public function getTalkTimeMin()
    {
        return $this->talk_time_min;
    }

    /**
     * Set web_browsing_min
     *
     * @param integer $webBrowsingMin
     * @return BatteryLife
     */
    public function setWebBrowsingMin($webBrowsingMin)
    {
        $this->web_browsing_min = $webBrowsingMin;

        return $this;
    }

    /**
     * Get web_browsing_min
     *
     * @return integer 
     */
    public function getWebBrowsingMin()
    {
        return $this->web_browsing_min;
    }

    /**
     * Set video_playback_min
     *
     * @param integer $videoPlaybackMin
     * @return BatteryLife
     */
    public function setVideoPlaybackMin($videoPlaybackMin)
    {
        $this->video_playback_min = $videoPlaybackMin;

        return $this;
    }

    /**
     * Get video_playback_min
     *
     * @return integer 
     */
    public function getVideoPlaybackMin()
    {
        return $this->video_playback_min;
    }

    /**
     * Set grade
     *
     * @param float $grade
     * @return BatteryLife
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
     * @return BatteryLife
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
