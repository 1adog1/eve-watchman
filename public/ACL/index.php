<?php
	session_start();

	require $_SERVER['DOCUMENT_ROOT'] . "/../src/auth/accessControl.php";
	require $_SERVER['DOCUMENT_ROOT'] . "/../src/pages/ACL/model.php";
	require $_SERVER['DOCUMENT_ROOT'] . "/../src/pages/ACL/controller.php";

	configureErrorChecking();

	require $_SERVER['DOCUMENT_ROOT'] . "/../config/config.php";
	
	checkForErrors();

	$PageMinimumAccessLevel = ["Super Admin", "ACL Admin"];
	checkLastPage();
	$_SESSION["CurrentPage"] = "Access Control List";

	checkCookies();

	determineAccess($_SESSION["AccessRoles"], $PageMinimumAccessLevel);
	
	$characterImageLink = "https://images.evetech.net/characters/" . $_SESSION["CharacterID"] . "/portrait";
	
?>

<!DOCTYPE html>
<html>
<head>
	<title>Access Control List</title>
	<link rel="stylesheet" href="../resources/stylesheets/styleMasterSheet.css">
	<link rel="icon" href="../resources/images/favicon.ico">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="../resources/bootstrap/css/bootstrap.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
	<script src="../resources/bootstrap/js/bootstrap.bundle.js"></script> 
</head>
<style>

	.AdminCells {
		top: 0;
		display: block;
		margin-top: 2.5%;
		background: rgba(10, 10, 10, 0.75);
		text-align: left;
		font-family: "Noto Sans", sans-serif;
		font-size: 0.8vw;
		color: #f1f1f1;
		text-decoration: none;
		width: 90%;
		height: auto;
		padding: 2%;
		border-radius: 0.1vw;
		clear: both;
	}

	.AdminImage {
		margin-left: 1%;
		padding: 0.5%;
		border-radius: 0.25vw;
		overflow: hidden;
		width: 2.5vw;
		height: 2.5vw;
	}
	
</style>

<?php require $_SERVER['DOCUMENT_ROOT'] . "/../src/site/siteCore.php"; ?>

<body class="background">
	
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-5">
				<br>
				<h2 class="ColumnHeader">Search for an Entity</h2>
				<hr>
				<form class="admin_add" method="post" action="/ACL/">
					
					<div class="form-group">
						<label for="search">Search Term</label>
						<input type="text" name="search" class="form-control" id="search">
					</div>
					<div class="form-group">
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="strict" value="true" id="strict">
							<label class="custom-control-label" for="strict">Strict Search</label>
						</div>
					</div>
					<div class="form-group">
						<input type="submit" value="Search" class="btn btn-dark btn-large">
					</div>
				</form>
				
				<br>
				
				<?php
				
					adminSearch();
					
				?>
				
			</div>
			<div class="col-md-7">
				<br>
				<h2 class="ColumnHeader">Access List</h2>
				<hr>
				<table style="width: 100%;">
					<tr>
						<th align="center"> </th>
						<th align="center">Name</th>
						<th align="center">Access Roles</th>
						<th align="center">Remove</th>
					</tr>
					<?php
						
						displayACL();

					?>
				</table>
			</div>
		</div>
	</div>

</body>

<?php require $_SERVER['DOCUMENT_ROOT'] . "/../src/site/footer.php"; ?>

</html>