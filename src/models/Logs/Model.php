<?php

    namespace Ridley\Models\Logs;

    class Model implements \Ridley\Interfaces\Model {
        
        private $controller;
        private $databaseConnection;
        
        public function __construct(
            private \Ridley\Core\Dependencies\DependencyManager $dependencies
        ) {
            
            $this->controller = $this->dependencies->get("Controller");
            $this->databaseConnection = $this->dependencies->get("Database");
            
        }
        
        public function getRowCount() {
            
            $toFilter = $this->controller->getFilterRequest();
            
            $countQuery = $this->databaseConnection->prepare("SELECT COUNT(id) FROM logs " . $toFilter["Request"]);
            
            foreach ($toFilter["Variables"] as $eachVariable => $eachValue) {
                
                $countQuery->bindValue($eachVariable, $eachValue["Value"], $eachValue["Type"]);
                
            }
            
            $countQuery->execute();
            $entryCount = $countQuery->fetch()[0];
            
            return $entryCount;
            
        }
        
        public function queryRows() {
            
            $toFilter = $this->controller->getFilterRequest();
            
            $pageOffset = ($this->controller->getPageNumber() * 100);
            
            $logQuery = $this->databaseConnection->prepare("SELECT id, timestamp, type, page, actor FROM logs " . $toFilter["Request"] . " ORDER BY id DESC LIMIT 100 OFFSET :offset");
            $logQuery->bindParam(":offset", $pageOffset, \PDO::PARAM_INT);
            
            foreach ($toFilter["Variables"] as $eachVariable => $eachValue) {
                
                $logQuery->bindValue($eachVariable, $eachValue["Value"], $eachValue["Type"]);
                
            }
            
            $logQuery->execute();
            $logData = $logQuery->fetchAll();
            
            return $logData;
            
        }
        
    }

?>