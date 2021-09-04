<?php
    
    require __DIR__ . "/../../../config/config.php";
    require __DIR__ . "/../Autoloader/Autoloader.php";
    
    
    $siteDatabase = new \Ridley\Core\Database\DatabaseHandler($configVariables["Database Server"], $configVariables["Database Username"], $configVariables["Database Password"], $configVariables["Database Name"]);
    require __DIR__ . "/../../registers/databaseTables.php";
    $masterDatabaseConnection = $siteDatabase->databaseConnenction;
    
    
    $siteLogger = new \Ridley\Core\Logging\LogHandler($masterDatabaseConnection, $configVariables);
    require __DIR__ . "/../../registers/logTypes.php";
    
    
    $errorHandler = new \Ridley\Core\Errors\ErrorHandler($siteLogger);
    require __DIR__ . "/../../registers/errorHandlingMethods.php";
    
    
    $authorizationClass = "\\Ridley\\Core\\Authorization\\" . $configVariables["Auth Type"] . "\\AuthHandler";
    $userAuthorization = new $authorizationClass($siteLogger, $masterDatabaseConnection, $configVariables);
    
    
    $pageHandler = new \Ridley\Core\Paging\PageHandler($siteLogger, $masterDatabaseConnection, $userAuthorization->getLoginStatus(), $userAuthorization->getCharacterStats(), $userAuthorization->getAccessRoles(), $configVariables["Auth Cookie Name"]);
    
    
    $dependencyManager = new \Ridley\Core\Dependencies\DependencyManager();
    require __DIR__ . "/../../registers/dependencies.php";
    
    
    $page = new \Ridley\Core\Site\SiteCore($dependencyManager);

?>