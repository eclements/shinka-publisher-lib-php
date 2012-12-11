<?php
include_once("config.php");
include_once("MxitUser.php");

class ShinkaBannerAd
{
	//Constant values so that we rather use the variable name, and NOT hard code the text string values in the code in multiple places:
    const TYPE_IMAGE = 'image';
    const TYPE_HTML = 'html';
    const TYPE_INVALID = 'invalid';
    const TARGET_MXIT = 'mxit';

	//The fields we need to set in the constructor so that we have enough info to later do multiple Server Ad requests using this BannerAd object:
	protected $_requestParam_age;
	protected $_requestParam_gender;
	protected $_requestParam_device;
	protected $_requestParam_deviceWidth;	
	protected $_requestParam_country;
	protected $_requestParam_xid;
	protected $_clientDeviceIP;
	
	//whizpool: variable defination to save the ad object we get from shinka
	protected $_ad;
	
	
	//The actual fields of this BannerAd which we get from OpenX after we have done a ServerAd request:
    protected $_type;
	protected $_mediaUrl;
	protected $_mediaHeight;
	protected $_mediaWidth;
	protected $_alt;
	protected $_target;
	protected $_beacon;
	protected $_click;
	protected $_html;
	
	//Eric: This needs to go into the config file. Split on separate lines.
	//API_SERVER defined in config.php
	//ShinkaConfig defined in config.php
	
	//Eric: Define the value that you return from doServerAdRequestForUser, rather as a field of this object: protected $ad; ?
	
	public function __construct()
	{
		$mxitUser = new MxitUser();
		$tempAge = $mxitUser->getAge();
		$this->_requestParam_age = floor($tempAge);		
		$this->_requestParam_gender = ($mxitUser->getGender() == 1)?'male':'female';		
		$this->_requestParam_device = $mxitUser->getDeviceUserAgent();
		$this->_requestParam_deviceWidth = $mxitUser->getDeviceWidth();
		$this->_requestParam_country = $mxitUser->getCurrentCountryId();		
		$this->_requestParam_xid = $mxitUser->getMxitUserId();		
		$this->_clientDeviceIP = $_SERVER['HTTP_X_FORWARDED_FOR'];		
	}
	
	public function doServerAdRequest()
    {
		$BannerRequest = array(
							'c.age' => $this->_requestParam_age,
							'c.gender' => $this->_requestParam_gender,
							'c.device' => $this->_requestParam_device,
							'c.country' => $this->_requestParam_country,
							'xid' => $this->_requestParam_xid,
							);
		$shinkaConfig = unserialize(ShinkaConfig);
							
		foreach($shinkaConfig as $size=>$adUnitId)
		{
			if ($size < $this->_requestParam_deviceWidth) 
			{
                    break;
            }
		}
		
		$BannerRequest['auid'] = $adUnitId;// 264843 for text ads;
			
		
		//Eric: The following lines are greek to me :-) Please add some comments to explain what this is doing:
		//whizpool: following is a http call to server, sending get parameters and headers
		
		$get = API_SERVER."?".http_build_query($BannerRequest); //whizpool:  api server address and get parameters to be sent
		$ch = curl_init();
		$timeout = TIMEOUT;		
		curl_setopt($ch,CURLOPT_URL,$get);
		curl_setopt($ch,CURLOPT_HTTPHEADER, array('User-Agent: Mozilla Compatible Africa Weather','X-Forwarded-For: $ip','Referer: '.REFERER,'Content-length: '.strlen($BannerRequest))); //whizpool:  defining headers to be sent with the call
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		$this->_ad = curl_exec($ch); //whizpool: get the Ad object in json format
		curl_close($ch); //Eric: Need some more comments here to understand what the $ad variable type is at this point?
		
		//Rather then return the parameter, remember we are using object orientation. Set the value as a field in this object so that it can be used in future by THIS object.		
		//Please set the following fields here, using the example provided. The reason for this is that we might need these fields in future:
		
		$decoded = json_decode($this->_ad); // decoding the json response		
		if($decoded->ads->count>0)
		{
				$this->_type = $decoded->ads->ad[0]->type;
				$this->_mediaUrl = $decoded->ads->ad[0]->creative[0]->media;
				$this->_mediaHeight = $decoded->ads->ad[0]->creative[0]->height;
				$this->_mediaWidth = $decoded->ads->ad[0]->creative[0]->width;
				$this->_alt = $decoded->ads->ad[0]->creative[0]->alt;
				if($decoded->ads->ad[0]->creative[0]->target==self::TARGET_MXIT)
				{
					$this->_target = "";
				}
				else
				{
					$this->_target = "onclick='window.open(this.href); return false;'";
				}
				
				$this->_beacon = $decoded->ads->ad[0]->creative[0]->tracking->impression;
				$this->_click = $decoded->ads->ad[0]->creative[0]->tracking->click;
				$this->_html = $decoded->ads->ad[0]->html;
		}
		
		//Use the approach from the example ad.php given in the setFromHttpResponse() method, to set this objects fields from the decoded json repsonse. 
		//At the end of this method, I want the values from the json response extracted and set as FIELDS on this OBJECT. That is the point of object oriented coding.
	}
		
		
	public function generateHTMLFromAd()  //Eric Why are you passing in the Ad parameter if this method is inside the BannerAd object. Just read the required values from the fields we have now setup above
	{
		$output="";
		if($this->_type==self::TYPE_IMAGE) // if add type is image
		{			
			$output.= "<img src=".$this->_mediaUrl." height=".$this->_mediaHeight." width=".$this->_mediaWidth." />";
			$output.= "<br /><a href=".$this->_click." ".$this->_target.">";
			$output.=$this->_alt;
			$output.= "</a>";	
		}
		elseif($this->_type==self::TYPE_HTML) // if ad type is html
		{
			$output.= "<a href=".$this->_click." ".$this->_target.">";
			$output.=$this->_html;
			$output.= "</a>";
			
			
			//Eric: Check the example, you also need to include the "onclick" logic for this add
		}
		else // if ad type is not image or html
		{
			$this->_type = self::TYPE_INVALID;
		}
		
		if($this->getType()==self::TYPE_INVALID) // check if ad type is valid image or html, if not return empty
		{
			return "";
		}
		else //whizpool: send the impression call and return the ad output
		{			
			$this->registerImpression($this->_beacon);
			return $output;
		}
		
	}
	
    public function getType()
    {
        return $this->_type;
    }

    public function isValid()
    {
        if ($this->getType() == self::TYPE_INVALID) 
		{
            return false;
        } 
		else 
		{
            return true;
        }
    }
	
	public function getMediaUrl()
    {
        return $this->_mediaUrl;
    }

	public function getMediaHeight()
    {
        return $this->_mediaHeight;
    }

	public function getMediaWidth()
    {
        return $this->_mediaWidth;
    }

	public function getAlt()
    {
        return $this->_alt;
    }

	public function getTarget()
    {
        return $this->_target;
    }

    public function getBeacon()
    {
        return $this->_beacon;
    }

	public function getClick()
    {
        return $this->_click;
    }

	public function getHtml()
    {
        return $this->_html;
    }	
	
	public function registerImpression($impression)
	{
		$get = $impression;
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$get);		
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		$impression_result = curl_exec($ch);
		curl_close($ch);		
		return $impression_result;		
	}
}

?>