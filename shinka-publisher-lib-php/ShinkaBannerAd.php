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
	protected $_requestParam_country;
	protected $_requestParam_xid;
	protected $_clientDeviceIP;
	
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

	protected $API_SERVER = 'http://ox-d.shinka.sh/ma/1.0/arj';
	protected $shinkaConfig = array("320"=>"264843", "216"=>"264846","168"=>"264845","120"=>"264844"); //Eric: This needs to go into the config file. Split on separate lines.
	
	//Eric: Define the value that you return from doServerAdRequestForUser, rather as a field of this object: protected $ad; ?
	
	public function __construct(MxitUser $mxitUser)
	{
		$tempAge = $mxitUser->getAge(); // defined in classes/users.php
		$_requestParam_age = floor($tempAge);
		
		$_requestParam_gender = ($mxitUser->getGender() == 1)?'male':'female';
		
		$_requestParam_device = $mxitUser->getDeviceUserAgent();
		
		$_requestParam_country = $mxitUser->getCurrentCountryId();
		
		$_requestParam_xid = $mxitUser->getMxitUserId();
		
		$_clientDeviceIP = $_SERVER['HTTP_X_FORWARDED_FOR'];		
	}
	
	public function doServerAdRequest()
    {
		$BannerRequest = array(
							'c.age' => $_requestParam_age,
							'c.gender' => $_requestParam_gender,
							'c.device' => $_requestParam_device,
							'c.country' => $_requestParam_country,
							'xid' => $_requestParam_xid,
							);
							
		foreach($this->shinkaConfig as $size=>$adUnitId)
		{
			if ($size < $user->getDeviceWidth()) 
			{
                    break;
            }
		}
		
		$BannerRequest['auid'] = $adUnitId;
		
		$get = $this->API_SERVER."?".http_build_query($BannerRequest);
		$ch = curl_init();
		$timeout = TIMEOUT;
		
		//Eric: The following lines are greek to me :-) Please add some comments to explain what this is doing:
		
		curl_setopt($ch,CURLOPT_URL,$get);		
		curl_setopt($ch,CURLOPT_HTTPHEADER, array('User-Agent: Mozilla Compatible Africa Weather','X-Forwarded-For: $ip','Referer: '.REFERER,'Content-length: '.strlen($BannerRequest)));
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		//curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);		
		
		$ad = curl_exec($ch); //Eric: Need some more comments here to understand what the $ad variable type is at this point?
		
		curl_close($ch);		
		
		//return $ad;  
		//Rather then return the parameter, remember we are using object orientation. Set the value as a field in this object so that it can be used in future by THIS object.		
		//Please set the following fields here, using the example provided. The reason for this is that we might need these fields in future:
		
		$_type = ;
		$_mediaUrl = ;
		$_mediaHeight = ;
		$_mediaWidth = ;
		$_alt = ;
		$_target = ;
		$_beacon = ;
		$_click = ;
		$_html = ;		
		
		//Use the approach from the example ad.php given in the setFromHttpResponse() method, to set this objects fields from the decoded json repsonse. 
		//At the end of this method, I want the values from the json response extracted and set as FIELDS on this OBJECT. That is the point of object oriented coding.
	}
		
		
	public function generateHTMLFromAd($ad)  //Eric Why are you passing in the Ad parameter if this method is inside the BannerAd object. Just read the required values from the fields we have now setup above
	{
		$decoded = json_decode($ad); // decoding the json response		
		
		if($decoded->ads->count>0)
		{
			if($decoded->ads->ad[0]->type=="image") // if add type is image
			{
				$output="";
				if($decoded->ads->ad[0]->creative[0]->target=="mxit")
				{
					$onclick = "";
				}
				else
				{
					$onclick = "onclick='window.open(this.href); return false;'";
				}
				
				$output.= "<img src=".$decoded->ads->ad[0]->creative[0]->media." height=".$decoded->ads->ad[0]->creative[0]->height." width=".$decoded->ads->ad[0]->creative[0]->width." />";
				$output.= "<a href=".$decoded->ads->ad[0]->creative[0]->tracking->click." ".$onclick.">";
				$output.=$decoded->ads->ad[0]->creative[0]->alt;
				$output.= "</a>";	
			}
			else // if ad type is html
			{
				$output = $decoded->ads->ad[0]->html;
				//Eric: Check the example, you also need to include the "onclick" logic for this add
			}
			
			//Eric: Add comment line here:
			$this->registerImpression($decoded->ads->ad[0]->creative[0]->tracking->impression);
			return $output;
		}
	}
	
    public function getType()
    {
        return $this->_type;
    }

    public function isValid()
    {
        if ($this->getType() == $this::TYPE_INVALID) {
            return false;
        } else {
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
		
		//$impression_result = file_get_contents($impression);
		return $impression_result;		
	}
}

?>