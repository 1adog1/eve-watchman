<?php

require $_SERVER['DOCUMENT_ROOT'] . "/../config/config.php";

$GLOBALS['MainDatabase'] = new PDO("mysql:host=$databaseServer", $databaseUsername, $databasePassword);
$GLOBALS['MainDatabase']->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$GLOBALS['MainDatabase']->exec("CREATE DATABASE IF NOT EXISTS $databaseName");
$GLOBALS['MainDatabase']->exec("use $databaseName");

if (checkTableExists($GLOBALS['MainDatabase'], "sessions") === false) {
	$GLOBALS['MainDatabase']->exec("CREATE TABLE sessions (id TEXT, accessroles TEXT, characterid BIGINT, expiration BIGINT)");
}
	
if (checkTableExists($GLOBALS['MainDatabase'], "entityaccess") === false) {
	$GLOBALS['MainDatabase']->exec("CREATE TABLE entityaccess (entityid BIGINT, accessroles TEXT, entityname TEXT, corporationid BIGINT, allianceid BIGINT)");
}

if (checkTableExists($GLOBALS['MainDatabase'], "idcache") === false) {
	$GLOBALS['MainDatabase']->exec("CREATE TABLE idcache (type TEXT, id BIGINT, name TEXT)");
}

if (checkTableExists($GLOBALS['MainDatabase'], "logs") === false) {
	$GLOBALS['MainDatabase']->exec("CREATE TABLE logs (timestamp BIGINT, type TEXT, page TEXT, actor TEXT, details TEXT, trueip TEXT, forwardip TEXT)");
}

if (checkTableExists($GLOBALS['MainDatabase'], "relays") === false) {
	$GLOBALS['MainDatabase']->exec("CREATE TABLE relays (name TEXT, id BIGINT, corpid BIGINT, corp TEXT, refreshtoken TEXT, allianceid BIGINT, alliance TEXT, roles MEDIUMTEXT)");
}

if (checkTableExists($GLOBALS['MainDatabase'], "configurations") === false) {
	$GLOBALS['MainDatabase']->exec("CREATE TABLE configurations (id TEXT, type TEXT, channel TEXT, url TEXT, pingtype TEXT, targetname TEXT, targetid TEXT, whitelist MEDIUMTEXT, timestamp BIGINT, alliance TEXT, allianceid BIGINT, corporation TEXT, corporationid BIGINT)");
    
    //The targetname and targetid columns have been decrepatated, but still remain to prevent problems when querying the table in python. 
}

if (checkTableExists($GLOBALS['MainDatabase'], "notifications") === false) {
	$GLOBALS['MainDatabase']->exec("CREATE TABLE notifications (timestamp BIGINT, type TEXT, configurationid TEXT, id BIGINT)");
}

if (checkTableExists($GLOBALS['MainDatabase'], "staggering") === false) {
	$GLOBALS['MainDatabase']->exec("CREATE TABLE staggering (corporationid BIGINT, characters TEXT, frequency BIGINT, lastrun BIGINT, currentposition BIGINT)");
}

function checkLastPage() {
	if (isset($_SESSION["CurrentPage"])) {
		$_SESSION["LastPage"] = $_SESSION["CurrentPage"];
	}
	else {
		$_SESSION["LastPage"] = "First Page";
	}	
}

function determineAccess($AccessRoles, $RequiredRoles) {
	
	$hasAccess = false;
	
	foreach ($AccessRoles as $throwaway => $roles) {
		
		if (in_array($roles, $RequiredRoles)) {
		
			$hasAccess = true;
		
		}
		
	}
	
	if ($hasAccess === false){
		makeLogEntry("Page Access Denied", $_SESSION["CurrentPage"], $_SESSION["Character Name"], "Access Roles: [" . json_encode($AccessRoles) . "] / Required: [" . json_encode($RequiredRoles) . "]");
		
		ob_flush();
		header("Location: /accessDenied");
		ob_end_flush();
		die();
	}
	elseif ($_SESSION["LastPage"] != $_SESSION["CurrentPage"]) {
		makeLogEntry("Page Access Granted", $_SESSION["CurrentPage"], $_SESSION["Character Name"], "Access Roles: [" . json_encode($AccessRoles) . "] / Required: [" . json_encode($RequiredRoles) . "]");
	}
}

function checkCookies() {
	
	require $_SERVER['DOCUMENT_ROOT'] . "/../config/config.php";
	
	$_SESSION["AuthCookie"] = "AuthID";
	
	if (isset($_COOKIE[$_SESSION["AuthCookie"]])) {
		$toPull = $GLOBALS['MainDatabase']->prepare("SELECT * FROM sessions WHERE id=:id");
		$toPull->bindParam(':id', $_COOKIE[$_SESSION["AuthCookie"]]);
		$toPull->execute();
		$authData = $toPull->fetchAll();
		
		if (!empty($authData)) {
			
			$authData = $authData[0];
			
			$_SESSION["AccessRoles"] = json_decode($authData["accessroles"], true);
			$_SESSION["CharacterID"] = $authData["characterid"];
		}
		
		else {
			setcookie($_SESSION["AuthCookie"], "", (time() - (86400)), "/");
		}
	}
		
	if (isset($_SESSION["CharacterID"]) and $_SESSION["CharacterID"] != 0 and ((!isset($_SESSION["Character Name"]) or $_SESSION["Character Name"] == "[Unknown Character]") or $_SESSION["CacheTime"] <= time())) {
					
		$_SESSION["Character Name"] = checkCache("Character", $_SESSION["CharacterID"]);
			
		$CharacterJson = file_get_contents("http://esi.evetech.net/latest/characters/" . $_SESSION["CharacterID"] . "/?datasource=tranquility");
		$CharacterData = json_decode($CharacterJson, TRUE);
		
		$_SESSION["CorporationID"] = $CharacterData["corporation_id"];
		
		$_SESSION["Corporation Name"] = checkCache("Corporation", $_SESSION["CorporationID"]);
		
		if (isset($CharacterData["alliance_id"])) {
			
			$_SESSION["AllianceID"] = $CharacterData["alliance_id"];
			$_SESSION["Alliance Name"] = checkCache("Alliance", $_SESSION["AllianceID"]);
			
		}
		
		else {
			$_SESSION["AllianceID"] = 0;
			$_SESSION["Alliance Name"] = "[No Alliance]";
		}
		
		$_SESSION["CacheTime"] = time() + 1800;
	}
	
	elseif (!isset($_SESSION["CharacterID"]) or $_SESSION["CharacterID"] == 0) {
		$_SESSION["AccessRoles"] = ["None"];
		
		$_SESSION["CharacterID"] = 0;
		$_SESSION["Character Name"] = "[Unknown Character]";
		$_SESSION["AllianceID"] = 0;
		$_SESSION["Alliance Name"] = "[Unknown Alliance]";
		$_SESSION["CorporationID"] = 0;
		$_SESSION["Corporation Name"] = "[Unknown Corporation]";		
	}
	
	if (isset($_SESSION["CharacterID"]) and $_SESSION["CharacterID"] != 0) {
	
		$toPull = $GLOBALS['MainDatabase']->prepare("SELECT * FROM entityaccess WHERE entityid=:entityid");
		$toPull->bindParam(':entityid', $_SESSION["CharacterID"]);
		$toPull->execute();
		$characterArrayData = $toPull->fetchAll();
		
		if (in_array($_SESSION["CharacterID"], $superadmins)) {
			$_SESSION["AccessRoles"] = ["Super Admin"];
		}
		elseif (!empty($characterArrayData)) {
			
			$characterArrayData = $characterArrayData[0];
			
			$_SESSION["AccessRoles"] = json_decode($characterArrayData["accessroles"], true);
		}
		else {
			$_SESSION["AccessRoles"] = [];
		}
		
		$accessRolesToAdd = json_encode($_SESSION["AccessRoles"]);
		
		if (isset($_COOKIE[$_SESSION["AuthCookie"]]) and ($_SESSION["CharacterID"] != $authData["characterid"] or $_SESSION["AccessRoles"] != json_decode($authData["accessroles"], true))) {
			$toUpdate = $GLOBALS['MainDatabase']->prepare("UPDATE sessions SET characterid = :characterid, accessroles = :accessroles WHERE id = :id");
			$toUpdate->bindParam(':accessroles', $accessRolesToAdd);
			$toUpdate->bindParam(':id', $_COOKIE[$_SESSION["AuthCookie"]]);
			$toUpdate->bindParam(':characterid', $_SESSION["CharacterID"]);
			
			$toUpdate->execute();
			
			makeLogEntry("Automated Database Edit", "Access Control", "[Server Backend]", "An authorization cookie has been updated for " . $_SESSION["CharacterID"] . ".");
		}
		
		if (!isset($_COOKIE[$_SESSION["AuthCookie"]])) {
			$bytes = random_bytes(64);
			$SessionID = bin2hex($bytes);
			$sessionExpiration = time() + $sessiontime;
			setcookie($_SESSION["AuthCookie"], $SessionID, $sessionExpiration, "/");
			
			$toInsert = $GLOBALS['MainDatabase']->prepare("INSERT INTO sessions (id, accessroles, characterid, expiration) VALUES (:id , :accessroles, :characterid, :expiration)");
			$toInsert->bindParam(':id', $SessionID);
			$toInsert->bindParam(':accessroles', $accessRolesToAdd);
			$toInsert->bindParam(':characterid', $_SESSION["CharacterID"]);
			$toInsert->bindParam(':expiration', $sessionExpiration);
			
			$toInsert->execute();
			
			makeLogEntry("Automated Database Edit", "Access Control", "[Server Backend]", "An authorization cookie has been generated for " . $_SESSION["CharacterID"] . ".");
		}	
	}
}

function purgeCookies() {
	$idsToClear = [];

	$toPull = $GLOBALS['MainDatabase']->prepare("SELECT * FROM sessions");
	$toPull->execute();
	
	$pulledArray = $toPull->fetchAll();
	
	foreach ($pulledArray as $throwaway => $arrayData) {
		if ($arrayData["expiration"] <= time()) {
			$idsToClear[$arrayData["id"]] = $arrayData["characterid"];
		}
	}
	
	foreach ($idsToClear as $sessionIDToClear => $characterIDToClear) {
		$toUpdate = $GLOBALS['MainDatabase']->prepare("DELETE FROM sessions WHERE id=:id");
		$toUpdate->bindParam(':id', $sessionIDToClear);
		$toUpdate->execute();
		
		makeLogEntry("Automated Database Edit", "Access Control", "[Server Backend]", "An authorization cookie has expired for " . $characterIDToClear . ".");
	}
}

function checkCache($cacheType, $cacheID) {
	
	$toPull = $GLOBALS['MainDatabase']->prepare("SELECT * FROM idcache WHERE type=:type AND id=:id");
	$toPull->bindParam(':type', $cacheType);
	$toPull->bindParam(':id', $cacheID);
	$toPull->execute();
	$checkData = $toPull->fetchAll();
	
	if (empty($checkData)) {
		
		$associationFound = false;
		$addingData = [];
		
		if ($cacheType == "Character") {

			$CharacterJson = file_get_contents("http://esi.evetech.net/latest/characters/" . $cacheID . "/?datasource=tranquility");
			$CharacterData = json_decode($CharacterJson, TRUE);
			
			if (!empty($CharacterData)) {
				$addingData = ["Type" => "Character", "ID" => $cacheID, "Name" => htmlspecialchars($CharacterData["name"])];
				$associationFound = true;
			}
			
		}
		elseif ($cacheType == "Corporation") {

			$CorpJson = file_get_contents("http://esi.evetech.net/latest/corporations/" . $cacheID . "/?datasource=tranquility");
			$CorpData = json_decode($CorpJson, TRUE);
			
			if (!empty($CorpData)) {
				$addingData = ["Type" => "Corporation", "ID" => $cacheID, "Name" => htmlspecialchars($CorpData["name"])];
				$associationFound = true;
			}
			
		}
		elseif ($cacheType == "Alliance") {

			$AllianceJson = file_get_contents("http://esi.evetech.net/latest/alliances/" . $cacheID . "/?datasource=tranquility");
			$AllianceData = json_decode($AllianceJson, TRUE);
			
			if (!empty($AllianceData)) {
				$addingData = ["Type" => "Alliance", "ID" => $cacheID, "Name" => htmlspecialchars($AllianceData["name"])];
				$associationFound = true;
			}
			
		}
		else {
			
			trigger_error("Unrecognized cache type '" . $cacheType . "'.", E_ERROR);
			
		}
		
		if ($associationFound === true) {

			$toInsert = $GLOBALS['MainDatabase']->prepare("INSERT INTO idcache (type, id, name) VALUES (:type, :id, :name)");
			$toInsert->bindParam(':type', $addingData["Type"]);
			$toInsert->bindParam(':id', $addingData["ID"]);
			$toInsert->bindParam(':name', $addingData["Name"]);
			$toInsert->execute();
			
			makeLogEntry("Automated Database Edit", "Access Control", "[Server Backend]", "The " . $addingData["Type"] . " ID " . $addingData["ID"] . " [" . $addingData["Name"] . "] was added to the cache.");
			
			return $addingData["Name"];
			
		}
		else {
			
			trigger_error("Error in adding " . $cacheType . " ID " . $cacheID . " to the cache.", E_ERROR);
		
		}
		
	}
	else {
		
		return $checkData[0]["name"];
		
	}
}

function checkTableExists($databaseVariable, $tableName) {
	try {
		$testVariable = $databaseVariable->query("SELECT 1 FROM $tableName LIMIT 1");
		
		$throwAwayVariable = $testVariable->fetchAll();
		
		return True;
	}
	catch (Exception $throwAwayException) {
		return False;
	}
}

function configureErrorChecking() {
	error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
	register_shutdown_function('checkForErrors');
}

function checkForErrors() {
	
	$lastError = error_get_last();
	
	if ($lastError["type"] === E_ERROR) {
		makeLogEntry("Critical Error", $lastError["file"], $_SESSION["Character Name"], $lastError["message"] . " on line " . $lastError["line"]);
	}
	if ($lastError["type"] === E_WARNING or $lastError["type"] === E_NOTICE) {
		makeLogEntry("Page Error", $lastError["file"], $_SESSION["Character Name"], $lastError["message"] . " on line " . $lastError["line"]);
	}
	
	error_clear_last();
	
}

function makeLogEntry($logType, $logPage, $logActor, $logDetails) {
	
	require $_SERVER['DOCUMENT_ROOT'] . "/../config/config.php";

	$currentTime = time();
	
	if ($storeVisitorIPs === true) {
	
		$remoteAddress = htmlspecialchars($_SERVER['REMOTE_ADDR']);
		$forwardedAddress = htmlspecialchars($_SERVER['HTTP_X_FORWARDED_FOR']);
		
	}
		
	else {
	
		$remoteAddress = "N/A";
		$forwardedAddress = "N/A";
		
	}

	$toInsert = $GLOBALS['MainDatabase']->prepare("INSERT INTO logs (timestamp, type, page, actor, details, trueip, forwardip) VALUES (:timestamp, :type, :page, :actor, :details, :trueip, :forwardip)");
	$toInsert->bindParam(':timestamp', $currentTime);
	$toInsert->bindParam(':type', $logType);
	$toInsert->bindParam(':page', $logPage);
	$toInsert->bindParam(':actor', $logActor);
	$toInsert->bindParam(':details', $logDetails);
	$toInsert->bindParam(':trueip', $remoteAddress);
	$toInsert->bindParam(':forwardip', $forwardedAddress);
	$toInsert->execute();
	
}

?>