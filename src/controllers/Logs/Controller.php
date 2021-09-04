<?php

    namespace Ridley\Controllers\Logs;

    class Controller implements \Ridley\Interfaces\Controller {
        
        private $pageList;
        private $typeList;
        
        public function __construct(
            private \Ridley\Core\Dependencies\DependencyManager $dependencies
        ) {
            
            $this->pageList = $this->dependencies->get("Page Names");
            $this->typeList = $this->dependencies->get("Log Type Groups");
            
        }
        
        public function generateRequestPrefix($currentRequest) {
            
            if ($currentRequest === "") {
                
                return "WHERE ";
                
            }
            else {
                
                return " AND ";
                
            }
        }
        
        //This function dynamically generates an SQL WHERE clause in the form of a string, for the purpose of filtering out log entries. 
        public function getFilterRequest() {
            
            $filterDetails = [
                "Request" => "",
                "Variables" => []
            ];
            
            //Filter by Actor
            if (isset($_POST["actor-selection"]) and $_POST["actor-selection"] != "") {
                
                $filterDetails["Request"] .= (
                    $this->generateRequestPrefix($filterDetails["Request"])
                    . "actor = :actor"
                );
                
                $filterDetails["Variables"][":actor"] = ["Value" => $_POST["actor-selection"], "Type" => \PDO::PARAM_STR];
                
            }
            
            //Filter by Start Date
            if (isset($_POST["date-start"]) and $_POST["date-start"] != "") {
                
                $incomingStartTime = strtotime($_POST["date-start"]);
                
                if ($incomingStartTime !== false) {
                    
                    $filterDetails["Request"] .= (
                        $this->generateRequestPrefix($filterDetails["Request"])
                        . "timestamp >= :date_start"
                    );
                    
                    $filterDetails["Variables"][":date_start"] = ["Value" => $incomingStartTime, "Type" => \PDO::PARAM_INT];
                    
                }
                
            }
            
            //Filter by End Date
            if (isset($_POST["date-end"]) and $_POST["date-end"] != "") {
                
                $incomingEndTime = strtotime($_POST["date-end"]);
                
                if ($incomingEndTime !== false) {
                    
                    $incomingEndTime += 86400;
                
                    $filterDetails["Request"] .= (
                        $this->generateRequestPrefix($filterDetails["Request"])
                        . "timestamp <= :date_end"
                    );
                    
                    $filterDetails["Variables"][":date_end"] = ["Value" => $incomingEndTime, "Type" => \PDO::PARAM_INT];
                    
                }
            }
            
            // Filter by Page
            if (isset($_POST["page-selection"]) and $_POST["page-selection"] != "" and isset($this->pageList[$_POST["page-selection"]])) {
                
                $filterDetails["Request"] .= (
                    $this->generateRequestPrefix($filterDetails["Request"])
                    . "page = :page"
                );
                
                $filterDetails["Variables"][":page"] = ["Value" => $this->pageList[$_POST["page-selection"]], "Type" => \PDO::PARAM_STR];
                
            }
            
            //Filter by Type
            $typeSubstring = "type IN (";
            
            $typeCounter = 0;
            foreach ($this->typeList as $eachID => $eachType) {
                
                if (isset($_POST["type-" . $eachID]) and $_POST["type-" . $eachID] === "true" and !empty($eachType["Types"])) {
                    
                    foreach ($eachType["Types"] as $eachRawType) {
                        
                        $typeSubstring .= (":type_" . $typeCounter . ",");
                        $filterDetails["Variables"][":type_" . $typeCounter] = ["Value" => $eachRawType, "Type" => \PDO::PARAM_STR];
                        $typeCounter++;
                        
                    }
                    
                }
                
            }
            
            $typeSubstring = (rtrim($typeSubstring, ",") . ")");
            
            if (!str_ends_with($typeSubstring, "()")) {
                
                $filterDetails["Request"] .= (
                    $this->generateRequestPrefix($filterDetails["Request"])
                    . $typeSubstring
                );
                
            }
            
            return $filterDetails;
            
        }
        
        public function getPageNumber() {
            
            if (
                isset($_POST["page"]) 
                and is_numeric($_POST["page"]) 
                and (
                    is_int($_POST["page"]) 
                    or ctype_digit($_POST["page"])
                )
            ) {
                return max(0, (intval($_POST["page"] - 1)));
            }
            else {
                return 0;
            }
            
        }
        
    }

?>