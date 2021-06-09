<?php

namespace ChrisGruen\RealtyManager\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Objectimmo extends AbstractEntity
{      
    /**
     * @var \DateTime
     */
    protected $crdate;
    
    /**
     * @var \DateTime
     */
    protected $tstamp;
    
    /**
     * @var int
     */    
    protected $sysLanguageUid;
    
    /**
     * @var int
     */   
    protected $l10nParent;
    
    /**
     * @var string
     */
    protected $objectNumber;
    
    /**
     * @var string
     */
    protected $title;
    
    /**
     * @var int
     */
    protected $objectType;
    
    /**
     * @var string
     */
    protected $street;
    
    /**
     * @var string
     */
    protected $zip;
    
    /**
     * @var int
     */
    protected $city;
    
    /**
     * @var int
     */
    protected $district;

    /**
     * @var float
     */
    protected $livingArea;
    
    /**
     * @var string
     */
    protected $teaser;
    
    /**
     * @var string
     */
    protected $discription;
    
    public function __construct()
    {
        
    }

    /**
     * Get timestamp
     *
     * @return \DateTime
     */
    public function getTstamp()
    {
        return $this->tstamp;
    }
    
    /**
     * Set time stamp
     *
     * @param \DateTime $tstamp time stamp
     */
    public function setTstamp($tstamp)
    {
        $this->tstamp = $tstamp;
    }
    
    /**
     * Get creation date
     *
     * @return \DateTime
     */
    public function getCrdate()
    {
        return $this->crdate;
    }
    
    /**
     * Set creation date
     *
     * @param \DateTime $crdate
     */
    public function setCrdate($crdate)
    {
        $this->crdate = $crdate;
    }
    
    /**
     * Set sys language
     *
     * @param int $sysLanguageUid
     */
    public function setSysLanguageUid($sysLanguageUid)
    {
        $this->_languageUid = $sysLanguageUid;
    }
    
    /**
     * Get sys language
     *
     * @return int
     */
    public function getSysLanguageUid()
    {
        return $this->_languageUid;
    }
    
    /**
     * Set l10n parent
     *
     * @param int $l10nParent
     */
    public function setL10nParent($l10nParent)
    {
        $this->l10nParent = $l10nParent;
    }
    
    /**
     * Get l10n parent
     *
     * @return int
     */
    public function getL10nParent()
    {
        return $this->l10nParent;
    }
    
    /**
     * Get object_number
     *
     * @return string
     */
    public function getObjectNumber()
    {
        return $this->objectNumber;
    }
    
    /**
     * Set class_game
     *
     * @param string $objectNumber object_number
     */
    public function setObjectNumber($objectNumber)
    {
        $this->objectNumber = $objectNumber;
    }
    
    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
    
    /**
     * Set title
     *
     * @param string $title title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }
    
    /**
     * Get object_type
     *
     * @return int
     */
    public function getObjectType()
    {
        return $this->objectType;
    }
    
    /**
     * Set object_type
     *
     * @param int $objectType object_type
     */
    public function setObjectType($objectType)
    {
        $this->objectType = $objectType;
    }
    
    /**
     * Set street
     *
     * @param string $street street
     */
    public function setStreet($street)
    {
        $this->street = $street;
    }
    
    /**
     * Get street
     *
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }
    
    /**
     * Set zip
     *
     * @param string $zip zip
     */
    public function setZip($zip)
    {
        $this->zip = $zip;
    }
    
    /**
     * Get zip
     *
     * @return string
     */
    public function getZip()
    {
        return $this->zip;
    }
    
    /**
     * Set city
     *
     * @param string $city city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }
    
    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }
    
    /**
     * Set district
     *
     * @param string $district district
     */
    public function setDistrict($district)
    {
        $this->district = $district;
    }
    
    /**
     * Get district
     *
     * @return string
     */
    public function getDistrict()
    {
        return $this->district;
    }
    
    /**
     * Set country
     *
     * @param string $country country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }
    
    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }
    
    /**
     * Set show_address
     *
     * @param int $showAddress show_address
     */
    public function setShowAddress($showAddress)
    {
        $this->showAddress = $showAddress;
    }
    
    /**
     * Get show_address
     *
     * @return int
     */
    public function getShowAddress()
    {
        return $this->showAddress;
    }
    
    /**
     * Set has_coordinates
     *
     * @param int $hasCoordinates has_coordinates
     */
    public function setHasCoordinates($hasCoordinates)
    {
        $this->hasCoordinates = $hasCoordinates;
    }
    
    /**
     * Get has_coordinates
     *
     * @return int
     */
    public function getHasCoordinates()
    {
        return $this->hasCoordinates;
    }
    
    /**
     * Set longitude
     *
     * @param float $longitude longitude
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
    }
    
    /**
     * Get longitude
     *
     * @return float
     */
    public function getLongitude()
    {
        return $this->longitude;
    }
    
    /**
     * Set latitude
     *
     * @param float $latitude latitude
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
    }
    
    /**
     * Get latitude
     *
     * @return float
     */
    public function getLatitude()
    {
        return $this->latitude;
    }
    
    /**
     * Get distance_to_the_sea
     *
     * @return int
     */
    public function getDistanceToTheSea()
    {
        return $this->distanceToTheSea;
    }
    
    /**
     * Set distance_to_the_sea
     *
     * @param int $distanceToTheSea distance_to_the_sea
     */
    public function setDistanceToTheSea($distanceToTheSea)
    {
        $this->distanceToTheSea = $distanceToTheSea;
    }
    
    /**
     * Set sea_view
     *
     * @param int $seaView sea_view
     */
    public function setSeaView($seaView)
    {
        $this->seaView = $seaView;
    }
    
    /**
     * Get sea_view
     *
     * @return int
     */
    public function getSeaView()
    {
        return $this->seaView;
    }
    
    /**
     * Set number_of_rooms
     *
     * @param float $numberOfRooms number_of_rooms
     */
    public function setNumberOfRooms($numberOfRooms)
    {
        $this->numberOfRooms = $numberOfRooms;
    }
    
    /**
     * Get number_of_rooms
     *
     * @return float
     */
    public function getNumberOfRooms()
    {
        return $this->numberOfRooms;
    }
    
    /**
     * Set livingArea
     *
     * @param float $livingArea livingArea
     */
    public function setLivingArea($livingArea)
    {
        $this->livingArea = $livingArea;
    }
    
    /**
     * Get livingArea
     *
     * @return float
     */
    public function getLivingArea()
    {
        return $this->livingArea;
    }
    
    /**
     * Set total_area
     *
     * @param string $totalArea total_area
     */
    public function setTotalArea($totalArea)
    {
        $this->totalArea = $totalArea;
    }
    
    /**
     * Get total_area
     *
     * @return string
     */
    public function getTotalArea()
    {
        return $this->totalArea;
    }
    
    /**
     * Set estate_size
     *
     * @param string $estateSize estate_size
     */
    public function setEstateSize($estateSize)
    {
        $this->estateSize = $estateSizea;
    }
    
    /**
     * Get estate_size
     *
     * @return string
     */
    public function getEstateSize()
    {
        return $this->estateSize;
    }
    
    /**
     * Set $teaser
     *
     * @param string $teaser teaser
     */
    public function setTeaser($teaser)
    {
        $this->teaser = $teaser;
    }
    
    /**
     * Get teaser
     *
     * @return string
     */
    public function getTeaser()
    {
        return $this->teaser;
    }
    
    /**
     * Set $discription
     *
     * @param string $discription discription
     */
    public function setDiscription($discription)
    {
        $this->discription = $discription;
    }
    
    /**
     * Get discription
     *
     * @return string
     */
    public function getDiscription()
    {
        return $this->Discription;
    }
}