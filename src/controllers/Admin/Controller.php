<?php

    namespace Ridley\Controllers\Admin;

    class Controller implements \Ridley\Interfaces\Controller {
        
        private $knownGroups = [
            "Neucore" => [],
            "Character" => [],
            "Corporation" => [],
            "Alliance" => []
        ];
        private $databaseConnection;
        private $logger;
        private $configVariables;
        
        public function __construct(
            private \Ridley\Core\Dependencies\DependencyManager $dependencies
        ) {
            
            $this->databaseConnection = $this->dependencies->get("Database");
            $this->logger = $this->dependencies->get("Logging");
            $this->configVariables = $this->dependencies->get("Configuration Variables");
            
            $authMethod = "populate" . $this->configVariables["Auth Type"];
            $this->$authMethod();
            
        }
        
        public function passKnownGroups() {
            
            return $this->knownGroups;
            
        }
        
        private function populateEve() {
            
            $populateQuery = $this->databaseConnection->prepare("SELECT * FROM access WHERE type IN ('Character', 'Corporation', 'Alliance')");
            $populateQuery->execute();
            $populateData = $populateQuery->fetchAll();
            
            if (!empty($populateData)) {
                
                foreach ($populateData as $eachGroup) {
                    
                    $this->knownGroups[$eachGroup["type"]][$eachGroup["id"]] = new \Ridley\Objects\Admin\Groups\Eve($this->dependencies, $eachGroup["id"], $eachGroup["name"], $eachGroup["type"]);
                    
                }
                
            }
        }
        
        private function populateNeucore() {
            
            $neucoreToken = base64_encode($this->configVariables["NeuCore ID"] . ":" . $this->configVariables["NeuCore Secret"]);
        
            $appRequestURL = $this->configVariables["NeuCore URL"] . "api/app/v1/show";
            
            $appRequestOptions = ["http" => ["ignore_errors" => true, "method" => "GET", "header" => ["Content-Type:application/json", "Authorization: Bearer " . $neucoreToken]]];
            $appRequestContext = stream_context_create($appRequestOptions);
            
            $appResponse = file_get_contents($appRequestURL, false, $appRequestContext);
            
            $appStatus = $http_response_header[0];
            
            if (str_contains($appStatus, "200")) {
            
                $appResponseData = json_decode($appResponse, true);
                
                foreach ($appResponseData["groups"] as $eachGroup) {
                    
                    $this->knownGroups["Neucore"][$eachGroup["id"]] = new \Ridley\Objects\Admin\Groups\Neucore($this->dependencies, $eachGroup["id"], $eachGroup["name"]);
                    
                }
                
            }
            
        }
        
    }

?>