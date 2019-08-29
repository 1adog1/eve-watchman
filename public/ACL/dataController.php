<?php
	session_start();
	
	require $_SERVER['DOCUMENT_ROOT'] . "/../src/auth/accessControl.php";

	configureErrorChecking();

	require $_SERVER['DOCUMENT_ROOT'] . "/../config/config.php";
	
	checkForErrors();
	
	$PageMinimumAccessLevel = ["Super Admin", "ACL Admin"];

	checkCookies();

	determineAccess($_SESSION["AccessRoles"], $PageMinimumAccessLevel);
	
	if ($_SERVER["REQUEST_METHOD"] == "POST") {
		
		$idToAdd = htmlspecialchars($_POST["id"]);
		
		$toPull = $GLOBALS['MainDatabase']->prepare("SELECT * FROM entityaccess WHERE entityid=:entityid");
		$toPull->bindParam(':entityid', $idToAdd);
		$toPull->execute();
		$pulledArrayData = $toPull->fetchAll();	
				
		if (empty($pulledArrayData)) {
			
			$parseJson = file_get_contents("http://esi.evetech.net/latest/characters/" . urlencode($idToAdd) . "/?datasource=tranquility");
			$parseData = json_decode($parseJson, TRUE);
			$parsedName = $parseData["name"];
			$parsedCorpID = $parseData["corporation_id"];
			
			if (isset($parseData["alliance_id"])) {
				$parsedAllianceID = $parseData["alliance_id"];
			}
			else {
				$parsedAllianceID = 0;
			}
			
			$rolesToAdd = [];
			
			if (isset($_POST["acl_admin"]) and htmlspecialchars($_POST["acl_admin"]) == "true") {
				$rolesToAdd[] = "ACL Admin";
			}
			if (isset($_POST["configure_corp"]) and htmlspecialchars($_POST["configure_corp"]) == "true") {
				$rolesToAdd[] = "Configure Corp";
			}
			if (isset($_POST["configure_alliance"]) and htmlspecialchars($_POST["configure_alliance"]) == "true") {
				$rolesToAdd[] = "Configure Alliance";
			}
			
			$rolesToAdd = json_encode($rolesToAdd);
			
			$toInsert = $GLOBALS['MainDatabase']->prepare("INSERT INTO entityaccess (entityid, accessroles, entityname, corporationid, allianceid) VALUES (:entityid, :accessroles, :entityname, :corporationid, :allianceid)");
			$toInsert->bindParam(':entityid', $idToAdd);
			$toInsert->bindParam(':accessroles', $rolesToAdd);
			$toInsert->bindParam(':entityname', $parsedName);
			$toInsert->bindParam(':corporationid', $parsedCorpID);
			$toInsert->bindParam(':allianceid', $parsedAllianceID);
			
			$toInsert->execute();
			
			makeLogEntry("User Database Edit", $_SESSION["CurrentPage"], $_SESSION["Character Name"], "The character " . $idToAdd . " has been added to the ACL.");
		}
		else {
			header("Location: /ACL/?error=ID_Already_In_ACL");
			ob_end_flush();
			die();
		}
	}
	else {
		
		$toDo = htmlspecialchars($_GET["todo"]);
		
		if ($toDo == "remove") {
			$idToRemove = htmlspecialchars($_GET["id"]);

			$toPull = $GLOBALS['MainDatabase']->prepare("SELECT * FROM entityaccess WHERE entityid=:entityid");
			$toPull->bindParam(':entityid', $idToRemove);
			$toPull->execute();
			$pulledArrayData = $toPull->fetchAll();	
					
			if (!empty($pulledArrayData)) {
				$toDelete = $GLOBALS['MainDatabase']->prepare("DELETE FROM entityaccess WHERE entityid=:entityid");
				$toDelete->bindParam(':entityid', $idToRemove);
				$toDelete->execute();
				
				makeLogEntry("User Database Edit", $_SESSION["CurrentPage"], $_SESSION["Character Name"], "The ID " . $idToRemove . " has been removed from the ACL.");
			}
			else {
				header("Location: /ACL/?error=ID_Not_In_ACL");
				ob_end_flush();
				die();
			}
		}
	}


	header("Location: /ACL/");
	ob_end_flush();
	die();
?>