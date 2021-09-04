<?php

namespace Ridley\Tests\Pages;

class Test implements \Ridley\Interfaces\Test {
    
        private $pageTable = [];
        private $registeredRoles = ["Super Admin"];
    
        public function __construct(
            private bool $printResult, 
            private bool $detailedOutput
        ) {
            
            require __DIR__ . "/../../registers/pages.php";
            require __DIR__ . "/../../registers/accessRoles.php";
            
        }
        
        private function registerPage(
            string $linkToUse, 
            string $nameToUse, 
            string $codeToUse, 
            bool $useModel = false, 
            bool $useController = false, 
            bool $useAPI = false, 
            bool $inNav = true, 
            bool $loginRequired = false, 
            array $accessRoles = []
        ) {
            
            $this->pageTable[$linkToUse] = [
                "Name" => $nameToUse, 
                "Code" => $codeToUse, 
                "Link" => ("/" . $linkToUse), 
                "Has Model" => $useModel, 
                "Has Controller" => $useController, 
                "Has API" => $useAPI, 
                "In Navbar" => $inNav, 
                "Login Required" => $loginRequired, 
                "Access Roles" => $accessRoles
            ];
            
        }
        
        private function registerRole(string $newRole) {
            
            $this->registeredRoles[] = $newRole;
            
        }
        
        private function checkClasses(string $pageCode, bool $useModel, bool $useController, bool $useAPI) {
            
            $pageTypes = [
                "View" => true, 
                "Model" => $useModel, 
                "Controller" => $useController, 
                "Api" => $useAPI
            ];
            
            $success = true;
            
            foreach ($pageTypes as $type => $inUse) {
                
                if ($inUse) {
                    
                    $className = "\\Ridley\\" . $type . "s\\" . $pageCode . "\\" . $type;
                    $interfaceName = "\\Ridley\\Interfaces\\" . $type;
                    
                    if (class_exists($className)) {
                        
                        if ($this->detailedOutput) {
                            
                            fwrite(STDOUT, "\n[INFO] The " . $type . " class belonging to page with code " . $pageCode . " exists.");
                            
                        }
                        
                        if (is_subclass_of($className, $interfaceName)) {
                            
                            if ($this->detailedOutput) {
                                
                                fwrite(STDOUT, "\n[INFO] The " . $type . " class belonging to page with code " . $pageCode . " implements the correct interface.");
                                
                            }
                            
                        }
                        else {
                            
                            $success = false;
                            fwrite(STDOUT, "\n[ISSUE] The " . $type . " class belonging to page with code " . $pageCode . " does not implement the required interface!");
                            
                        }
                        
                    }
                    else {
                        
                        $success = false;
                        fwrite(STDOUT, "\n[ISSUE] The " . $type . " class belonging to page with code " . $pageCode . " does not exist!");
                        
                    }
                    
                }
                
            }
            
            return $success;
            
        }
        
        private function verifyAccessRoles(string $pageCode, array $accessRoles) {
            
            if (!array_diff($accessRoles, $this->registeredRoles)) {
                
                if ($this->detailedOutput) {
                    
                    fwrite(STDOUT, "\n[INFO] The page with code " . $pageCode . " does not reference any unknown Access Roles.");
                    
                }
                
                return true;
                
            }
            else {
                
                fwrite(STDOUT, "\n[ISSUE] The page with code " . $pageCode . " references one or more Access Roles that have not been registered!");
                return false;
                
            }
            
        }
        
        public function runTest(): bool {
            
            $success = true;
            
            foreach ($this->pageTable as $pageLink => $pageData) {
                
                if (
                    !$this->checkClasses($pageData["Code"], $pageData["Has Model"], $pageData["Has Controller"], $pageData["Has API"]) or 
                    !$this->verifyAccessRoles($pageData["Code"], $pageData["Access Roles"]) 
                ) {
                    
                    $success = false;
                    
                }
                
            }
            
            if ($this->printResult) {
                
                $statusText = $success ? "[PASS]" : "[FAIL]";
                fwrite(STDOUT, "\n" . $statusText . " Page Tests");
                
            }
            
            return $success;
            
        }
    
}

?>