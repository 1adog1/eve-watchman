<?php

	function generateOptions() {
		
		$toPull = $GLOBALS['MainDatabase']->prepare("SELECT * FROM relays");
		$toPull->execute();
		$relayData = $toPull->fetchAll();
        
        $knownCorps = [];
				
		foreach ($relayData as $throwaway => $relayData) {
			
			if ((in_array("Super Admin", $_SESSION["AccessRoles"])) or (in_array("Configure Alliance", $_SESSION["AccessRoles"]) and $relayData["allianceid"] == $_SESSION["AllianceID"] and $relayData["alliance"] !== "[No Alliance]" and $_SESSION["AllianceID"] !== 0) or (in_array("Configure Corp", $_SESSION["AccessRoles"]) and $relayData["corpid"] == $_SESSION["CorporationID"])) {
			
                if (!in_array($relayData["corpid"], $knownCorps)){
            
                    echo "<option value='" . $relayData["corpid"] . "'>" . $relayData["corp"] . " [" . $relayData["alliance"] . "]</option>";
                    
                    $knownCorps[] = $relayData["corpid"];
                    
                }
			}
		}
	}
	
	function generateCorporationArray() {
		
		$toPull = $GLOBALS['MainDatabase']->prepare("SELECT * FROM relays");
		$toPull->execute();
		$relayData = $toPull->fetchAll();
		
		$arrayObject = [];
		
		foreach ($relayData as $throwaway => $relayData) {
			
			if ((in_array("Super Admin", $_SESSION["AccessRoles"])) or (in_array("Configure Alliance", $_SESSION["AccessRoles"]) and $relayData["allianceid"] == $_SESSION["AllianceID"] and $relayData["alliance"] !== "[No Alliance]" and $_SESSION["AllianceID"] !== 0) or (in_array("Configure Corp", $_SESSION["AccessRoles"]) and $relayData["corpid"] == $_SESSION["CorporationID"])) {
                
                if (!isset($arrayObject[$relayData["corpid"]])) {
                    $arrayObject[$relayData["corpid"]] = ["corp" => $relayData["corp"], "corpid" => $relayData["corpid"], "alliance" => $relayData["alliance"], "allianceid" => $relayData["allianceid"], "roles" => []];;
                }
                
				foreach (json_decode($relayData["roles"], true) as $throwaway => $eachRole) {
					
					$formattedRole = (str_replace("_", " ", $eachRole));
                    
                    if (!isset($arrayObject[$relayData["corpid"]]["roles"][$formattedRole])) {
                        
                        $arrayObject[$relayData["corpid"]]["roles"][$formattedRole] = 0;
                        
                    }
                    
                    $arrayObject[$relayData["corpid"]]["roles"][$formattedRole] += 1;
					
				}
			}
		}
				
		echo json_encode($arrayObject);
		
	}
	
	function generateConfigurationArray() {
        
        $targetCounts = [];

		$toQuery = $GLOBALS['MainDatabase']->prepare("SELECT * FROM relays");
		$toQuery->execute();
		$relayData = $toQuery->fetchAll();
        
        foreach ($relayData as $throwaway => $relays) {
            
            if (!isset($targetCounts[$relays["corpid"]])) {
                $targetCounts[$relays["corpid"]] = 0;
            }
            
            $targetCounts[$relays["corpid"]] += 1;
            
        }
		
		$toPull = $GLOBALS['MainDatabase']->prepare("SELECT * FROM configurations");
		$toPull->execute();
		$configurationData = $toPull->fetchAll();
		
		foreach ($configurationData as $throwaway => $configurations) {
		
			if ((in_array("Super Admin", $_SESSION["AccessRoles"])) or (in_array("Configure Alliance", $_SESSION["AccessRoles"]) and $configurations["allianceid"] == $_SESSION["AllianceID"] and $configurations["alliance"] !== "[No Alliance]" and $_SESSION["AllianceID"] !== 0) or (in_array("Configure Corp", $_SESSION["AccessRoles"]) and $configurations["corporationid"] == $_SESSION["CorporationID"])) {
				
				$relayTypes = "";
				
				foreach (json_decode($configurations["whitelist"], true) as $throwaway => $types) {
					$relayTypes .= ($types . "<br>");
				}
                
                $targets = isset($targetCounts[$configurations["corporationid"]]) ? $targetCounts[$configurations["corporationid"]] : 0;
				
				echo "
				<tr>
				
					<td>" . $configurations["type"] . "</td>
					<td>" . $configurations["channel"] . "</td>
					<td>" . $configurations["pingtype"] . "</td>
                    <td>" . $targets . "</td>
					<td>" . $configurations["alliance"] . "</td>
					<td>" . $configurations["corporation"] . "</td>
					<td class='small text-left'>
					<button class='btn btn-dark btn-sm' type='button' data-toggle='collapse' data-target='#" . $configurations["id"] . "' aria-expanded='false' aria-controls='collapseExample'>Show Notifications</button>
					<br>
					<div class='collapse' id='" . $configurations["id"] . "'><br>" . $relayTypes . "</div>
					</td>
					<td><a href='/manage/dataController?todo=remove&id=" . urlencode($configurations["id"]) . "'><button class='btn btn-dark btn-md'><img src='/resources/images/octicons/trashcan.svg' class='alertSVG'></button></a></td>
				
				</tr>
				";
				
			}
			
		}
		
	}

?>