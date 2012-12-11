<?php

//Include the Shinka Publisher Library
include_once("shinka-publisher-lib-php/ShinkaBannerAd.php"); 

// Create shinka banner ad object. Can be done at top of page, and re-used to display multiple banners on page.
$ShinkaBannerAd = new ShinkaBannerAd();	
	
// Do a server ad request to populate the BannerAd object with a new banner. This can be done multiple times with the same ShinkaBannerAd object to get new banners for the same user:
$ShinkaBannerAd->doServerAdRequest();
// Get HTML that should be displayed for this banner:
print $ShinkaBannerAd->generateHTMLFromAd();

print '<br/>Some more html and text here<br/>';

$ShinkaBannerAd->doServerAdRequest();
print $ShinkaBannerAd->generateHTMLFromAd(); // Get HTML that should be displayed for this banner:

?>