<?php
	session_start();

	require $_SERVER['DOCUMENT_ROOT'] . "/../src/auth/accessControl.php";
	
	configureErrorChecking();

	require $_SERVER['DOCUMENT_ROOT'] . "/../config/config.php";
	
	checkForErrors();

	checkLastPage();
	$_SESSION["CurrentPage"] = "Home";

	checkCookies();
	
	$characterImageLink = "https://images.evetech.net/characters/" . $_SESSION["CharacterID"] . "/portrait";
	
?>

<!DOCTYPE html>
<html>
<head>
	<title>Eve Watchman</title>
	<link rel="stylesheet" href="../resources/stylesheets/styleMasterSheet.css">
	<link rel="icon" href="../resources/images/favicon.ico">
	
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="../resources/bootstrap/css/bootstrap.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
	<script src="../resources/bootstrap/js/bootstrap.bundle.js"></script>
	
	<meta property="og:title" content="Eve Watchman">
	<meta property="og:description" content="The Eve Watchman Website">
	<meta property="og:type" content="website">
	<meta property="og:url" content="<?php echo $siteURL; ?>">

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
			
				<?php if (isset($_SESSION["RelayStatus"]) and $_SESSION["RelayStatus"] == "Added") : ?>

					<div class="card bg-success">
						<div class="card-header">
							<h2 class="text-center">Your Character Has Been Successfully Added!</h2>
						</div>
					</div>
					
					<?php unset($_SESSION["RelayStatus"]); ?>

					<br>
					<br>
						
				<?php elseif (isset($_SESSION["RelayStatus"]) and $_SESSION["RelayStatus"] == "Updated") : ?>

					<div class="card bg-success">
						<div class="card-header">
							<h2 class="text-center">Your Character Has Been Successfully Updated!</h2>
						</div>
					</div>
					
					<?php unset($_SESSION["RelayStatus"]); ?>

					<br>
					<br>
					
				<?php endif; ?>			
			
				<div class="card bg-dark">
					<div class="card-header">
						<div class="display-4 text-center">Welcome to Eve Watchman!</div>
					</div>
					<div class="card-body">
						<p class="text-center">This app provides corporations and alliances with notifcations related to their structures.</p>
						<br>
						<p class="text-center">To configure the site or manage notifications, you can login through the <strong>Management Login</strong> option.</p>
						<p class="text-center">To add a character whose notifications will be relayed, login via the <strong>Relay Character Login</strong> option.</p>
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