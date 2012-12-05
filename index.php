<?php

include_once("ShinkaBannerAd.php"); //We should only need to include one main file. That file should then include all the dependancies of the library. I have decided we don't really need the ShinkaHelper class because everything is in the ShinkaBannerAd class

	// Create shinka banner ad object. Can be done at top of page, and re-used to display multiple banners on page.
	$ShinkaBannerAd = new ShinkaBannerAd($mxitUser);	
		
	// Do a server ad request to populate the BannerAd object with a new banner. This can be done multiple times with the same ShinkaBannerAd object to get new banners for the same user:
	$ShinkaBannerAd->doServerAdRequest();
	print $shinkaBannerAd->generateHTMLFromAd(); // Get HTML that should be displayed for this banner:
	
	print '<br/>Some more html and text here<br/>';

	$ShinkaBannerAd->doServerAdRequest();
	print $shinkaBannerAd->generateHTMLFromAd(); // Get HTML that should be displayed for this banner:

?>