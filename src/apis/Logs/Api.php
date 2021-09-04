<?php

    namespace Ridley\Apis\Logs;

    class Api implements \Ridley\Interfaces\Api {
        
        private $databaseConnection;
        
        public function __construct(
            private \Ridley\Core\Dependencies\DependencyManager $dependencies
        ) {
            
            $this->databaseConnection = $this->dependencies->get("Database");
            
            if (isset($_POST["Action"])) {
                
                if ($_POST["Action"] == "Get_Row" and isset($_POST["ID"])) {
                    
                    $this->getRow($_POST["ID"]);
                    
                }
                else {
                    
                    header($_SERVER["SERVER_PROTOCOL"] . " 400 Bad Request");
                    trigger_error("No valid combination of action and required secondary arguments was received.", E_USER_ERROR);
                    
                }
                
            }
            else {
                
                header($_SERVER["SERVER_PROTOCOL"] . " 400 Bad Request");
                trigger_error("Request is missing the action argument.", E_USER_ERROR);
                
            }
            
        }
        
        private function getRow($rowID) {
            
            $entryQuery = $this->databaseConnection->prepare("SELECT * FROM logs WHERE id=:id");
            $entryQuery->bindParam(":id", $rowID, \PDO::PARAM_INT);
            $entryQuery->execute();
            
            $entryResult = $entryQuery->fetch();
            
            if (!is_null($entryResult) and $entryResult !== false) {
                
                echo json_encode($entryResult);
            
            }
            else {
                
                header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
                trigger_error("A request was made for a log entry id that does not exist.", E_USER_ERROR);
                
            }
            
        }
                
    }

?>