<?php

    namespace Ridley\Models\Home;

    class Model implements \Ridley\Interfaces\Model {
        
        private $controller;
        private $databaseConnection;
        private $logger;
        private $configVariables;
        private $loginStatus;
        private $characterData;
        
        public function __construct(
            private \Ridley\Core\Dependencies\DependencyManager $dependencies
        ) {
            
            $this->controller = $this->dependencies->get("Controller");
            $this->databaseConnection = $this->dependencies->get("Database");
            $this->logger = $this->dependencies->get("Logging");
            $this->configVariables = $this->dependencies->get("Configuration Variables");
            $this->loginStatus = $this->dependencies->get("Login Status");
            $this->characterData = $this->dependencies->get("Character Stats");
            
        }
        
        public function checkIfRelay() {
            
            if ($this->loginStatus) {
            
                $relayQuery = $this->databaseConnection->prepare("SELECT * FROM refreshtokens WHERE type=:type AND characterid=:characterid");
                $relayQuery->bindValue(":type", "Relay");
                $relayQuery->bindParam(":characterid", $this->characterData["Character ID"], \PDO::PARAM_INT);
                $relayQuery->execute();
                $relayData = $relayQuery->fetch();
                
                if (!empty($relayData)) {
                
                    $auth = new \Ridley\Core\Authorization\Base\AuthBase(
                        $this->logger, 
                        $this->databaseConnection, 
                        $this->configVariables
                    );
                    
                    $accessToken = $auth->getAccessToken("Relay", $this->characterData["Character ID"]);
                    
                    if ($accessToken !== False) {
                        
                        return True;
                        
                    }
                    else {
                        
                        return False;
                        
                    }
                    
                }
                else {
                    
                    return False;
                    
                }
            
            }
            
        }
        
    }

?>