<?php

	function generateOptions() {
		
		$toPull = $GLOBALS['MainDatabase']->prepare("SELECT * FROM relays");
		$toPull->execute();
		$relayData = $toPull->fetchAll();
				
		foreach ($relayData as $throwaway => $relayData) {
			
			if ((in_array("Super Admin", $_SESSION["AccessRoles"])) or (in_array("Configure Alliance", $_SESSION["AccessRoles"]) and $relayData["allianceid"] == $_SESSION["AllianceID"] and $relayData["alliance"] !== "[No Alliance]") or (in_array("Configure Corp", $_SESSION["AccessRoles"]) and $relayData["corpid"] == $_SESSION["CorporationID"])) {
			
				echo "<option value='" . $relayData["id"] . "'>" . $relayData["name"] . " [" . $relayData["corp"] . "]</option>";
			
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
					<td>" . $configurations["targetname"] . "</td>
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