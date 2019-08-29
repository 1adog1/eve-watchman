<?php
	session_start();

	require $_SERVER['DOCUMENT_ROOT'] . "/../src/auth/accessControl.php";
	require $_SERVER['DOCUMENT_ROOT'] . "/../src/pages/logView/model.php";
	require $_SERVER['DOCUMENT_ROOT'] . "/../src/pages/logView/controller.php";

	configureErrorChecking();

	require $_SERVER['DOCUMENT_ROOT'] . "/../config/config.php";
	
	checkForErrors();

	$PageMinimumAccessLevel = ["Super Admin"];
	checkLastPage();
	$_SESSION["CurrentPage"] = "Site Logs";

	checkCookies();

	determineAccess($_SESSION["AccessRoles"], $PageMinimumAccessLevel);
	
	$characterImageLink = "https://imageserver.eveonline.com/Character/" . $_SESSION["CharacterID"] . "_128.jpg";
	
?>

<!DOCTYPE html>
<html>
<head>
	<title>Site Logs</title>
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
		<div class="row">
			<div class="col-md-3">
				<br>
				<h2 class="ColumnHeader">Filter Logs</h2>
				<hr>
				<form class="log_filter" method="post" action="/logView/">
					<div class="form-group">
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="access_g" value="true" id="access_g"> 
							<label class="custom-control-label" for="access_g">Access Grants</label>
						</div>

						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="access_d" value="true" id="access_d"> 
							<label class="custom-control-label" for="access_d">Access Denials</label>
						</div>

						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="login" value="true" id="login"> 
							<label class="custom-control-label" for="login">Logins</label>
						</div>

						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="s_database" value="true" id="s_database"> 
							<label class="custom-control-label" for="s_database">Server Database Edits</label>
						</div>

						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="u_database" value="true" id="u_database"> 
							<label class="custom-control-label" for="u_database">User Database Edits</label>
						</div>

						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="search" value="true" id="search"> 
							<label class="custom-control-label" for="search">User Searches</label>
						</div>

						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="c_errors" value="true" id="c_errors"> 
							<label class="custom-control-label" for="c_errors">Critical Errors</label>
						</div>

						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="p_errors" value="true" id="p_errors"> 
							<label class="custom-control-label" for="p_errors">Page Errors</label>
						</div>
					</div>
					
					
					<div class="form-group">
						<label for="CharacterName">Character Name</label>
						<input type="text" name="CharacterName" class="form-control" id="CharacterName">
					</div>
					
					<div class="form-group">
						<label for="StartDate">Date Range</label>
						<input type="date" name="StartDate" min="2019-01-01" max="2038-01-01" class="form-control" id="StartDate"> to <input type="date" name="EndDate" min="2019-01-01" max="2038-01-01" class="form-control">
					</div>

					<div class="form-group">
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" name="all" value="true" id="all"> 
							<label class="custom-control-label" for="all">Show All</label>
						</div>
					</div>

					<input class="btn btn-dark btn-large" type="submit" value="Filter">
				</form>
			
			</div>
			<div class="col-md-9">
				<br>
				<h2 class="ColumnHeader">Log Entries</h2>
				<hr>
				<table style="width: 100%;">
					<tr>
						<th align="center">Timestamp</th>
						<th align="center">Type</th>
						<th align="center">Page</th>
						<th align="center">Actor</th>
						<th align="center">Details</th>
						<th align="center">Real IP Address</th>
						<th align="center">Forwarded IP Address</th>
					</tr>
				<?php
				
					$logArray = getLogArray();
					
					displayLogs($logArray, $maxTableRows);
			
				?>
				</table>
			</div>
		</div>
	</div>

</body>

<?php require $_SERVER['DOCUMENT_ROOT'] . "/../src/site/footer.php"; ?>

</html>