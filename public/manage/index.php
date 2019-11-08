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
					
						<label for="target_character">Target Character</label>
						<select name="target_character" class="custom-select" id="target_character">
						
							<?php generateOptions(); ?>
							
						</select>
						<div class="selection_info">
							<br>
							<strong>Selection Information</strong>
							<br>
							Name: <span id="characterName"></span>
							<br>
							Alliance: <span id="characterAlliance"></span>
							<br>
							Corporation: <span id="characterCorp"></span>
							<br>
							<br>
							<button class="btn btn-dark btn-md" type="button" data-toggle="collapse" data-target="#characterRoles" aria-expanded="false" aria-controls="collapseExample">Show Player Roles</button>
							<br>
							<div class="collapse" id="characterRoles"></div>
							<script>
								
								$(document).ready(function(){
									
									var relayJSON = <?php generateCharacterArray(); ?>;
									var relayString = JSON.stringify(relayJSON);
									var relays = JSON.parse(relayString);

									var theData;
									
									for (theData of relays) {
										if ($('#target_character').val() == theData["id"]) {
											$('#characterName').text(theData["name"]);
											$('#characterAlliance').text(theData["alliance"]);
											$('#characterCorp').text(theData["corp"]);
											$('#characterRoles').text(theData["roles"]);
											
										}
									}
									
									$("#target_character").change(function(){
										var theData;
										
										for (theData of relays) {
											if ($('#target_character').val() == theData["id"]) {
												$('#characterName').text(theData["name"]);
												$('#characterAlliance').text(theData["alliance"]);
												$('#characterCorp').text(theData["corp"]);
												$('#characterRoles').text(theData["roles"]);
												
											}
										}
									});
								});
							
							</script>
						</div>
					
					</div>
					<div class="form-group">
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
                    <th>Add Target</th>
					<th>Targets</th>
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