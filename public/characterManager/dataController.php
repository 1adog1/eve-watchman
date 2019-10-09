<?php
	session_start();

	require $_SERVER['DOCUMENT_ROOT'] . "/../src/auth/accessControl.php";
	
	configureErrorChecking();

	require $_SERVER['DOCUMENT_ROOT'] . "/../config/config.php";
	
	checkForErrors();
	
	$PageMinimumAccessLevel = ["Super Admin", "ACL Admin", "Character Controller"];

	checkCookies();
	
	determineAccess($_SESSION["AccessRoles"], $PageMinimumAccessLevel);
    
    if (isset($_GET["todo"]) and htmlspecialchars($_GET["todo"]) == "remove") {
        
        $toRemove = htmlspecialchars($_GET["id"]);
        
        $toPull = $GLOBALS['MainDatabase']->prepare("SELECT * FROM relays WHERE id = :id");
        $toPull->bindParam(':id', $toRemove);
        $toPull->execute();
        $relayData = $toPull->fetchAll();
        
        if (!empty($relayData)) {
        
            if ((in_array("Super Admin", $_SESSION["AccessRoles"])) or (in_array("Configure Alliance", $_SESSION["AccessRoles"]) and $relayData[0]["allianceid"] == $_SESSION["AllianceID"] and $_SESSION["AllianceID"] !== 0) or (in_array("Configure Corp", $_SESSION["AccessRoles"]) and $relayData[0]["corpid"] == $_SESSION["CorporationID"])) {
                
                $toDelete = $GLOBALS['MainDatabase']->prepare("DELETE FROM relays WHERE id=:id");
                $toDelete->bindParam(':id', $toRemove);
                $toDelete->execute();
                
                makeLogEntry("User Database Edit", $_SESSION["CurrentPage"], $_SESSION["Character Name"], "The relay character " . $toRemove . " has been deleted.");
                
            }
            
            else {
                
                header("Location: /characterManager/?error=please_do_not_mess_with_the_form_html");
                ob_end_flush();
                die();					
                
            }
            
        }
        
        else {

            header("Location: /characterManager/?error=please_do_not_mess_with_the_form_html");
            ob_end_flush();
            die();
            
        }
        
    }
    else {

        header("Location: /characterManager/?error=todo_not_specified");
        ob_end_flush();
        die();	
        
    }

	header("Location: /characterManager/");
	ob_end_flush();
	die();

?>