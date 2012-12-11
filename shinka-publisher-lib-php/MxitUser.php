<?php

class MxitUser
{	
	protected $_id;
    protected $_mxitUserId;
    protected $_mxitNick;
    protected $_dateRegistered;
    protected $_dateLastAccess;
    protected $_currentCity;
    protected $_currentCountryId;
    protected $_currentRegionId;
    protected $_dateOfBirth;
    protected $_deviceFeature;
    protected $_deviceHeight;
    protected $_deviceWidth;
    protected $_deviceUserAgent;
    protected $_language;
    protected $_registeredCountry;
    protected $_gender;

	//Eric: Please add the __construct method and make it call the constructFromHTTPHeaders method
	
	public function __construct()
	{
		$this->constructFromHTTPHeaders();
	}
	
    public function setId($userId)
    {
        $this->_id = (int) $userId;
        return $this;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function setMxitUserId($mxitUserId)
    {
        $this->_mxitUserId = (string) $mxitUserId;
        return $this;
    }

    public function getMxitUserId()
    {
        return $this->_mxitUserId;
    }

    public function setMxitNick($mxitNick)
    {
        $this->_mxitNick = (string) $mxitNick;
        return $this;
    }

    public function getMxitNick()
    {
        return $this->_mxitNick;
    }

    public function setDateRegistered($dateRegistered)
    {
        $date = strtotime($dateRegistered);
        $this->_dateRegistered = (string) date('Y-m-d H:i:s', $date);
        return $this;
    }

    public function getDateRegistered()
    {
        return $this->_dateRegistered;
    }

    public function setDateLastAccess($dateLastAccess)
    {
        $date = strtotime($dateLastAccess);
        $this->_dateLastAccess = (string) date('Y-m-d H:i:s', $date);
        return $this;
    }

    public function getDateLastAccess()
    {
        return $this->_dateLastAccess;
    }

    public function setCurrentCity($currentCity)
    {
        $this->_currentCity = (string) $currentCity;
        return $this;
    }

    public function getCurrentCity()
    {
        return $this->_currentCity;
    }

    public function setCurrentCountryId($currentCountryId)
    {
        $this->_currentCountryId = (string) $currentCountryId;
        return $this;
    }

    public function getCurrentCountryId()
    {
        return $this->_currentCountryId;
    }

    public function setCurrentRegionId($currentRegionId)
    {
        $this->_currentRegionId = (int) $currentRegionId;
        return $this;
    }

    public function getCurrentRegionId()
    {
        return $this->_currentRegionId;
    }

    public function setDateOfBirth($dateOfBirth)
    {
        $date = strtotime($dateOfBirth);
        $this->_dateOfBirth = (string) date('Y-m-d H:i:s', $date);
        return $this;
    }

    public function getDateOfBirth()
    {
        return $this->_dateOfBirth;
    }

    public function setDeviceFeature($deviceFeature)
    {
        $this->_deviceFeature = (int) $deviceFeature;
        return $this;
    }

    public function getDeviceFeature()
    {
        return $this->_deviceFeature;
    }

    public function setDeviceHeight($deviceHeight)
    {
        $this->_deviceHeight = (int) $deviceHeight;
        return $this;
    }

    public function getDeviceHeight()
    {
        return $this->_deviceHeight;
    }

    public function setDeviceWidth($deviceWidth)
    {
        $this->_deviceWidth = (int) $deviceWidth;
        return $this;
    }

    public function getDeviceWidth()
    {
        return $this->_deviceWidth;
    }

    public function setDeviceUserAgent($deviceUserAgent)
    {
        $this->_deviceUserAgent = (string) $deviceUserAgent;
        return $this;
    }

    public function getDeviceUserAgent()
    {
        return $this->_deviceUserAgent;
    }

    public function setLanguage($language)
    {
        $this->_language = (string) $language;
        return $this;
    }

    public function getLanguage()
    {
        return $this->_language;
    }

    public function setRegisteredCountry($registeredCountry)
    {
        $this->_registeredCountry = (string) $registeredCountry;
        return $this;
    }

    public function getRegisteredCountry()
    {
        return $this->_registeredCountry;
    }

    public function setGender($gender)
    {
        $this->_gender = (int) $gender;
        return $this;
    }

    public function getGender()
    {
        return $this->_gender;
    }
	
	
	
	
	public function mapHTTPHeadersToUserArray() 
	{
		foreach($_SERVER as $h=>$v)
		//if(ereg('HTTP_(.+)',$h,$hp))
		if(preg_match('/HTTP_(.+)/',$h,$hp))
		$headers[$hp[1]]=$v;
		return $headers;
	}

	public function getAge() 
	{
		$iTimestamp = strtotime($this->getDateOfBirth());
		$iAge = date('Y') - date('Y', $iTimestamp);
		
		if(date('n') < date('n', $iTimestamp)) 
		{
			return --$iAge;
		} 
		elseif(date('n') == date('n', $iTimestamp)) 
		{
			if(date('j') < date('j', $iTimestamp)) 
			{
				return $iAge - 1;
			} 
			else 
			{
				return $iAge;
			}
		} 
		else 
		{
			return $iAge;
		}
	}
	
	
	public function constructFromHTTPHeaders()
	{
		$headersArray = $this->mapHTTPHeadersToUserArray();
		
		$pixels = explode('x', $headersArray['UA_PIXELS']);	
		$location = explode(',', $headersArray['X_MXIT_LOCATION']);
		$profile = explode(',', $headersArray['X_MXIT_PROFILE']);
		$userid = $headersArray['X_MXIT_USERID_R'];
		$nick = $headersArray['X_MXIT_NICK'];
		$useragent = $headersArray['X_DEVICE_USER_AGENT'];
		
		// setting values for user using the sent header values
		$this->setMxitUserId($userid)
			 ->setDateOfBirth($profile[2])
			 ->setGender(($profile[3]=='Male')?1:0)
			 ->setMxitNick($nick)
			 ->setDateRegistered(date('Y-m-d H:i:s'))
			 ->setDateLastAccess(date('Y-m-d H:i:s'))
			 ->setCurrentCity($location[5])
			 ->setCurrentCountryId($location[0])
			 ->setCurrentRegionId($location[2])
			 ->setDeviceFeature($location[7])
			 ->setDeviceHeight($pixels[1])
			 ->setDeviceWidth($pixels[0])
			 ->setDeviceUserAgent($useragent)
			 ->setLanguage($profile[0])
			 ->setRegisteredCountry($profile[1]);	 
		
		
	}
	
}

?>