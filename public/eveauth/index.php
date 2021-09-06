<?php
	session_start();
	
	require $_SERVER['DOCUMENT_ROOT'] . "/../src/auth/accessControl.php";

	purgeCookies();
	configureErrorChecking();
	
	checkLastPage();
	$_SESSION["CurrentPage"] = "Eve Authentication";
	
	require $_SERVER['DOCUMENT_ROOT'] . "/../config/config.php";

	$encodedAuthorization = "Basic " . base64_encode($clientid . ":" . $clientsecret);
	
	if (isset($_GET["code"]) and isset($_GET["state"]) and isset($_SESSION["UniqueState"])) {
		
		if (htmlspecialchars($_GET["state"]) == $_SESSION["UniqueState"]) {
	
			$authenticationCode = htmlspecialchars($_GET["code"]);

			$curlPost = curl_init();
			curl_setopt($curlPost, CURLOPT_URL, "https://login.eveonline.com/oauth/token/");
			curl_setopt($curlPost, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curlPost, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curlPost, CURLOPT_HTTPHEADER, ["Content-Type:application/x-www-form-urlencoded", "Authorization:" . $encodedAuthorization]);
			curl_setopt($curlPost, CURLOPT_POSTFIELDS, http_build_query(["grant_type" => "authorization_code", "code" => $authenticationCode]));

			$response = json_decode(curl_exec($curlPost), true);
			
			if (isset($response["access_token"])) {
			
				$authenticationToken = $response["access_token"];
				$refreshToken = $response["refresh_token"];
				
				curl_close($curlPost);

				$curlGet = curl_init();
				curl_setopt($curlGet, CURLOPT_URL, "https://login.eveonline.com/oauth/verify/");
				curl_setopt($curlGet, CURLOPT_HTTPGET, true);
				curl_setopt($curlGet, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($curlGet, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($curlGet, CURLOPT_HTTPHEADER, ["Authorization: Bearer " . $authenticationToken]);

				$response = json_decode(curl_exec($curlGet), true);
								
				if (isset($response["Scopes"]) and strpos($response["Scopes"], "esi-universe.read_structures.v1") !== false and strpos($response["Scopes"], "esi-characters.read_corporation_roles.v1") !== false and strpos($response["Scopes"], "esi-characters.read_notifications.v1") !== false) {
					
					$characterID = $response["CharacterID"];
					
					$CharacterJson = file_get_contents("http://esi.evetech.net/latest/characters/" . $characterID . "/?datasource=tranquility");
					$CharacterData = json_decode($CharacterJson, true);
					
                    if (isset($CharacterData["name"])) {

                        $characterName = htmlspecialchars($CharacterData["name"]);
                        
                        $corpID = $CharacterData["corporation_id"];
                        $corpName = checkCache("Corporation", $corpID);
                        
                        if (isset($CharacterData["alliance_id"])) {
                        
                            $allianceID = $CharacterData["alliance_id"];
                            $allianceName = checkCache("Alliance", $allianceID);
                        }
                        else {
                            
                            $allianceID = 0;
                            $allianceName = "[No Alliance]";
                            
                        }
                        
                        $rolesJson = file_get_contents("https://esi.evetech.net/latest/characters/" . $characterID . "/roles/?datasource=tranquility&token=" . $authenticationToken);
                        $rolesData = json_decode($rolesJson, true);
                        
                        if (isset($rolesData["roles"])) {
                            
                            $rolesToAdd = json_encode($rolesData["roles"]);

                            $toPull = $GLOBALS['MainDatabase']->prepare("SELECT * FROM relays WHERE id=:id");
                            $toPull->bindParam(':id', $characterID);
                            $toPull->execute();
                            $characterArrayData = $toPull->fetchAll();
                            
                            if (empty($characterArrayData)) {
                                
                                $toInsert = $GLOBALS['MainDatabase']->prepare("INSERT INTO relays (name, id, corpid, corp, refreshtoken, allianceid, alliance, roles) VALUES (:name, :id, :corpid, :corp, :refreshtoken, :allianceid, :alliance, :roles)");
                                $toInsert->bindParam(':name', $characterName);
                                $toInsert->bindParam(':id', $characterID);
                                $toInsert->bindParam(':corpid', $corpID);
                                $toInsert->bindParam(':corp', $corpName);
                                $toInsert->bindParam(':refreshtoken', $refreshToken);
                                $toInsert->bindParam(':allianceid', $allianceID);
                                $toInsert->bindParam(':alliance', $allianceName);
                                $toInsert->bindParam(':roles', $rolesToAdd);
                                
                                $toInsert->execute();
                                
                                $_SESSION["RelayStatus"] = "Added";
                                
                                makeLogEntry("User Database Edit", "Eve Authentication", "[Server Backend]", $characterID . " has been added as a relay character.");
                                
                            }
                            else {
                                
                                $toUpdate = $GLOBALS['MainDatabase']->prepare("UPDATE relays SET name = :name, corpid = :corpid, corp = :corp, refreshtoken = :refreshtoken, allianceid = :allianceid, alliance = :alliance, roles = :roles WHERE id = :id");
                                $toUpdate->bindParam(':name', $characterName);
                                $toUpdate->bindParam(':corpid', $corpID);
                                $toUpdate->bindParam(':corp', $corpName);
                                $toUpdate->bindParam(':refreshtoken', $refreshToken);
                                $toUpdate->bindParam(':allianceid', $allianceID);
                                $toUpdate->bindParam(':alliance', $allianceName);
                                $toUpdate->bindParam(':roles', $rolesToAdd);
                                $toUpdate->bindParam(':id', $characterID);
                                
                                $toUpdate->execute();
                                
                                $_SESSION["RelayStatus"] = "Updated";
                                
                                makeLogEntry("User Database Edit", "Eve Authentication", "[Server Backend]", $characterID . "'s relay information has been updated.");
                                
                            }

                        }
                        else {

                            checkForErrors();
                            makeLogEntry("User Login", $_SESSION["CurrentPage"], $_SESSION["Character Name"], "Login Failure");
                            
                        }

                    }
                    else {
                        
                        checkForErrors();
                        makeLogEntry("User Login", $_SESSION["CurrentPage"], $_SESSION["Character Name"], "Login Failure");
                        
                    }
					
				}
				else {
				
					$_SESSION["CharacterID"] = $response["CharacterID"];
					
					curl_close($curlGet);

					checkCookies();
				
					makeLogEntry("User Login", $_SESSION["CurrentPage"], $_SESSION["Character Name"], "Login Success");
				}
			
			}
			else {
				curl_close($curlPost);
				
				checkForErrors();
				makeLogEntry("User Login", $_SESSION["CurrentPage"], $_SESSION["Character Name"], "Login Failure");

			}
		}
		else {
			checkForErrors();
			makeLogEntry("User Login", $_SESSION["CurrentPage"], $_SESSION["Character Name"], "Login Failure - STATES DO NOT MATCH");			
		}
	}
	else {
		
		checkForErrors();
		makeLogEntry("User Login", $_SESSION["CurrentPage"], $_SESSION["Character Name"], "Login Failure");
	}

	ob_flush();
	header("Location: /");
	ob_end_flush();
	die();
?>