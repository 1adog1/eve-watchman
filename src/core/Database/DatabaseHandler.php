<?php

    namespace Ridley\Core\Database;

    class DatabaseHandler {
        
        public $databaseConnenction;
        
        private $tables = [
            [
                "Name" => "logs",
                "Columns" => [
                    ["Name" => "id", "Type" => "BIGINT", "Special" => "primary key AUTO_INCREMENT"], 
                    ["Name" => "timestamp", "Type" => "BIGINT"], 
                    ["Name" => "type", "Type" => "TEXT"], 
                    ["Name" => "page", "Type" => "TEXT"], 
                    ["Name" => "actor", "Type" => "TEXT"], 
                    ["Name" => "details", "Type" => "LONGTEXT"], 
                    ["Name" => "trueip", "Type" => "TEXT"], 
                    ["Name" => "forwardip", "Type" => "TEXT"]
                ]
            ],
            [
                "Name" => "access",
                "Columns" => [
                    ["Name" => "type", "Type" => "TEXT"], 
                    ["Name" => "id", "Type" => "BIGINT"], 
                    ["Name" => "name", "Type" => "TEXT"], 
                    ["Name" => "roles", "Type" => "TEXT"]
                ]
            ],
            [
                "Name" => "logins",
                "Columns" => [
                    ["Name" => "type", "Type" => "TEXT"], 
                    ["Name" => "state", "Type" => "TEXT"], 
                    ["Name" => "scopes", "Type" => "TEXT"], 
                    ["Name" => "expiration", "Type" => "BIGINT"]
                ]
            ],
            [
                "Name" => "sessions",
                "Columns" => [
                    ["Name" => "id", "Type" => "TEXT"], 
                    ["Name" => "isloggedin", "Type" => "TINYINT"], 
                    ["Name" => "accessroles", "Type" => "TEXT"], 
                    ["Name" => "characterid", "Type" => "TEXT"], 
                    ["Name" => "charactername", "Type" => "TEXT"], 
                    ["Name" => "currentpage", "Type" => "TEXT"], 
                    ["Name" => "csrftoken", "Type" => "TEXT"], 
                    ["Name" => "expiration", "Type" => "BIGINT"], 
                    ["Name" => "recheck", "Type" => "BIGINT"]
                ]
            ],
            [
                "Name" => "refreshtokens",
                "Columns" => [
                    ["Name" => "type", "Type" => "TEXT"], 
                    ["Name" => "characterid", "Type" => "TEXT"], 
                    ["Name" => "scopes", "Type" => "TEXT"], 
                    ["Name" => "refreshtoken", "Type" => "TEXT"], 
                    ["Name" => "accesstoken", "Type" => "TEXT"], 
                    ["Name" => "recheck", "Type" => "BIGINT"]
                ]
            ],
            [
                "Name" => "esicache",
                "Columns" => [
                    ["Name" => "endpoint", "Type" => "TEXT"], 
                    ["Name" => "hash", "Type" => "TEXT"], 
                    ["Name" => "expiration", "Type" => "BIGINT"], 
                    ["Name" => "response", "Type" => "LONGTEXT"]
                ]
            ],
        ];
        
        function __construct($databaseServer, $databaseUsername, $databasePassword, $databaseName) {
            
            $this->databaseConnenction = new \PDO("mysql:host=$databaseServer", $databaseUsername, $databasePassword);
            
            $this->databaseConnenction->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            
            $this->databaseConnenction->exec("CREATE DATABASE IF NOT EXISTS $databaseName");
            $this->databaseConnenction->exec("use $databaseName");
            
            foreach ($this->tables as $eachTable) {
                
                $this->createTable($eachTable["Name"], $eachTable["Columns"]);
                
            }
            
        }
        
        public function register(string $tableName, array ...$tableColumns) {
            
            $tableToAdd = ["Name" => $tableName, "Columns" => $tableColumns];
            
            $this->tables[] = $tableToAdd;
            
            $this->createTable($tableName, $tableColumns);
            
        }
        
        private function createTable($tableName, $columns) {
            
            if ($this->checkTableExists($tableName) === false) {
                
                $creationStatement = "CREATE TABLE $tableName (";
                
                foreach ($columns as $eachColumn) {
                    
                    $creationStatement .= ($eachColumn["Name"] . " " . $eachColumn["Type"]);
                    
                    if (isset($eachColumn["Special"])) {
                        $creationStatement .= (" " . $eachColumn["Special"]);
                    }
                    
                    $creationStatement .= (", ");
                    
                }
                
                $creationStatement = (substr($creationStatement, 0, -2) . ")");
                
                $this->databaseConnenction->exec($creationStatement);
                
            }
            
        }
        
        private function checkTableExists($tableName) {
            
            try {
                
                $tableTest = $this->databaseConnenction->query("SELECT 1 FROM $tableName LIMIT 1");
                
                $testVariable = $tableTest->fetchAll();
                
                return True;
                
            }
            catch (\Exception $throwAway) {
                return False;
            }
            
        }
        
    }

?>