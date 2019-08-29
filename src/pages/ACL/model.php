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
			<td align='center'><img class='AdminImage' src='https://imageserver.eveonline.com/character/" . $entity["entityid"] . "_64.jpg'></td>
			<td align='center'><strong>" . $entity["entityname"] . "</strong></td>
			<td align='center'><strong>" . $accessString . "</strong></td>
			<td align='center'><a href='dataController.php?todo=remove&id=" . urlencode($entity["entityid"]) . "'><button class='btn btn-dark btn-sm'><strong>X</strong></button></a></td>
		</tr>
		";
		
	}
	
}

function showCharacterCard($ParsedName, $SearchID) {

	echo
	"<div class='AdminCells'>
		<div>
		<img class='AdminImage' style='float: left;' src='https://imageserver.eveonline.com/character/" . $SearchID . "_64.jpg'>
		</div>
		<strong>" . $ParsedName . "</strong>
		<br>
		<form method='post' action='/ACL/dataController.php'>
		
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
			</div>
			
			<input type='hidden' id='id' name='id' value='" . $SearchID . "'>

			<div class='form-group'>
				<input class='btn btn-dark btn-sm' type='submit' value='Add'>
			</div>
		</form>
	</div>";
	
}

?>