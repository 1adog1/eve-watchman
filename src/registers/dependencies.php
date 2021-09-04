<?php

    declare(strict_types = 1);

    /*
    
        Declare dependencies to be used by page controllers, models, and views.
        
        The $dependencyManager->register method accepts the following arguments:
        
            [string] key: An identifier for the dependency.
            [mixed] value: The dependency itself.
        
        Note that the keys "Controller", and "Model" are automatically set by the app and cannot be registered here.
    
    */
    
    $dependencyManager->register("Database", $masterDatabaseConnection);
    $dependencyManager->register("Logging", $siteLogger);
    $dependencyManager->register("Authorization Control", $userAuthorization);
    $dependencyManager->register("Log Type Groups", $siteLogger->getTypeGroups());
    $dependencyManager->register("Configuration Variables", $configVariables);
    $dependencyManager->register("Nav Links", $pageHandler->getNavLinks());
    $dependencyManager->register("Page Names", $pageHandler->getPageNames());
    $dependencyManager->register("URL Data", $pageHandler->getURLData());
    $dependencyManager->register("Page Link", $pageHandler->getPageLink());
    $dependencyManager->register("Page Code", $pageHandler->getPageCode());
    $dependencyManager->register("Page Has Model", $pageHandler->hasModel());
    $dependencyManager->register("Page Has Controller", $pageHandler->hasController());
    $dependencyManager->register("Page Has API", $pageHandler->hasAPI());
    $dependencyManager->register("CSRF Token", $userAuthorization->getCSRFToken());
    $dependencyManager->register("Login Status", $userAuthorization->getLoginStatus());
    $dependencyManager->register("Access Roles", $userAuthorization->getAccessRoles());
    $dependencyManager->register("Character Stats", $userAuthorization->getCharacterStats());
    
    //Dependencies above this line are required for critical base app functionality.

?>