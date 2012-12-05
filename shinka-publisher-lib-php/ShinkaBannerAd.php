<?php
include_once("config.php");
include_once("MxitUser.php");
include_once("ShinkaHelper.php");
class ShinkaBannerAd
{
	protected $API_SERVER = 'http://ox-d.shinka.sh/ma/1.0/arj';
	
	protected $shinkaConfig = array("320"=>"264843", "216"=>"264846","168"=>"264845","120"=>"264844");
	
	public function getShinkaBannerAd($user)
    {
	
		$age = $user->getAge(); // defined in classes/users.php
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];		
		$BannerRequest = array(
							'c.age' => floor($age),
							'c.country' => $user->getCurrentCountryId(),
							'c.gender' => ($user->getGender() == 1)?'male':'female',
							'c.user' => $user->getMxitUserId(),
							'xid' => $user->getMxitUserId(),
							'c.device' => $user->getDeviceUserAgent(),
							'c.screensize' => $user->getDeviceWidth() . 'x' . $user->getDeviceHeight(),
							//'auid' => AUID,							
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
		curl_setopt($ch,CURLOPT_URL,$get);		
		curl_setopt($ch,CURLOPT_HTTPHEADER, array('User-Agent: Mozilla Compatible Africa Weather','X-Forwarded-For: $ip','Referer: '.REFERER,'Content-length: '.strlen($BannerRequest)));
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		//curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);		
		$ad = curl_exec($ch);
		curl_close($ch);		
		
		return $ad;
		
	}
		
	public function displayBannerAd($ad)
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
			}
			
			$this->registerImpression($decoded->ads->ad[0]->creative[0]->tracking->impression);
			return $output;
		}
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