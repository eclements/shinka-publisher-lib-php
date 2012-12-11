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
	protected $_adUnitIDToUse;
	
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

		//Decide which AdUnitID to use based on the user device width:

		    $deviceWidth = $this->_requestParam_deviceWidth;
			if ($this->_requestParam_xid == TESTUSER) print 'DeviceWidth:'. $deviceWidth . '<br/>'; 
			
			if ($deviceWidth >= 320) 
			{
                $this->_adUnitIDToUse = AdUnitID_320;			
            }
			elseif ($deviceWidth >= 216)
			{
				$this->_adUnitIDToUse = AdUnitID_216;
			}
			elseif ($deviceWidth >= 168)
			{
				$this->_adUnitIDToUse = AdUnitID_168;
			}
			else
			{
				$this->_adUnitIDToUse = AdUnitID_120;
			}
			if ($this->_requestParam_xid == TESTUSER) print 'AdUnitIDToUse:' . $this->_adUnitIDToUse . '<br/>';			
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
							
		$BannerRequest['auid'] = $this->_adUnitIDToUse;
			
		//Following is a http call to server, sending get parameters and headers
		$get = API_SERVER."?".http_build_query($BannerRequest); //api server address and get parameters to be sent
		$curlSessionHandle = curl_init();
		$timeout = TIMEOUT;		
		curl_setopt($curlSessionHandle,CURLOPT_URL,$get);
		
		if ($this->_requestParam_xid == TESTUSER) print 'URLUsed: ' . $get . '<br/>';
		
		//Defining headers to be sent with the call
		curl_setopt($curlSessionHandle, CURLOPT_USERAGENT, "Mozilla Compatible");
		curl_setopt($curlSessionHandle, CURLOPT_REFERER, REFERER);
		curl_setopt($curlSessionHandle,CURLOPT_HTTPHEADER, array('X-Forwarded-For: '.$this->_clientDeviceIP)); //'Content-length: '.strlen($BannerRequest) 
		curl_setopt($curlSessionHandle,CURLOPT_RETURNTRANSFER,1);
		
		//Get the Ad object in json format
		$this->_ad = curl_exec($curlSessionHandle); 
		curl_close($curlSessionHandle); 
		
		// decoding the json response		
		$decodedBody = json_decode($this->_ad); 
		
        if (isset($decodedBody->ads->version) && $decodedBody->ads->version == 1) {
            if (isset($decodedBody->ads->count) && $decodedBody->ads->count > 0) {
                //if (isset($decodedBody->ads->ad[0]) && is_array($decodedBody->ads->ad[0])) {		
				if (isset($decodedBody->ads->ad)) {	
					
					if ($this->_requestParam_xid == TESTUSER) print 'Decoding ad...<br/>';
					$ad = $decodedBody->ads->ad[0];
					
					switch ($ad->type) {
						case 'image':
						try {	
						    if ($this->_requestParam_xid == TESTUSER) print 'Type: Image Ad<br/>';
							
							$creative = $ad->creative[0];
							
							$this->_mediaUrl = $creative->media;
							$this->_mediaHeight = $creative->height;
							$this->_mediaWidth = $creative->width;
							$this->_alt = $creative->alt;						
							$this->_beacon = $creative->tracking->impression;
							$this->_click = $creative->tracking->click;				
							$this->_type = $this::TYPE_IMAGE;
							
							if ($this->_requestParam_xid == TESTUSER) print '_mediaUrl: '.$creative->media.'<br/>';
							
							if($creative->target==self::TARGET_MXIT)
							{
								$this->_target = "";
							}
							else
							{
								$this->_target = "onclick='window.open(this.href); return false;'";
							}		
							if ($this->_requestParam_xid == TESTUSER) print '_target: '.$this->_target.'<br/>';							
							
				        } catch (Exception $e) {
                                $this->_type = $this::TYPE_INVALID;
                        }
						break;
						case 'html':
                        try {
						    if ($this->_requestParam_xid == TESTUSER) print 'Type: HTML Ad<br/>';
							
                            $this->_html = $ad->html;
                            $this->_type = $this::TYPE_HTML;
                        } catch (Exception $e) {
                            $this->_type = $this::TYPE_INVALID;
                        }
						break;	
					}
				}
				else
				{
					if ($this->_requestParam_xid == TESTUSER) print 'No ad returned: <br/>';
					if ($this->_requestParam_xid == TESTUSER) print $this->_ad .'<br/>';
				}				
			}
			else
			{
				if ($this->_requestParam_xid == TESTUSER) print 'Error 3<br>';
			}
		}
		else
		{
			if ($this->_requestParam_xid == TESTUSER) print 'Error 4<br>';
		}		
	}
		
	public function generateHTMLFromAd()  
	{
		if ($this->_requestParam_xid == TESTUSER) print 'Generating HTML...<br/>';
		
		$output="";
		if($this->_type==self::TYPE_IMAGE) // if add type is image
		{	
			if (IS_RESIZE_IMAGES)
			{
				//With on the fly resizing:
				$imageURL = '/image-resizer.php?url=' . urlencode($this->_mediaUrl) . '&width=' . $this->_mediaWidth . '&height=' . $this->_mediaHeight . '&device=' . $this->_requestParam_deviceWidth;
			}
			else 
			{
				//No resizing:
				$imageURL = $this->_mediaUrl;			
			}
			
			if ($this->_requestParam_xid == TESTUSER) print 'Image URL: ' . $imageURL;
			if ($this->_requestParam_xid == TESTUSER) print 'Image Link: <a href="' . $imageURL . '" onclick="window.open(this.href); return false;">link</a>';
				
			$imageHTML_Tag = '<img src="' .$imageURL. '" align="middle" />';
			$output.= $imageHTML_Tag;
				
			$output.= '<a href="' .$this->_click. '" '.$this->_target. '>' . $this->_alt . '</a>';
			
			$this->registerImpression($this->_beacon);
			return $output;
		}
		elseif($this->_type==self::TYPE_HTML) // if ad type is html
		{
			//$output.= "<a href=".$this->_click." ".$this->_target.">";
			$output.=$this->_html;
			//$output.= "</a>";
			
			$this->registerImpression($this->_beacon);
			return $output;
		}
		else // if ad type is not image or html
		{
			$this->_type = self::TYPE_INVALID;
			return "";
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
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla Compatible");
		curl_setopt($ch, CURLOPT_REFERER, REFERER);
		curl_setopt($ch,CURLOPT_HTTPHEADER, array('X-Forwarded-For: '.$this->_clientDeviceIP));		
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		$impression_result = curl_exec($ch);
		curl_close($ch);		
		return $impression_result;		
	}
}

?>