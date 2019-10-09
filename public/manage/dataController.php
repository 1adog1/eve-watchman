<?php
	session_start();

	require $_SERVER['DOCUMENT_ROOT'] . "/../src/auth/accessControl.php";
	
	configureErrorChecking();

	require $_SERVER['DOCUMENT_ROOT'] . "/../config/config.php";
	
	checkForErrors();
	
	$PageMinimumAccessLevel = ["Super Admin", "Configure Alliance", "Configure Corp"];

	checkCookies();
	
	determineAccess($_SESSION["AccessRoles"], $PageMinimumAccessLevel);	
		
	if ($_SERVER["REQUEST_METHOD"] == "POST" and isset($_POST["create_relay"]) and $_POST["create_relay"] == "true") {
		
		$notificationEvents = 
		["upwell_attack" => ["StructureDestroyed", "StructureLostArmor", "StructureLostShields", "StructureUnderAttack"], 
		"moon_detonation" => ["MoonminingLaserFired", "MoonminingAutomaticFracture"], 
        "moon_management" => ["MoonminingExtractionCancelled", "MoonminingExtractionFinished", "MoonminingExtractionStarted"], 
		"upwell_management" => ["StructureAnchoring", "StructureFuelAlert", "StructureOnline", "StructureUnanchoring", "StructureServicesOffline", "StructureWentHighPower", "StructureWentLowPower", "StructuresReinforcementChanged", "OwnershipTransferred"], 
		"sov_attacks" => ["EntosisCaptureStarted", "SovCommandNodeEventStarted", "SovStructureReinforced", "SovStructureDestroyed"], 
		"sov_management" => ["SovAllClaimAquiredMsg", "SovAllClaimLostMsg", "SovStructureSelfDestructRequested", "SovStructureSelfDestructFinished", "SovStructureSelfDestructCancel"], 
		"custom_office" => ["OrbitalAttacked", "OrbitalReinforced"], 
		"pos_attack" => ["TowerAlertMsg"], 
		"pos_management" => ["TowerResourceAlertMsg", "AllAnchoringMsg"]];
		
		$whitelistArray = [];
		
		$toLoopArray = [];
		
		if (isset($_POST["upwell_attack"]) and htmlspecialchars($_POST["upwell_attack"]) == "true") {
			$toLoopArray[] = "upwell_attack";
		}
		
		if (isset($_POST["moon_detonation"]) and htmlspecialchars($_POST["moon_detonation"]) == "true") {
			$toLoopArray[] = "moon_detonation";
		}
		
		if (isset($_POST["moon_management"]) and htmlspecialchars($_POST["moon_management"]) == "true") {
			$toLoopArray[] = "moon_management";
		}
		
		if (isset($_POST["upwell_management"]) and htmlspecialchars($_POST["upwell_management"]) == "true") {
			$toLoopArray[] = "upwell_management";
		}

		if (isset($_POST["sov_attacks"]) and htmlspecialchars($_POST["sov_attacks"]) == "true") {
			$toLoopArray[] = "sov_attacks";
		}
		
		if (isset($_POST["sov_management"]) and htmlspecialchars($_POST["sov_management"]) == "true") {
			$toLoopArray[] = "sov_management";
		}
		
		if (isset($_POST["custom_office"]) and htmlspecialchars($_POST["custom_office"]) == "true") {
			$toLoopArray[] = "custom_office";
		}
		
		if (isset($_POST["pos_attack"]) and htmlspecialchars($_POST["pos_attack"]) == "true") {
			$toLoopArray[] = "pos_attack";
		}
		
		if (isset($_POST["pos_management"]) and htmlspecialchars($_POST["pos_management"]) == "true") {
			$toLoopArray[] = "pos_management";
		}
		
		foreach ($toLoopArray as $throwaway => $qualifiers) {
			
			foreach ($notificationEvents[$qualifiers] as $secondThrowaway => $notifications) {
				$whitelistArray[] = $notifications;
			}
			
		}
		
		if (!empty($whitelistArray)) {

			$bytes = random_bytes(16);
			$uniqueID = bin2hex($bytes);	
			$uniqueID = "x" . $uniqueID;
			
			$whitelistArray = json_encode($whitelistArray);
			$currentTime = time();
			$platform = htmlspecialchars($_POST["platform"]);
			$webhookToAdd = $_POST["hook_url"];
            $targetIDArray = [];
            $targetIDArray[] = htmlspecialchars($_POST["target_character"]);
			$targetID = json_encode($targetIDArray);
			$targetChannel = htmlspecialchars($_POST["target_channel"]);
			$pingType = htmlspecialchars($_POST["ping_type"]);
			
			$CharacterJson = file_get_contents("http://esi.evetech.net/latest/characters/" . $targetIDArray[0] . "/?datasource=tranquility");
			$CharacterData = json_decode($CharacterJson, true);
			
			$nameArrayToAdd = [];
            $nameArrayToAdd[] = htmlspecialchars($CharacterData["name"]);
            $nameToAdd = json_encode($nameArrayToAdd);
			
			$corpIDToAdd = $CharacterData["corporation_id"];
			$corpToAdd = checkCache("Corporation", $corpIDToAdd);
			
			if (isset($CharacterData["alliance_id"])) {
				$allianceIDToAdd = $CharacterData["alliance_id"];
				$allianceToAdd = checkCache("Alliance", $allianceIDToAdd);
			}
			else {
				$allianceIDToAdd = 0;
				$allianceToAdd = "[No Alliance]";
			}
			
			if ((in_array("Super Admin", $_SESSION["AccessRoles"])) or (in_array("Configure Alliance", $_SESSION["AccessRoles"]) and $allianceIDToAdd == $_SESSION["AllianceID"] and $_SESSION["AllianceID"] !== 0) or (in_array("Configure Corp", $_SESSION["AccessRoles"]) and $corpIDToAdd == $_SESSION["CorporationID"])) {
				
				if (($platform == "slack_webhook" and strpos($webhookToAdd, "https://hooks.slack.com/services/") === 0) or ($platform == "discord_webhook" and strpos($webhookToAdd, "https://discordapp.com/api/webhooks/") === 0)) {
			
					$toInsert = $GLOBALS['MainDatabase']->prepare("INSERT INTO configurations (id, type, channel, url, pingtype, targetname, targetid, whitelist, timestamp, alliance, allianceid, corporation, corporationid) VALUES (:id, :type, :channel, :url, :pingtype, :targetname, :targetid, :whitelist, :timestamp, :alliance, :allianceid, :corporation, :corporationid)");
					$toInsert->bindParam(':id', $uniqueID);
					$toInsert->bindParam(':type', $platform);
					$toInsert->bindParam(':channel', $targetChannel);
					$toInsert->bindParam(':url', $webhookToAdd);
					$toInsert->bindParam(':pingtype', $pingType);
					$toInsert->bindParam(':targetname', $nameToAdd);
					$toInsert->bindParam(':targetid', $targetID);
					$toInsert->bindParam(':whitelist', $whitelistArray);
					$toInsert->bindParam(':timestamp', $currentTime);
					$toInsert->bindParam(':alliance', $allianceToAdd);
					$toInsert->bindParam(':allianceid', $allianceIDToAdd);
					$toInsert->bindParam(':corporation', $corpToAdd);
					$toInsert->bindParam(':corporationid', $corpIDToAdd);
					
					$toInsert->execute();

					$relayTypes = "";
					
					foreach (json_decode($whitelistArray, true) as $throwaway => $types) {
						$relayTypes .= ($types . "\n");
					}
					
					if ($platform == "discord_webhook") {
						$curlPost = curl_init($webhookToAdd);
						curl_setopt($curlPost, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($curlPost, CURLOPT_SSL_VERIFYPEER, false);
						curl_setopt($curlPost, CURLOPT_POSTFIELDS, ["content" => "**Configuration Added - [" . date("F d, Y - H:i:s", $currentTime) . "]**\nTarget Corporation: " .  $corpToAdd . "\nTarget Alliance: " . $allianceToAdd . "\nPing Type: " . $pingType . "\nNotifications Being Relayed:\n```\n" . $relayTypes . "\n```"]);
						
						curl_exec($curlPost);

						curl_close($curlPost);
					}
					else {

						$curlPost = curl_init($webhookToAdd);
						curl_setopt($curlPost, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($curlPost, CURLOPT_SSL_VERIFYPEER, false);
						curl_setopt($curlPost, CURLOPT_POSTFIELDS, ["payload" => json_encode(["text" => "*Configuration Added - [" . date("F d, Y - H:i:s", $currentTime) . "]*\nTarget Corporation: " .  $corpToAdd . "\nTarget Alliance: " . $allianceToAdd . "\nPing Type: " . $pingType . "\nNotifications Being Relayed:\n```\n" . $relayTypes . "\n```"])]);
						
						$response = json_decode(curl_exec($curlPost), true);
						
						print_r($response);

						curl_close($curlPost);
						
					}
					

					
					makeLogEntry("User Database Edit", $_SESSION["CurrentPage"], $_SESSION["Character Name"], "The configuration " . $uniqueID . " has been created.");
					
				}
				else {
					header("Location: /manage/?error=bad_webhook_url");
					ob_end_flush();
					die();					
				}
			}
			else {
				header("Location: /manage/?error=please_do_not_mess_with_the_form_html");
				ob_end_flush();
				die();
			}
		}
		else {
			header("Location: /manage/?error=whitelist_empty");
			ob_end_flush();
			die();				
		}
	}
    elseif ($_SERVER["REQUEST_METHOD"] == "POST" and isset($_POST["add_character"])) {
        
        $toAddTo = htmlspecialchars($_POST["add_character"]);
        $toAdd = htmlspecialchars($_POST["target_add_character"]);
        
        $CharacterJson = file_get_contents("http://esi.evetech.net/latest/characters/" . $toAdd . "/?datasource=tranquility");
        $CharacterData = json_decode($CharacterJson, true);
        
        $nameToAdd = htmlspecialchars($CharacterData["name"]);
        
        $corpIDToAdd = $CharacterData["corporation_id"];
        $corpToAdd = checkCache("Corporation", $corpIDToAdd);
        
        if (isset($CharacterData["alliance_id"])) {
            $allianceIDToAdd = $CharacterData["alliance_id"];
            $allianceToAdd = checkCache("Alliance", $allianceIDToAdd);
        }
        else {
            $allianceIDToAdd = 0;
            $allianceToAdd = "[No Alliance]";
        }
        
        if ((in_array("Super Admin", $_SESSION["AccessRoles"])) or (in_array("Configure Alliance", $_SESSION["AccessRoles"]) and $allianceIDToAdd == $_SESSION["AllianceID"] and $_SESSION["AllianceID"] !== 0) or (in_array("Configure Corp", $_SESSION["AccessRoles"]) and $corpIDToAdd == $_SESSION["CorporationID"])) {

            $toPull = $GLOBALS['MainDatabase']->prepare("SELECT * FROM configurations WHERE id = :id");
            $toPull->bindParam(':id', $toAddTo);
            $toPull->execute();
            $configurationData = $toPull->fetchAll();

            if (!empty($configurationData)) {

                if (((in_array("Super Admin", $_SESSION["AccessRoles"])) or (in_array("Configure Alliance", $_SESSION["AccessRoles"]) and $configurationData[0]["allianceid"] == $_SESSION["AllianceID"] and $_SESSION["AllianceID"] !== 0) or (in_array("Configure Corp", $_SESSION["AccessRoles"]) and $configurationData[0]["corporationid"] == $_SESSION["CorporationID"])) and $corpIDToAdd == $configurationData[0]["corporationid"]) {
                    
                    $IDList = json_decode($configurationData[0]["targetid"], true);
                    $nameList = json_decode($configurationData[0]["targetname"], true);
                    
                    if (!in_array($toAdd, $IDList)) {
                    
                        $IDList[] = $toAdd;
                        $nameList[] = $nameToAdd;
                    
                    }
                    else {
                        
                        header("Location: /manage/?error=character_already_a_target");
                        ob_end_flush();
                        die();
                        
                    }
                    $IDListToAdd = json_encode(array_values($IDList));
                    $nameListToAdd = json_encode(array_values($nameList));
                
                    $toUpdate = $GLOBALS['MainDatabase']->prepare("UPDATE configurations SET targetname=:targetname, targetid=:targetid WHERE id=:id");
                    $toUpdate->bindParam(':targetname', $nameListToAdd);
                    $toUpdate->bindParam(':targetid', $IDListToAdd);
                    $toUpdate->bindParam(':id', $toAddTo);
                    $toUpdate->execute();
                    
                    makeLogEntry("User Database Edit", $_SESSION["CurrentPage"], $_SESSION["Character Name"], "The character " . $toAdd . " has been added to " . $toAddTo . ".");
                    
                }
                
                else {
                    
                    header("Location: /manage/?error=please_do_not_mess_with_the_form_html");
                    ob_end_flush();
                    die();
                    
                }
                
            }
            else {

                header("Location: /manage/?error=please_do_not_mess_with_the_form_html");
                ob_end_flush();
                die();		
                
            }
            
        }

        else {

            header("Location: /manage/?error=please_do_not_mess_with_the_form_html");
            ob_end_flush();
            die();
            
        }
        
    }
	else {
		
        if (isset($_GET["todo"]) and htmlspecialchars($_GET["todo"]) == "remove_character") {

			$toRemoveFrom = htmlspecialchars($_GET["configid"]);
            $toRemove = htmlspecialchars($_GET["id"]);
			
			$toPull = $GLOBALS['MainDatabase']->prepare("SELECT * FROM configurations WHERE id = :id");
			$toPull->bindParam(':id', $toRemoveFrom);
			$toPull->execute();
			$configurationData = $toPull->fetchAll();
			
			if (!empty($configurationData)) {
			
				if ((in_array("Super Admin", $_SESSION["AccessRoles"])) or (in_array("Configure Alliance", $_SESSION["AccessRoles"]) and $configurationData[0]["allianceid"] == $_SESSION["AllianceID"] and $_SESSION["AllianceID"] !== 0) or (in_array("Configure Corp", $_SESSION["AccessRoles"]) and $configurationData[0]["corporationid"] == $_SESSION["CorporationID"])) {
					                    
                    $IDList = json_decode($configurationData[0]["targetid"], true);
                    $nameList = json_decode($configurationData[0]["targetname"], true);
                    
                    if (in_array($toRemove, $IDList)) {
                        
                        $nameToRemove = checkCache("Character", $toRemove);
                        if (($keyToRemove = array_search($toRemove, $IDList)) !== false) {
                            unset($IDList[$keyToRemove]);
                        }
                        if (($keyNameToRemove = array_search($nameToRemove, $nameList)) !== false) {
                            unset($nameList[$keyNameToRemove]);
                        }
                        
                        $IDListToAdd = json_encode(array_values($IDList));
                        $nameListToAdd = json_encode(array_values($nameList));
                                            
                        $toUpdate = $GLOBALS['MainDatabase']->prepare("UPDATE configurations SET targetname=:targetname, targetid=:targetid WHERE id=:id");
                        $toUpdate->bindParam(':targetname', $nameListToAdd);
                        $toUpdate->bindParam(':targetid', $IDListToAdd);
                        $toUpdate->bindParam(':id', $toRemoveFrom);
                        $toUpdate->execute();
                        
                        makeLogEntry("User Database Edit", $_SESSION["CurrentPage"], $_SESSION["Character Name"], "The character " . $toRemove . " has been removed from " . $toRemoveFrom . ".");
                    }
					else {
                        
					header("Location: /manage/?error=please_do_not_mess_with_the_form_html");
					ob_end_flush();
					die();	
                        
                    }
				}
				
				else {
					
					header("Location: /manage/?error=please_do_not_mess_with_the_form_html");
					ob_end_flush();
					die();					
					
				}
				
			}
			
			else {

				header("Location: /manage/?error=please_do_not_mess_with_the_form_html");
				ob_end_flush();
				die();
				
			}
            
        }
        
		elseif (isset($_GET["todo"]) and htmlspecialchars($_GET["todo"]) == "remove") {
			
			$toRemove = htmlspecialchars($_GET["id"]);
			
			$toPull = $GLOBALS['MainDatabase']->prepare("SELECT * FROM configurations WHERE id = :id");
			$toPull->bindParam(':id', $toRemove);
			$toPull->execute();
			$configurationData = $toPull->fetchAll();
			
			if (!empty($configurationData)) {
			
				if ((in_array("Super Admin", $_SESSION["AccessRoles"])) or (in_array("Configure Alliance", $_SESSION["AccessRoles"]) and $configurationData[0]["allianceid"] == $_SESSION["AllianceID"] and $_SESSION["AllianceID"] !== 0) or (in_array("Configure Corp", $_SESSION["AccessRoles"]) and $configurationData[0]["corporationid"] == $_SESSION["CorporationID"])) {
					
					$toDelete = $GLOBALS['MainDatabase']->prepare("DELETE FROM configurations WHERE id=:id");
					$toDelete->bindParam(':id', $toRemove);
					$toDelete->execute();
					
					makeLogEntry("User Database Edit", $_SESSION["CurrentPage"], $_SESSION["Character Name"], "The configuration " . $toRemove . " has been deleted.");
					
				}
				
				else {
					
					header("Location: /manage/?error=please_do_not_mess_with_the_form_html");
					ob_end_flush();
					die();					
					
				}
				
			}
			
			else {

				header("Location: /manage/?error=please_do_not_mess_with_the_form_html");
				ob_end_flush();
				die();
				
			}
			
		}
		else {

			header("Location: /manage/?error=todo_not_specified");
			ob_end_flush();
			die();	
			
		}
		
	}

	header("Location: /manage/");
	ob_end_flush();
	die();

?>