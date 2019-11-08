<?php
	session_start();

	require $_SERVER['DOCUMENT_ROOT'] . "/../src/auth/accessControl.php";

	configureErrorChecking();

	require $_SERVER['DOCUMENT_ROOT'] . "/../config/config.php";
	
	checkForErrors();

	checkLastPage();
	$_SESSION["CurrentPage"] = "Access Denied";
	checkCookies();

	$characterImageLink = "https://images.evetech.net/characters/" . $_SESSION["CharacterID"] . "/portrait";
	
?>

<!DOCTYPE html>
<html>
<head>
	<title>Access Denied!</title>
	<link rel="stylesheet" href="../resources/stylesheets/styleMasterSheet.css">
	<link rel="icon" href="../resources/images/favicon.ico">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="../resources/bootstrap/css/bootstrap.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
	<script src="../resources/bootstrap/js/bootstrap.bundle.js"></script> 
</head>

<?php require $_SERVER['DOCUMENT_ROOT'] . "/../src/site/siteCore.php"; ?>

<body class="background">
	<div class="container-fluid">
		<br>
		<br>
		<div class="row">
			<div class="col-md-3">			
			</div>
			<div class="col-md-6">
				<div class="card bg-danger">
					<div class="card-header">
						<div class="display-4 text-center">Access Denied!</div>
					</div>
					<div class="card-body">
						<p class="text-center">You either don't have the required role to access this page, or it doesn't exist.</p>
					</div>
				</div>
			</div>
			<div class="col-md-3">
			</div>
		</div>
	</div>
</body>


<?php require $_SERVER['DOCUMENT_ROOT'] . "/../src/site/footer.php"; ?>

</html>
