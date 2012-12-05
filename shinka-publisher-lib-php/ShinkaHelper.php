<?php
include_once("MxitUser.php");
include_once("ShinkaBannerAd.php");
class ShinkaHelper
{
	public function appendBanner($user)
	{
		// create shinka object to call ads
		$ShinkaBannerAd = new ShinkaBannerAd();		
		$ShinkaBannerAd = $ShinkaBannerAd->getShinkaBannerAd($user);
		return $ShinkaBannerAd;

	}
}

?>