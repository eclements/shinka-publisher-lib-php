<?php
include_once("ShinkaBannerAd.php");

class ShinkaHelper
{
	//To be used if you only want to get the ShinkaBannerAd object:
	public function getBannerAd()
	{
		// Create Mxit user object
		$mxitUser = new MxitUser();
		$mxitUser->createFromHTTPHeaders();  //This should not be needed as you should create a __construct method in the MxitUser object that calls this.

		// Create shinka banner ad object
		$ShinkaBannerAd = new ShinkaBannerAd($mxitUser);		//Create a __construct method that will construct the bannerAd object from the User Object and set required fields of the Ad object
		
		// Do a server ad request to populate the BannerAd object with a new banner. This can be done multiple times with the same ShinkaBannerAd object to get new banners for the same user:
		$ShinkaBannerAd = $ShinkaBannerAd->doServerAdRequest(); 
		
		return $ShinkaBannerAd;
	}

	//To be used if you don't want to pass in the MxitUser object and want the helper to create and populate it for you:
	public function getAndAppendBannerAd()
	{
		// Create Mxit user object:
		$MxitUser = new MxitUser();
		$MxitUser->createFromHTTPHeaders(); //This should not be needed as you should create a __construct method in the MxitUser object that calls this.

		return getAndAppendBannerAdFromUser($MxitUser);
	}
	
	//To be used if you are caching the user object and don't want us to create it on each call, then just pass it in to this method
	public function getAndAppendBannerAdFromUser($mxitUser)
	{
		// Create shinka banner ad object:
		$ShinkaBannerAd = new ShinkaBannerAd($mxitUser);	
		// Do a server ad request to populate the BannerAd object with a new banner. This can be done multiple times with the same ShinkaBannerAd object to get new banners for the same user:
		$ShinkaBannerAd = $ShinkaBannerAd->doServerAdRequest();

		// Get HTML that should be displayed for this banner:
		$finalBannerAdHTML = $shinkaBannerAd->generateHTMLFromAd();		
		
		return $finalBannerAdHTML;
	}
}

?>