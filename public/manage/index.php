<?php
	session_start();

	require $_SERVER['DOCUMENT_ROOT'] . "/../src/auth/accessControl.php";
	require $_SERVER['DOCUMENT_ROOT'] . "/../src/pages/manage/model.php";
	
	configureErrorChecking();

	require $_SERVER['DOCUMENT_ROOT'] . "/../config/config.php";
	
	checkForErrors();
	
	$PageMinimumAccessLevel = ["Super Admin", "Configure Alliance", "Configure Corp"];
	checkLastPage();
	$_SESSION["CurrentPage"] = "Configuration Manager";

	checkCookies();
	
	determineAccess($_SESSION["AccessRoles"], $PageMinimumAccessLevel);	
	
	$characterImageLink = "https://images.evetech.net/characters/" . $_SESSION["CharacterID"] . "/portrait";
	
?>

<!DOCTYPE html>
<html>
<head>
	<title>Notification Manager</title>
	<link rel="stylesheet" href="../resources/stylesheets/styleMasterSheet.css">
	<link rel="icon" href="../resources/images/favicon.ico">
	
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="../resources/bootstrap/css/bootstrap.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
	<script src="../resources/bootstrap/js/bootstrap.bundle.js"></script>

</head>

<?php require $_SERVER['DOCUMENT_ROOT'] . "/../src/site/siteCore.php"; ?>

<body class="background">
	<div class="container-fluid">
		<br>
		<div class="row">
			<div class="col-xl-3">
				<h2 class="ColumnHeader">Add a Configuration</h2>
				<br>
				<form method="post" action="/manage/dataController">
					<div class="form-group">
						<label for="platform">Platform</label>
						<select name="platform" class="custom-select" id="platform">
							<option value="slack_webhook">Slack Webhook</option>
							<option value="discord_webhook">Discord Webhook</option>
						</select>
					</div>
					<div class="form-group">
						<label for="ping_type">Ping Type</label>
						<select name="ping_type" class="custom-select" id="ping_type">
							<option value="everyone">@everyone / @channel</option>
							<option value="here">@here</option>
							<option value="none">No Ping</option>
						</select>
					</div>
					<div class="form-group">
						<label for="target_channel">Channel Name (For user reference only)</label>
						<input type="text" name="target_channel" class="form-control" id="target_channel" autocomplete="off" required>
					</div>
					<div class="form-group">
						<label for="hook_url">Webhook URL</label>
						<input type="password" name="hook_url" class="form-control" id="hook_url" autocomplete="off" required>					
					</div>
					<div class="form-group">
					
						<label for="target_corporation">Target Corporation</label>
						<select name="target_corporation" class="custom-select" id="target_corporation">
						
							<?php generateOptions(); ?>
							
						</select>
						<div class="selection_info">
							<br>
							<strong>Selection Information</strong>
							<br>
							Alliance: <span id="allianceName"></span>
							<br>
							Corporation: <span id="corporationName"></span>
							<br>
							<br>
							<button class="btn btn-dark btn-md" type="button" data-toggle="collapse" data-target="#aggregateRoles" aria-expanded="false" aria-controls="collapseExample">Show Aggregate Roles</button>
							<br>
							<div class="collapse" id="aggregateRoles" style="white-space: pre-line;"></div>
							<script>
								
								$(document).ready(function(){
									
									var relays = <?php generateCorporationArray(); ?>;
									
									for (theData in relays) {
										if ($('#target_corporation').val() == relays[theData]["corpid"]) {
                                            
                                            var roleString = "";
                                            
                                            for (eachRole in relays[theData]["roles"]) {
                                                
                                                roleString += (eachRole + ": " + relays[theData]["roles"][eachRole] + "\n");
                                                
                                            }
                                            
											$('#allianceName').text(relays[theData]["alliance"]);
											$('#corporationName').text(relays[theData]["corp"]);
											$('#aggregateRoles').text(roleString);
											
										}
									}
                                    
                                    $("#selection").change(function(){
                                        
                                        if ($('#selection').val() == "groups") {
                                            
                                            $('#select_individual').attr("hidden", true);
                                            $('#select_groups').removeAttr("hidden");
                                            
                                        }
                                        if ($('#selection').val() == "individual") {
                                            
                                            $('#select_groups').attr("hidden", true);
                                            $('#select_individual').removeAttr("hidden");
                                            
                                        }
                                        
                                    });
									
									$("#target_corporation").change(function(){
                                        
										for (theData in relays) {
											if ($('#target_corporation').val() == relays[theData]["corpid"]) {
                                                
                                                var roleString = "";
                                                
                                                for (eachRole in relays[theData]["roles"]) {
                                                    
                                                    roleString += (eachRole + ": " + relays[theData]["roles"][eachRole] + "\n");
                                                    
                                                }
                                                
												$('#allianceName').text(relays[theData]["alliance"]);
												$('#corporationName').text(relays[theData]["corp"]);
												$('#aggregateRoles').text(roleString);
												
											}
										}
									});
								});
							
							</script>
						</div>
					
					</div>
					<div class="form-group">
						<label for="selection">Selection Type</label>
						<select name="selection" class="custom-select" id="selection">
							<option value="groups">Groups</option>
							<option value="individual">Individual</option>
						</select>
					</div>                    
					<div class="form-group" id="select_groups">
						<br>
						<strong>Events to Relay</strong>
						<br>
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="upwell_attack" value="true" id="upwell_attack">
							<label class="custom-control-label" for="upwell_attack">Upwell Attack/Reinforcement Events</label>
						</div>
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="moon_detonation" value="true" id="moon_detonation">
							<label class="custom-control-label" for="moon_detonation">Moon Detonations</label>
						</div>
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="moon_management" value="true" id="moon_management">
							<label class="custom-control-label" for="moon_management">Moon Management</label>
						</div>
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="upwell_management" value="true" id="upwell_management">
							<label class="custom-control-label" for="upwell_management">Upwell Management Events</label>
						</div>
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="sov_attacks" value="true" id="sov_attacks">
							<label class="custom-control-label" for="sov_attacks">Sovereignty Attacks/Reinforcement</label>
						</div>
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="sov_management" value="true" id="sov_management">
							<label class="custom-control-label" for="sov_management">Sovereignty Management</label>
						</div>
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="custom_office" value="true" id="custom_office">
							<label class="custom-control-label" for="custom_office">Customs Office Events</label>
						</div>
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="pos_attack" value="true" id="pos_attack">
							<label class="custom-control-label" for="pos_attack">POS Attack Events</label>
						</div>
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="pos_management" value="true" id="pos_management">
							<label class="custom-control-label" for="pos_management">POS Management Events</label>
						</div>
					</div>
                    <div class="form-group" id="select_individual" hidden>
						<br>
						<strong>Notifications to Relay</strong>
						<br>
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="StructureDestroyed" value="true" id="StructureDestroyed">
							<label class="custom-control-label" for="StructureDestroyed">Structure Destroyed</label>
						</div>
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="StructureLostArmor" value="true" id="StructureLostArmor">
							<label class="custom-control-label" for="StructureLostArmor">Structure Lost Armor</label>
						</div>
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="StructureLostShields" value="true" id="StructureLostShields">
							<label class="custom-control-label" for="StructureLostShields">Structure Lost Shields</label>
						</div>
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="StructureUnderAttack" value="true" id="StructureUnderAttack">
							<label class="custom-control-label" for="StructureUnderAttack">Structure Under Attack</label>
						</div>
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="MoonminingAutomaticFracture" value="true" id="MoonminingAutomaticFracture">
							<label class="custom-control-label" for="MoonminingAutomaticFracture">Moonmining Automatic Fracture</label>
						</div>
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="MoonminingLaserFired" value="true" id="MoonminingLaserFired">
							<label class="custom-control-label" for="MoonminingLaserFired">Moonmining Laser Fired</label>
						</div>
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="MoonminingExtractionCancelled" value="true" id="MoonminingExtractionCancelled">
							<label class="custom-control-label" for="MoonminingExtractionCancelled">Moonmining Extraction Cancelled</label>
						</div>
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="MoonminingExtractionFinished" value="true" id="MoonminingExtractionFinished">
							<label class="custom-control-label" for="MoonminingExtractionFinished">Moonmining Extraction Finished</label>
						</div>
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="MoonminingExtractionStarted" value="true" id="MoonminingExtractionStarted">
							<label class="custom-control-label" for="MoonminingExtractionStarted">Moonmining Extraction Started</label>
						</div>
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="StructureAnchoring" value="true" id="StructureAnchoring">
							<label class="custom-control-label" for="StructureAnchoring">Structure Anchoring</label>
						</div>
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="StructureFuelAlert" value="true" id="StructureFuelAlert">
							<label class="custom-control-label" for="StructureFuelAlert">Structure Fuel Alert</label>
						</div>
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="StructureOnline" value="true" id="StructureOnline">
							<label class="custom-control-label" for="StructureOnline">Structure Online</label>
						</div>
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="StructureUnanchoring" value="true" id="StructureUnanchoring">
							<label class="custom-control-label" for="StructureUnanchoring">Structure Unanchoring</label>
						</div>
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="StructureServicesOffline" value="true" id="StructureServicesOffline">
							<label class="custom-control-label" for="StructureServicesOffline">Structure Services Offline</label>
						</div>
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="StructureWentHighPower" value="true" id="StructureWentHighPower">
							<label class="custom-control-label" for="StructureWentHighPower">Structure Went High Power</label>
						</div>
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="StructureWentLowPower" value="true" id="StructureWentLowPower">
							<label class="custom-control-label" for="StructureWentLowPower">Structure Went Low Power</label>
						</div>
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="StructuresReinforcementChanged" value="true" id="StructuresReinforcementChanged">
							<label class="custom-control-label" for="StructuresReinforcementChanged">Structures Reinforcement Changed</label>
						</div>
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="OwnershipTransferred" value="true" id="OwnershipTransferred">
							<label class="custom-control-label" for="OwnershipTransferred">Ownership Transferred</label>
						</div>
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="EntosisCaptureStarted" value="true" id="EntosisCaptureStarted">
							<label class="custom-control-label" for="EntosisCaptureStarted">Entosis Capture Started</label>
						</div>
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="SovCommandNodeEventStarted" value="true" id="SovCommandNodeEventStarted">
							<label class="custom-control-label" for="SovCommandNodeEventStarted">Sov Command Node Event Started</label>
						</div>
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="SovStructureReinforced" value="true" id="SovStructureReinforced">
							<label class="custom-control-label" for="SovStructureReinforced">Sov Structure Reinforced</label>
						</div>
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="SovStructureDestroyed" value="true" id="SovStructureDestroyed">
							<label class="custom-control-label" for="SovStructureDestroyed">Sov Structure Destroyed</label>
						</div>
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="SovAllClaimAquiredMsg" value="true" id="SovAllClaimAquiredMsg">
							<label class="custom-control-label" for="SovAllClaimAquiredMsg">Sov Claim Acquired</label>
						</div>
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="SovAllClaimLostMsg" value="true" id="SovAllClaimLostMsg">
							<label class="custom-control-label" for="SovAllClaimLostMsg">Sov Claim Lost</label>
						</div>
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="SovStructureSelfDestructRequested" value="true" id="SovStructureSelfDestructRequested">
							<label class="custom-control-label" for="SovStructureSelfDestructRequested">Sov Structure Self Destruct Requested</label>
						</div>
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="SovStructureSelfDestructFinished" value="true" id="SovStructureSelfDestructFinished">
							<label class="custom-control-label" for="SovStructureSelfDestructFinished">Sov Structure Self Destruct Finished</label>
						</div>
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="SovStructureSelfDestructCancel" value="true" id="SovStructureSelfDestructCancel">
							<label class="custom-control-label" for="SovStructureSelfDestructCancel">Sov Structure Self Destruct Cancel</label>
						</div>
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="OrbitalAttacked" value="true" id="OrbitalAttacked">
							<label class="custom-control-label" for="OrbitalAttacked">Orbital Attacked</label>
						</div>
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="OrbitalReinforced" value="true" id="OrbitalReinforced">
							<label class="custom-control-label" for="OrbitalReinforced">Orbital Reinforced</label>
						</div>
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="TowerAlertMsg" value="true" id="TowerAlertMsg">
							<label class="custom-control-label" for="TowerAlertMsg">Tower Under Attack</label>
						</div>
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="TowerResourceAlertMsg" value="true" id="TowerResourceAlertMsg">
							<label class="custom-control-label" for="TowerResourceAlertMsg">Tower Resource Alert</label>
						</div>
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="AllAnchoringMsg" value="true" id="AllAnchoringMsg">
							<label class="custom-control-label" for="AllAnchoringMsg">Tower Anchoring</label>
						</div>
                    </div>
					<div class="form-group">
                    
                        <input type="hidden" name="create_relay" id="create_relay" value="true">
						<input type="submit" value="Create Configuration" class="btn btn-dark btn-large">
					
					</div>
					<p>On creation your chosen channel should receive a message detailing this configuration. If it does not please remove it and try again.</p>
				</form>
			
			</div>
			<div class="col-xl-9">
				<h2 class="ColumnHeader">Configurations</h2>
				<br>
				<table style="width: 100%;">
				<tr>
					<th>Platform</th>
					<th>Channel</th>
					<th>Ping Type</th>
					<th>Total Targets</th>
					<th>Alliance</th>
					<th>Corporation</th>
					<th>Whitelist</th>
					<th>Remove</th>
				</tr>
				
					<?php generateConfigurationArray(); ?>
				
				</table>
			</div>
		</div>
	</div>
</body>

<?php require $_SERVER['DOCUMENT_ROOT'] . "/../src/site/footer.php"; ?>

</html>