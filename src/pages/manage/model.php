<?php

	function generateOptions($specifiedCorp = "All") {
		
		$toPull = $GLOBALS['MainDatabase']->prepare("SELECT * FROM relays");
		$toPull->execute();
		$relayData = $toPull->fetchAll();
				
		foreach ($relayData as $throwaway => $relayData) {
			
			if ((in_array("Super Admin", $_SESSION["AccessRoles"])) or (in_array("Configure Alliance", $_SESSION["AccessRoles"]) and $relayData["allianceid"] == $_SESSION["AllianceID"] and $relayData["alliance"] !== "[No Alliance]") or (in_array("Configure Corp", $_SESSION["AccessRoles"]) and $relayData["corpid"] == $_SESSION["CorporationID"])) {
			
                if ($specifiedCorp == "All"){
            
                    echo "<option value='" . $relayData["id"] . "'>" . $relayData["name"] . " [" . $relayData["corp"] . "]</option>";
                }
                elseif ($relayData["corpid"] == $specifiedCorp){
            
                    echo "<option value='" . $relayData["id"] . "'>" . $relayData["name"] . "</option>";
                }
			}
		}	
	}
	
	function generateCharacterArray() {
		
		$toPull = $GLOBALS['MainDatabase']->prepare("SELECT * FROM relays");
		$toPull->execute();
		$relayData = $toPull->fetchAll();
		
		$arrayObject = [];
		
		foreach ($relayData as $throwaway => $relayData) {
			
			if ((in_array("Super Admin", $_SESSION["AccessRoles"])) or (in_array("Configure Alliance", $_SESSION["AccessRoles"]) and $relayData["allianceid"] == $_SESSION["AllianceID"] and $relayData["alliance"] !== "[No Alliance]") or (in_array("Configure Corp", $_SESSION["AccessRoles"]) and $relayData["corpid"] == $_SESSION["CorporationID"])) {
			
				$rolesString = "";
				
				foreach (json_decode($relayData["roles"], true) as $throwaway => $eachRole) {
					
					$rolesString .= (str_replace("_", " ", $eachRole) . "\n");
					
				}
				
				$arrayObject[] = ["name" => $relayData["name"], "id" => $relayData["id"], "corp" => $relayData["corp"], "corpid" => $relayData["corpid"], "alliance" => $relayData["alliance"], "allianceid" => $relayData["allianceid"], "roles" => $rolesString];
			
			}
			
		}
				
		echo json_encode($arrayObject);
		
	}
	
	function generateConfigurationArray() {
		
		$toPull = $GLOBALS['MainDatabase']->prepare("SELECT * FROM configurations");
		$toPull->execute();
		$configurationData = $toPull->fetchAll();
		
		foreach ($configurationData as $throwaway => $configurations) {
		
			if ((in_array("Super Admin", $_SESSION["AccessRoles"])) or (in_array("Configure Alliance", $_SESSION["AccessRoles"]) and $configurations["allianceid"] == $_SESSION["AllianceID"] and $configurations["alliance"] !== "[No Alliance]") or (in_array("Configure Corp", $_SESSION["AccessRoles"]) and $configurations["corporationid"] == $_SESSION["CorporationID"])) {
				
				$relayTypes = "";
				
				foreach (json_decode($configurations["whitelist"], true) as $throwaway => $types) {
					$relayTypes .= ($types . "<br>");
				}
				
				echo "
				<tr>
				
					<td>" . $configurations["type"] . "</td>
					<td>" . $configurations["channel"] . "</td>
					<td>" . $configurations["pingtype"] . "</td>
					<td>
                    <br>
                    <form method='post' action='/manage/dataController'>
                        <select name='target_add_character' class='custom-select' id='target_add_character' style='width: 200px;'>
                        
                ";
                
                generateOptions($configurations["corporationid"]);
                
                echo "
                        
                        </select>
                        <input type='hidden' name='add_character' id='add_character' value=" . $configurations["id"] . ">
                        <br>
                        <input type='submit' value='Add' class='btn btn-dark btn-small'>
                    </form>
                    </td>
                    <td style='text-align: left;'>
                ";
                
				foreach (json_decode($configurations["targetid"], true) as $throwaway => $targetids) {
                    $targetName = checkCache("Character", $targetids);
                    
					echo ("<a href='dataController?todo=remove_character&id=" . urlencode($targetids) . "&configid=" . urlencode($configurations["id"]) . "'><button class='btn btn-dark btn-sm'><strong>X</strong></button></a> " . $targetName . "<br>");
				}                
                    
                echo "
                    </td>
					<td>" . $configurations["alliance"] . "</td>
					<td>" . $configurations["corporation"] . "</td>
					<td class='small'>
					<button class='btn btn-dark btn-md' type='button' data-toggle='collapse' data-target='#" . $configurations["id"] . "' aria-expanded='false' aria-controls='collapseExample'>Show Notifications</button>
					<br>
					<div class='collapse' id='" . $configurations["id"] . "'><br>" . $relayTypes . "</div>
					</td>
					<td><a href='/manage/dataController?todo=remove&id=" . urlencode($configurations["id"]) . "'><button class='btn btn-dark btn-md'><strong>X</strong></button></a></td>
				
				</tr>
				";
				
			}
			
		}
		
	}

?>