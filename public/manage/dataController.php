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
		
        if (isset($_POST["selection"]) and htmlspecialchars($_POST["selection"]) == "groups") {
            
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
        
        }
        elseif (isset($_POST["selection"]) and htmlspecialchars($_POST["selection"]) == "individual") {
            
            if (isset($_POST["StructureDestroyed"]) and htmlspecialchars($_POST["StructureDestroyed"]) == "true") {
                $whitelistArray[] = "StructureDestroyed";
            }
            if (isset($_POST["StructureLostArmor"]) and htmlspecialchars($_POST["StructureLostArmor"]) == "true") {
                $whitelistArray[] = "StructureLostArmor";
            }
            if (isset($_POST["StructureLostShields"]) and htmlspecialchars($_POST["StructureLostShields"]) == "true") {
                $whitelistArray[] = "StructureLostShields";
            }
            if (isset($_POST["StructureUnderAttack"]) and htmlspecialchars($_POST["StructureUnderAttack"]) == "true") {
                $whitelistArray[] = "StructureUnderAttack";
            }
            if (isset($_POST["MoonminingAutomaticFracture"]) and htmlspecialchars($_POST["MoonminingAutomaticFracture"]) == "true") {
                $whitelistArray[] = "MoonminingAutomaticFracture";
            }
            if (isset($_POST["MoonminingLaserFired"]) and htmlspecialchars($_POST["MoonminingLaserFired"]) == "true") {
                $whitelistArray[] = "MoonminingLaserFired";
            }
            if (isset($_POST["MoonminingExtractionCancelled"]) and htmlspecialchars($_POST["MoonminingExtractionCancelled"]) == "true") {
                $whitelistArray[] = "MoonminingExtractionCancelled";
            }
            if (isset($_POST["MoonminingExtractionFinished"]) and htmlspecialchars($_POST["MoonminingExtractionFinished"]) == "true") {
                $whitelistArray[] = "MoonminingExtractionFinished";
            }
            if (isset($_POST["MoonminingExtractionStarted"]) and htmlspecialchars($_POST["MoonminingExtractionStarted"]) == "true") {
                $whitelistArray[] = "MoonminingExtractionStarted";
            }
            if (isset($_POST["StructureAnchoring"]) and htmlspecialchars($_POST["StructureAnchoring"]) == "true") {
                $whitelistArray[] = "StructureAnchoring";
            }
            if (isset($_POST["StructureFuelAlert"]) and htmlspecialchars($_POST["StructureFuelAlert"]) == "true") {
                $whitelistArray[] = "StructureFuelAlert";
            }
            if (isset($_POST["StructureOnline"]) and htmlspecialchars($_POST["StructureOnline"]) == "true") {
                $whitelistArray[] = "StructureOnline";
            }
            if (isset($_POST["StructureUnanchoring"]) and htmlspecialchars($_POST["StructureUnanchoring"]) == "true") {
                $whitelistArray[] = "StructureUnanchoring";
            }
            if (isset($_POST["StructureServicesOffline"]) and htmlspecialchars($_POST["StructureServicesOffline"]) == "true") {
                $whitelistArray[] = "StructureServicesOffline";
            }
            if (isset($_POST["StructureWentHighPower"]) and htmlspecialchars($_POST["StructureWentHighPower"]) == "true") {
                $whitelistArray[] = "StructureWentHighPower";
            }
            if (isset($_POST["StructureWentLowPower"]) and htmlspecialchars($_POST["StructureWentLowPower"]) == "true") {
                $whitelistArray[] = "StructureWentLowPower";
            }
            if (isset($_POST["StructuresReinforcementChanged"]) and htmlspecialchars($_POST["StructuresReinforcementChanged"]) == "true") {
                $whitelistArray[] = "StructuresReinforcementChanged";
            }
            if (isset($_POST["OwnershipTransferred"]) and htmlspecialchars($_POST["OwnershipTransferred"]) == "true") {
                $whitelistArray[] = "OwnershipTransferred";
            }
            if (isset($_POST["EntosisCaptureStarted"]) and htmlspecialchars($_POST["EntosisCaptureStarted"]) == "true") {
                $whitelistArray[] = "EntosisCaptureStarted";
            }
            if (isset($_POST["SovCommandNodeEventStarted"]) and htmlspecialchars($_POST["SovCommandNodeEventStarted"]) == "true") {
                $whitelistArray[] = "SovCommandNodeEventStarted";
            }
            if (isset($_POST["SovStructureReinforced"]) and htmlspecialchars($_POST["SovStructureReinforced"]) == "true") {
                $whitelistArray[] = "SovStructureReinforced";
            }
            if (isset($_POST["SovStructureDestroyed"]) and htmlspecialchars($_POST["SovStructureDestroyed"]) == "true") {
                $whitelistArray[] = "SovStructureDestroyed";
            }
            if (isset($_POST["SovAllClaimAquiredMsg"]) and htmlspecialchars($_POST["SovAllClaimAquiredMsg"]) == "true") {
                $whitelistArray[] = "SovAllClaimAquiredMsg";
            }
            if (isset($_POST["SovAllClaimLostMsg"]) and htmlspecialchars($_POST["SovAllClaimLostMsg"]) == "true") {
                $whitelistArray[] = "SovAllClaimLostMsg";
            }
            if (isset($_POST["SovStructureSelfDestructRequested"]) and htmlspecialchars($_POST["SovStructureSelfDestructRequested"]) == "true") {
                $whitelistArray[] = "SovStructureSelfDestructRequested";
            }
            if (isset($_POST["SovStructureSelfDestructFinished"]) and htmlspecialchars($_POST["SovStructureSelfDestructFinished"]) == "true") {
                $whitelistArray[] = "SovStructureSelfDestructFinished";
            }
            if (isset($_POST["SovStructureSelfDestructCancel"]) and htmlspecialchars($_POST["SovStructureSelfDestructCancel"]) == "true") {
                $whitelistArray[] = "SovStructureSelfDestructCancel";
            }
            if (isset($_POST["OrbitalAttacked"]) and htmlspecialchars($_POST["OrbitalAttacked"]) == "true") {
                $whitelistArray[] = "OrbitalAttacked";
            }
            if (isset($_POST["OrbitalReinforced"]) and htmlspecialchars($_POST["OrbitalReinforced"]) == "true") {
                $whitelistArray[] = "OrbitalReinforced";
            }
            if (isset($_POST["TowerAlertMsg"]) and htmlspecialchars($_POST["TowerAlertMsg"]) == "true") {
                $whitelistArray[] = "TowerAlertMsg";
            }
            if (isset($_POST["TowerResourceAlertMsg"]) and htmlspecialchars($_POST["TowerResourceAlertMsg"]) == "true") {
                $whitelistArray[] = "TowerResourceAlertMsg";
            }
            if (isset($_POST["AllAnchoringMsg"]) and htmlspecialchars($_POST["AllAnchoringMsg"]) == "true") {
                $whitelistArray[] = "AllAnchoringMsg";
            }

        }
        else {
            
            header("Location: /manage/?error=please_do_not_mess_with_the_form_html");
            ob_end_flush();
            die();
            
        }
		
		if (!empty($whitelistArray)) {

			$bytes = random_bytes(16);
			$uniqueID = bin2hex($bytes);	
			$uniqueID = "x" . $uniqueID;
			
			$whitelistArray = json_encode($whitelistArray);
			$currentTime = time();
			$platform = htmlspecialchars($_POST["platform"]);
			$webhookToAdd = $_POST["hook_url"];
			$targetChannel = htmlspecialchars($_POST["target_channel"]);
			$pingType = htmlspecialchars($_POST["ping_type"]);
            
			$corpIDToAdd = htmlspecialchars($_POST["target_corporation"]);
			$corpToAdd = checkCache("Corporation", $corpIDToAdd);

			$corpoationJson = file_get_contents("http://esi.evetech.net/latest/corporations/" . $corpIDToAdd . "/?datasource=tranquility");
			$corporationData = json_decode($corpoationJson, true);
			
			if (isset($corporationData["alliance_id"])) {
				$allianceIDToAdd = $corporationData["alliance_id"];
				$allianceToAdd = checkCache("Alliance", $allianceIDToAdd);
			}
			else {
				$allianceIDToAdd = 0;
				$allianceToAdd = "[No Alliance]";
			}
			
			if ((in_array("Super Admin", $_SESSION["AccessRoles"])) or (in_array("Configure Alliance", $_SESSION["AccessRoles"]) and $allianceIDToAdd == $_SESSION["AllianceID"] and $_SESSION["AllianceID"] !== 0) or (in_array("Configure Corp", $_SESSION["AccessRoles"]) and $corpIDToAdd == $_SESSION["CorporationID"])) {
				
				if (($platform == "slack_webhook" and strpos($webhookToAdd, "https://hooks.slack.com/services/") === 0) or ($platform == "discord_webhook" and strpos($webhookToAdd, "https://discord.com/api/webhooks/") === 0)) {
                    			
					$toInsert = $GLOBALS['MainDatabase']->prepare("INSERT INTO configurations (id, type, channel, url, pingtype, whitelist, timestamp, alliance, allianceid, corporation, corporationid) VALUES (:id, :type, :channel, :url, :pingtype, :whitelist, :timestamp, :alliance, :allianceid, :corporation, :corporationid)");
					$toInsert->bindParam(':id', $uniqueID);
					$toInsert->bindParam(':type', $platform);
					$toInsert->bindParam(':channel', $targetChannel);
					$toInsert->bindParam(':url', $webhookToAdd);
					$toInsert->bindParam(':pingtype', $pingType);
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
	else {
        
		if (isset($_GET["todo"]) and htmlspecialchars($_GET["todo"]) == "remove") {
			
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
