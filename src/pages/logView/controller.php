<?php

function getPageNumber() {
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        
        if (isset($_POST["page"])) {
            $page = $_POST["page"];
        }
        else {
            $page = 1;
        }
        
    }
    else {
        
        if (isset($_GET["page"])) {
            $page = $_GET["page"];
        }
        else {
            $page = 1;
        }
        
    }
    
    return $page;
    
}

function getPageOffset() {
    
    $page = getPageNumber();
    
    if (is_numeric($page)) {
        $pageOffset = (max(0, ($page - 1)) * 100);
    }
    else {
        $pageOffset = 0;
    }
    
    return $pageOffset;
    
}

function getWherePrefix($alreadyWhere) {
    
    if ($alreadyWhere === true) {
        $prefix = " AND ";
    }
    else {
        $prefix = " WHERE ";
    }
    
    return $prefix;
    
}

function checkFilter() {
    
    $whereData = ["Prepare" => "", "Arguments" => []];
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
                
        $alreadyWhere = false;
        
        $whereArray = [];
        
        if (isset($_POST["CharacterName"]) and htmlspecialchars($_POST["CharacterName"]) != "") {
            
            $whereData["Prepare"] .= (getWherePrefix($alreadyWhere) . "LOWER(actor) LIKE LOWER(:actor)");
            $whereData["Arguments"][] = ["Placeholder" => ":actor", "Value" => ("%" . htmlspecialchars($_POST["CharacterName"]) . "%"), "Type" => PDO::PARAM_STR];
            $alreadyWhere = true;
            
        }
        
        if (isset($_POST["StartDate"]) and $_POST["StartDate"] != "") {
            
            if (count(explode("-", htmlspecialchars($_POST["StartDate"]))) == 3) {
            
                $whereData["Prepare"] .= (getWherePrefix($alreadyWhere) . "timestamp >= :starttime");
                $whereData["Arguments"][] = ["Placeholder" => ":starttime", "Value" => strtotime(htmlspecialchars($_POST["StartDate"])), "Type" => PDO::PARAM_INT];
                $alreadyWhere = true;
            
            }
            
        }
        
        if (isset($_POST["EndDate"]) and $_POST["EndDate"] != "") {
            
            if (count(explode("-", htmlspecialchars($_POST["EndDate"]))) == 3) {
            
                $whereData["Prepare"] .= (getWherePrefix($alreadyWhere) . "timestamp <= :endtime");
                $whereData["Arguments"][] = ["Placeholder" => ":endtime", "Value" => strtotime(htmlspecialchars($_POST["EndDate"])), "Type" => PDO::PARAM_INT];
                $alreadyWhere = true;
            
            }
            
        }
        
        if (isset($_POST["access_g"]) and $_POST["access_g"] == "true") {
            array_push($whereArray, "Page Access Granted");
        }
        
        if (isset($_POST["access_d"]) and $_POST["access_d"] == "true") {
            array_push($whereArray, "Page Access Denied");
        }
        
        if (isset($_POST["login"]) and $_POST["login"] == "true") {
            array_push($whereArray, "User Login");
        }
        
        if (isset($_POST["search"]) and $_POST["search"] == "true") {
            array_push($whereArray, "User Search");
        }
        
        if (isset($_POST["s_database"]) and $_POST["s_database"] == "true") {
            array_push($whereArray, "Automated Database Edit", "Automated Database Creation", "Outside Database Posted");
        }

        if (isset($_POST["u_database"]) and $_POST["u_database"] == "true") {
            array_push($whereArray, "User Database Edit");
        }
        
        if (isset($_POST["r_sent"]) and $_POST["r_sent"] == "true") {
            array_push($whereArray, "Relay Sent");
        }
        
        if (isset($_POST["r_error"]) and $_POST["r_error"] == "true") {
            array_push($whereArray, "Relay Error");
        }
        
        if (isset($_POST["c_errors"]) and $_POST["c_errors"] == "true") {
            array_push($whereArray, "Critical Error");
        }

        if (isset($_POST["p_errors"]) and $_POST["p_errors"] == "true") {
            array_push($whereArray, "Page Error");
        }
        
        if (!empty($whereArray)) {
            
            $whereInPlaceholders = [];
                        
            $tempCounter = 0;
            foreach ($whereArray as $eachWhere) {
                
                $whereInPlaceholders[] = (":inArgument" . $tempCounter);
                $whereData["Arguments"][] = ["Placeholder" => (":inArgument" . $tempCounter), "Value" => $eachWhere, "Type" => PDO::PARAM_STR];
                $tempCounter++; 
                
            }
            
            $inStatement = ("type IN (" . implode(", ", $whereInPlaceholders) . ")");
            
            $whereData["Prepare"] .= (getWherePrefix($alreadyWhere) . $inStatement);
            
        }
        
        if (isset($_POST["all"]) and $_POST["all"] == "true") {
            
            $whereData["Prepare"] = "";
            $whereData["Arguments"] = [];
            
        }
        
    }

    return $whereData;
    
}

?>