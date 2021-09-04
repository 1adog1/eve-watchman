<?php

    $configVariables = [];

    $configData = parse_ini_file(__DIR__ . "/config.ini");
    
    //EVE AUTHENTICATION CONFIGURATION
    $configVariables["Client ID"] = $configData["ClientID"];
    $configVariables["Client Secret"] = $configData["ClientSecret"];
    $configVariables["Client Scopes"] = $configData["ClientScopes"];
    $configVariables["Default Scopes"] = $configData["DefaultScopes"];
    $configVariables["Client Redirect"] = $configData["ClientRedirect"];
    $configVariables["Auth Type"] = $configData["AuthType"];
    $configVariables["Super Admins"] = explode(",", str_replace(" ", "", $configData["SuperAdmins"]));
    
    //NEUCORE AUTHENTICATION CONFIGURATION
    $configVariables["NeuCore ID"] = $configData["AppID"];
    $configVariables["NeuCore Secret"] = $configData["AppSecret"];
    $configVariables["NeuCore URL"] = $configData["AppURL"];

    //DATABASE SERVER CONFIGURATION
    $configVariables["Database Server"] = $configData["DatabaseServer"] . ":" . $configData["DatabasePort"];
    $configVariables["Database Username"] = $configData["DatabaseUsername"];
    $configVariables["Database Password"] = $configData["DatabasePassword"];
    
    //DATABASE NAME CONFIGURATION
    $configVariables["Database Name"] = $configData["DatabaseName"];
    
    //SITE CONFIGURATION
    $configVariables["Auth Cookie Name"] = $configData["AuthCookieName"];
    $configVariables["Session Time"] = $configData["SessionTime"];
    $configVariables["Auth Cache Time"] = $configData["AuthCacheTime"];
    $configVariables["Max Table Rows"] = $configData["MaxTableRows"];
    $configVariables["Store Visitor IPs"] = boolval($configData["StoreVisitorIPs"]);
    
?>