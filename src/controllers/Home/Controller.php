<?php

    namespace Ridley\Controllers\Home;

    class Controller implements \Ridley\Interfaces\Controller {
        
        private $databaseConnection;
        private $logger;
        private $configVariables;
        private $versionVariables;
        
        public function __construct(
            private \Ridley\Core\Dependencies\DependencyManager $dependencies
        ) {
            
            $this->databaseConnection = $this->dependencies->get("Database");
            $this->versionVariables = $this->dependencies->get("Version Variables");
            $this->logger = $this->dependencies->get("Logging");
            $this->configVariables = $this->dependencies->get("Configuration Variables");
            
            if (isset($_GET["action"]) and $_GET["action"] == "login") {
                
                $auth = new \Ridley\Core\Authorization\Base\AuthBase(
                    $this->logger, 
                    $this->databaseConnection, 
                    $this->configVariables,
                    $this->versionVariables
                );
                
                $auth->login("Relay", $this->configVariables["Client Scopes"]);
                
            }
            
        }
        
    }

?>