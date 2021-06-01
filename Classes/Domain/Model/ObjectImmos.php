<?php

namespace ChrisGruen\RealtyManager\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Objectimmos extends AbstractEntity
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
    protected $object_number;
    
    /**
     * @var string
     */
    protected $title;
    
    /**
     * @var int
     */
    protected $object_type;
    
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
     * @param int $objectNumber object_number
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
}