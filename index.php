<?php

include_once("shinka-publisher-lib-php/MxitUser.php");
include_once("shinka-publisher-lib-php/ShinkaHelper.php");
include_once("shinka-publisher-lib-php/ShinkaBannerAd.php");

// create user object
$user = new MxitUser();
$user->create();

// create shinka helper object
$shinkaHelper = new ShinkaHelper();
$appendBanner = $shinkaHelper->appendBanner($user);

// create shinka banner ad object
$shinkaBannerAd = new ShinkaBannerAd();
$displayBannerAd = $shinkaBannerAd->displayBannerAd($appendBanner);

print $displayBannerAd;

?>