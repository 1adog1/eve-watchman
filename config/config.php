<?php

	//AUTHENTICATION CONFIGURATION
	$clientid = "";
	$clientsecret = "";
	$clientscopes = "esi-universe.read_structures.v1 esi-characters.read_corporation_roles.v1 esi-characters.read_notifications.v1";
	/* Client Scopes is a string of space-seperated scopes for the login process. This site requires:
	esi-universe.read_structures.v1 
	esi-characters.read_corporation_roles.v1 
	esi-characters.read_notifications.v1
	*/
	$clientredirect = "http://localhost:8080/eveauth/";
	
	
	//DATABASE SERVER CONFIGURATION
	$databaseServer = "127.0.0.1:2580";
	/* This variable MUST include a port seperated by a colon (ie. localhost:3306) 
    When using a MySQL Server on localhost you may need to use 127.0.0.1 instead for this variable.*/
	$databaseUsername = "";
	$databasePassword = "";
	
	
	//DATABASE NAME CONFIGURATION
	$databaseName = "EveWatchmanDatabase";
	/* This database will be created automatically on connection, it does not need to be created manually. */
		
	
	//SITE CONFIGURATION
	$siteURL = "http://localhost:8080";
	$superadmins = [];
	/* Super Admins is an array of character IDs in integer form. */
	$sessiontime = 43200;
	/* Session Time is an integer of seconds after logging in that a character's session will be invalidated. */
	$maxTableRows = 2500;
	/* Max Table Rows is the maximum amount of rows a table will display */
	$storeVisitorIPs = false;

?>