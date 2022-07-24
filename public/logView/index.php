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

    
    $characterImageLink = "https://images.evetech.net/characters/" . $_SESSION["CharacterID"] . "/portrait";
    
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
                <form class="log_filter" id="custom_filter" method="post" action="/logView/">
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" name="access_g" value="true" id="access_g" <?php echo (isset($_POST["access_g"]) and $_POST["access_g"] == "true") ? ("checked") : (""); ?> > 
                            <label class="custom-control-label" for="access_g">Access Grants</label>
                        </div>

                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" name="access_d" value="true" id="access_d" <?php echo (isset($_POST["access_d"]) and $_POST["access_d"] == "true") ? ("checked") : (""); ?> > 
                            <label class="custom-control-label" for="access_d">Access Denials</label>
                        </div>

                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" name="login" value="true" id="login" <?php echo (isset($_POST["login"]) and $_POST["login"] == "true") ? ("checked") : (""); ?> > 
                            <label class="custom-control-label" for="login">Logins</label>
                        </div>
                        
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" name="search" value="true" id="search" <?php echo (isset($_POST["search"]) and $_POST["search"] == "true") ? ("checked") : (""); ?> > 
                            <label class="custom-control-label" for="search">User Searches</label>
                        </div>

                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" name="s_database" value="true" id="s_database" <?php echo (isset($_POST["s_database"]) and $_POST["s_database"] == "true") ? ("checked") : (""); ?> > 
                            <label class="custom-control-label" for="s_database">Server Database Edits</label>
                        </div>

                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" name="u_database" value="true" id="u_database" <?php echo (isset($_POST["u_database"]) and $_POST["u_database"] == "true") ? ("checked") : (""); ?> > 
                            <label class="custom-control-label" for="u_database">User Database Edits</label>
                        </div>
                        
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" name="r_sent" value="true" id="r_sent" <?php echo (isset($_POST["r_sent"]) and $_POST["r_sent"] == "true") ? ("checked") : (""); ?> > 
                            <label class="custom-control-label" for="r_sent">Relays Sent</label>
                        </div>
                        
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" name="r_error" value="true" id="r_error" <?php echo (isset($_POST["r_error"]) and $_POST["r_error"] == "true") ? ("checked") : (""); ?> > 
                            <label class="custom-control-label" for="r_error">Relay Errors</label>
                        </div>

                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" name="c_errors" value="true" id="c_errors" <?php echo (isset($_POST["c_errors"]) and $_POST["c_errors"] == "true") ? ("checked") : (""); ?> > 
                            <label class="custom-control-label" for="c_errors">Critical Errors</label>
                        </div>

                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" name="p_errors" value="true" id="p_errors" <?php echo (isset($_POST["p_errors"]) and $_POST["p_errors"] == "true") ? ("checked") : (""); ?> > 
                            <label class="custom-control-label" for="p_errors">Page Errors</label>
                        </div>
                        
                    </div>
                    
                    
                    <div class="form-group">
                        <label for="CharacterName">Character Name</label>
                        <input type="text" name="CharacterName" class="form-control" id="CharacterName" <?php echo (isset($_POST["CharacterName"]) and $_POST["CharacterName"] != "") ? ("value='" . htmlspecialchars($_POST["CharacterName"]). "'") : (""); ?> >
                    </div>
                    
                    <div class="form-group">
                        <label for="StartDate">Date Range</label>
                        <input type="date" name="StartDate" min="2019-01-01" max="2038-01-01" class="form-control" id="StartDate" <?php echo (isset($_POST["StartDate"]) and $_POST["StartDate"] != "") ? ("value='" . htmlspecialchars($_POST["StartDate"]). "'") : (""); ?> >
                        to 
                        <input type="date" name="EndDate" min="2019-01-01" max="2038-01-01" class="form-control" <?php echo (isset($_POST["EndDate"]) and $_POST["EndDate"] != "") ? ("value='" . htmlspecialchars($_POST["EndDate"]). "'") : (""); ?> >
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" name="all" value="true" id="all" <?php echo (isset($_POST["all"]) and $_POST["all"] == "true") ? ("checked") : (""); ?> > 
                            <label class="custom-control-label" for="all">Show All</label>
                        </div>
                    </div>

                    <input class="btn btn-dark btn-large btn-block text-center" type="submit" value="Filter">
                    
                    <br>
                    
                    <nav aria-label="Log Pagination">
                    
                        <ul class="pagination">
                        
                            <?php generatePageNav(); ?>
                        
                        </ul>
                    
                    </nav>
                    
                </form>
            
            </div>
            <div class="col-md-9">
                <br>
                <h2 class="ColumnHeader">Log Entries</h2>
                <hr>
                <table class="w-100 small">
                    <tr>
                        <th class="col-2" align="center">Timestamp</th>
                        <th class="col-1" align="center">Type</th>
                        <th class="col-1" align="center">Page</th>
                        <th class="col-1" align="center">Actor</th>
                        <th class="col-5" align="center">Details</th>
                        <th class="col-1" align="center">Real IP Address</th>
                        <th class="col-1" align="center">Forwarded IP Address</th>
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