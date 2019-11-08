<?php
	session_start();

	require $_SERVER['DOCUMENT_ROOT'] . "/../src/auth/accessControl.php";
	require $_SERVER['DOCUMENT_ROOT'] . "/../src/pages/characterManager/model.php";
	require $_SERVER['DOCUMENT_ROOT'] . "/../src/pages/characterManager/controller.php";
	
	configureErrorChecking();

	require $_SERVER['DOCUMENT_ROOT'] . "/../config/config.php";
	
	checkForErrors();
	
	$PageMinimumAccessLevel = ["Super Admin", "ACL Admin", "Character Controller"];
	checkLastPage();
	$_SESSION["CurrentPage"] = "Character Manager";

	checkCookies();
	
	determineAccess($_SESSION["AccessRoles"], $PageMinimumAccessLevel);	
	
	$characterImageLink = "https://images.evetech.net/characters/" . $_SESSION["CharacterID"] . "/portrait";
	
?>

<!DOCTYPE html>
<html>
<head>
	<title>Character Manager</title>
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
			<div class="col-md-12">
				<h2 class="ColumnHeader">Relay Characters</h2>
				<br>
				<table style="width: 100%;">
				<tr>
					<th>Name</th>
					<th>Corporation</th>
					<th>Alliance</th>
                    <th>Roles</th>
                    <th class="text-left">
                        <form method="post" action="/characterManager/" onsubmit="return confirm('Checking all characters may take up to 5 seconds per character. Doing this with a lot of characters could take some time. Do you wish to proceed?');">
                            Check Status
                            <input type="hidden" name="check_character" id="check_character" value="All">
                            <input type="submit" value="Check All" class="btn btn-dark btn-small ml-5">
                        </form>
                    </th>
					<th>Remove</th>
				</tr>
				
					<?php generateCharacterArray(); ?>
				
				</table>
			</div>
		</div>
	</div>
</body>

<?php require $_SERVER['DOCUMENT_ROOT'] . "/../src/site/footer.php"; ?>

</html>