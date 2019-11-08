<?php

function displayACL() {
	
	$pulledData = $GLOBALS['MainDatabase']->query("SELECT * FROM entityaccess");
	$entityData = $pulledData->fetchAll();
							
	foreach ($entityData as $throwaway => $entity) {
		
		$accessString = "";
		
		foreach (json_decode($entity["accessroles"], true) as $throwaway => $roles) {
			$accessString .= ($roles . "<br>");
		}
	
		echo "
		
		<tr>	
			<td align='center'><img class='AdminImage' src='https://images.evetech.net/characters/" . $entity["entityid"] . "/portrait'></td>
			<td align='center'><strong>" . $entity["entityname"] . "</strong></td>
			<td align='center'><strong>" . $accessString . "</strong></td>
			<td align='center'><a href='dataController?todo=remove&id=" . urlencode($entity["entityid"]) . "'><button class='btn btn-dark btn-sm'><img src='/resources/images/octicons/trashcan.svg' class='alertSVG'></button></a></td>
		</tr>
		";
		
	}
	
}

function showCharacterCard($ParsedName, $SearchID) {

	echo
	"<div class='AdminCells'>
		<div>
		<img class='AdminImage' style='float: left;' src='https://images.evetech.net/characters/" . $SearchID . "/portrait'>
		</div>
		<strong>" . $ParsedName . "</strong>
		<br>
		<form method='post' action='/ACL/dataController'>
		
			<div class='form-group'>
				<div class='custom-control custom-checkbox custom-control-inline'>
					<input type='checkbox' class='custom-control-input' name='acl_admin' value='true' id='acl_admin_" . $SearchID . "'> 
					<label class='custom-control-label' for='acl_admin_" . $SearchID . "'>ACL Admin</label>
				</div>

				<div class='custom-control custom-checkbox custom-control-inline'>
					<input type='checkbox' class='custom-control-input' name='configure_corp' value='true' id='configure_corp_" . $SearchID . "'> 
					<label class='custom-control-label' for='configure_corp_" . $SearchID . "'>Configure Corporation</label>
				</div>
			
				<div class='custom-control custom-checkbox custom-control-inline'>
					<input type='checkbox' class='custom-control-input' name='configure_alliance' value='true' id='configure_alliance_" . $SearchID . "'> 
					<label class='custom-control-label' for='configure_alliance_" . $SearchID . "'>Configure Alliance</label>
				</div>
                
				<div class='custom-control custom-checkbox custom-control-inline'>
					<input type='checkbox' class='custom-control-input' name='character_controller' value='true' id='character_controller_" . $SearchID . "'> 
					<label class='custom-control-label' for='character_controller_" . $SearchID . "'>Character Controller</label>
				</div>                
                
			</div>
			
			<input type='hidden' id='id' name='id' value='" . $SearchID . "'>

			<div class='form-group'>
				<input class='btn btn-dark btn-sm' type='submit' value='Add'>
			</div>
		</form>
	</div>";
	
}

?>