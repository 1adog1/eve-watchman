<?php

	function generateCharacterArray() {
		
		$toPull = $GLOBALS['MainDatabase']->prepare("SELECT * FROM relays");
		$toPull->execute();
		$relayData = $toPull->fetchAll();
		
		foreach ($relayData as $throwaway => $relays) {
		
			if ((in_array("Super Admin", $_SESSION["AccessRoles"])) or (in_array("Configure Alliance", $_SESSION["AccessRoles"]) and $relays["allianceid"] == $_SESSION["AllianceID"] and $relays["alliance"] !== "[No Alliance]" and $_SESSION["AllianceID"] !== 0) or (in_array("Configure Corp", $_SESSION["AccessRoles"]) and $relays["corpid"] == $_SESSION["CorporationID"])) {
				
				$allRoles = "";
				
				foreach (json_decode($relays["roles"], true) as $throwaway => $roles) {
					$allRoles .= (str_replace("_", " ", $roles) . "<br>");
				}
				
				echo "
				<tr>
					<td>" . $relays["name"] . "</td>
                    <td>" . $relays["corp"] . "</td>
                    <td>" . $relays["alliance"] . "</td>
					<td class='small text-left'>
					<button class='btn btn-dark btn-md' type='button' data-toggle='collapse' data-target='#roles_" . $relays["id"] . "' aria-expanded='false' aria-controls='collapseExample'>Show Roles</button>
					<br>
					<div class='collapse' id='roles_" . $relays["id"] . "'><br>" . $allRoles . "</div>
					</td>
                    <td class='text-left'>
                ";
                
                runCharacterCheck($relays["id"]);
                
                echo "
                    </td>
					<td><a href='/characterManager/dataController?todo=remove&id=" . urlencode($relays["id"]) . "'><button class='btn btn-dark btn-md'><img src='/resources/images/octicons/trashcan.svg' class='alertSVG'></button></a></td>
				</tr>
				";
				
			}
			
		}
		
	}

?>