<?php

    $configArray = parse_ini_file("/var/app/watchmanConfig.ini");

	//AUTHENTICATION CONFIGURATION
	$clientid = $configArray["ClientID"];
	$clientsecret = $configArray["ClientSecret"];
	$clientscopes = $configArray["ClientScopes"];
	$clientredirect = $configArray["ClientRedirect"];
	
	
	//DATABASE SERVER CONFIGURATION
	$databaseServer = $configArray["DatabaseServer"] . ":" . $configArray["DatabasePort"];
	$databaseUsername = $configArray["DatabaseUsername"];
	$databasePassword = $configArray["DatabasePassword"];
	
	
	//DATABASE NAME CONFIGURATION
	$databaseName = $configArray["DatabaseName"];
	
	//SITE CONFIGURATION
	$siteURL = $configArray["SiteURL"];
	$superadmins = explode(",", str_replace(" ", "", $configArray["SuperAdmins"]));
	$sessiontime = $configArray["SessionTime"];
	$maxTableRows = $configArray["MaxTableRows"];
	$storeVisitorIPs = $configArray["StoreVisitorIPs"];

?>
