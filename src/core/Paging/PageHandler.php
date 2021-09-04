<?php

    namespace Ridley\Core\Paging;

    class PageHandler {
        
        private $userName;
        
        private $urlData = ["Page Name" => False, "Page Topic" => False, "Page Number" => False, "Action" => False, "Core Action" => False];
        private $currentModel = false;
        private $currentController = false;
        private $currentAPI = false;
        private $pageCode;
        private $pageLink;
        
        private $pageTable = [];
        
        function __construct(
            private $pageHandlingLogger, 
            private $pageHandlingConnection,
            private $isLoggedIn, 
            private $userStats, 
            private $userAccessRoles,
            private $cookieName
        ) {
            
            require __DIR__ . "/../../registers/pages.php";
            
            $this->setURLData();
            
            if ($this->urlData["Page Name"] !== False) {
                
                $sanitizedPageLink = htmlspecialchars($this->urlData["Page Name"]);
            
            }
            else {
                
                $sanitizedPageLink = false;
                
            }
            
            if (isset($this->userStats["Character Name"])) {
                
                $this->userName = $this->userStats["Character Name"];
                
            }
            else {
                
                $this->userName = "Unknown";
                
            }
            
            $this->getPageData($sanitizedPageLink);
            
        }
        
        public function hasModel() {
            
            return $this->currentModel;
            
        }
        
        public function hasController() {
            
            return $this->currentController;
            
        }
        
        public function hasAPI() {
            
            return $this->currentAPI;
            
        }
        
        public function getPageCode() {
             
             return $this->pageCode;
             
        }
        
        public function getPageLink() {
             
             return $this->pageLink;
             
        }
        
        public function getURLData() {
            
            return $this->urlData;
            
        }
        
        public function getPageNames() {
            
            $pageList = [];
            
            foreach ($this->pageTable as $eachPage => $eachPageData) {
                
                $pageList[$eachPage] = $eachPageData["Name"];
                
            }
            
            return $pageList;
            
        }
        
        public function getNavLinks() {
            
            $navLinks = [];
            
            foreach ($this->pageTable as $eachPage => $eachPageData) {
                
                if ($this->checkAccess($eachPageData["Login Required"], $eachPageData["Access Roles"]) and $eachPageData["In Navbar"]) {
                
                    $navLinks[] = ["Name" => $eachPageData["Name"], "Link" => $eachPage, "Code" => $eachPageData["Code"]];
                
                }
                
            }
            
            return $navLinks; 
            
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
        
        private function setURLData() {
            
            $rawURL = urldecode($_SERVER["REQUEST_URI"]);
            $parsedURL = parse_url($rawURL, PHP_URL_PATH);
            $parsedPath = preg_split("@/@", $parsedURL, null, PREG_SPLIT_NO_EMPTY);
            
            if (count($parsedPath) >= 1) {
                
                $this->urlData["Page Name"] = $parsedPath[0];
                
            }
            if (count($parsedPath) >= 2) {
                
                $this->urlData["Page Topic"] = $parsedPath[1];
                
            }
            if (count($parsedPath) >= 3) {
                
                $this->urlData["Page Number"] = $parsedPath[2];
                
            }
            
            if (isset($_GET["action"])) {
                
                $this->urlData["Action"] = $_GET["action"];
                
            }
            if (isset($_GET["core_action"])) {
                
                $this->urlData["Core Action"] = $_GET["core_action"];
                
            }
            
        }
        
        private function setPageDetails($pageData) {
            
            $this->pageCode = $pageData["Code"];
            $this->pageLink = $pageData["Link"];
            $this->currentModel = $pageData["Has Model"];
            $this->currentController = $pageData["Has Controller"];
            $this->currentAPI = $pageData["Has API"];
            
        }
        
        private function getLastPage() {
            
            if (isset($_COOKIE[$this->cookieName])) {
                
                $pullPage = $this->pageHandlingConnection->prepare("SELECT currentpage FROM sessions WHERE id=:id");
                $pullPage->bindParam(":id", $_COOKIE[$this->cookieName]);
                $pullPage->execute();
                $currentPage = $pullPage->fetchAll();
                
                if (!empty($currentPage)) {
                    
                    return $currentPage[0]["currentpage"];
                    
                }
                else {
                    
                    return false;
                    
                }
                
            }
            else {
                
                return false;
                
            }
            
        }
        
        private function updateLastPage($newPage) {
            
            if (isset($_COOKIE[$this->cookieName])) {
                
                $pullPage = $this->pageHandlingConnection->prepare("SELECT currentpage FROM sessions WHERE id=:id");
                $pullPage->bindParam(":id", $_COOKIE[$this->cookieName]);
                $pullPage->execute();
                $currentPage = $pullPage->fetchAll();
                
                if (!empty($currentPage)) {
                    
                    $updatePage = $this->pageHandlingConnection->prepare("UPDATE sessions SET currentpage=:currentpage WHERE id=:id");
                    $updatePage->bindParam(":id", $_COOKIE[$this->cookieName]);
                    $updatePage->bindParam(":currentpage", $newPage);
                    $updatePage->execute();
                    
                }
                
            }
            
        }
        
        private function grantAccess($pageData) {
            
            if ($this->getLastPage() !== $pageData["Name"]) {
                
                $this->updateLastPage($pageData["Name"]);
                
                $this->pageHandlingLogger->make_log_entry("Access Granted", $pageData["Name"], $this->userName, ("Login Status (Required / Provided): " . ($pageData["Login Required"] ? "True" : "False") . " / " . ($this->isLoggedIn ? "True" : "False") . "\nRoles (Required / Provided): (" . implode(", ", $pageData["Access Roles"]) . ") / (" . implode(", ", $this->userAccessRoles) . ")"));
                
            }
            
            $this->setPageDetails($pageData);
            
        }
        
        private function pageNotFound($pageString) {
            
            $this->updateLastPage($this->pageTable["unknown"]["Name"]);
            
            $this->pageHandlingLogger->make_log_entry("Page Not Found", $pageString, $this->userName, ("The Page '" . $pageString . "' does not exist."));
            
            $this->setPageDetails($this->pageTable["unknown"]);
            
        }
        
        private function denyAccess($pageData) {
            
            $this->updateLastPage($this->pageTable["unknown"]["Name"]);
            
            $this->pageHandlingLogger->make_log_entry("Access Denied", $pageData["Name"], $this->userName, ("Login Status (Required / Provided): " . ($pageData["Login Required"] ? "True" : "False") . " / " . ($this->isLoggedIn ? "True" : "False") . "\nRoles (Required / Provided): (" . implode(", ", $pageData["Access Roles"]) . ") / (" . implode(", ", $this->userAccessRoles) . ")"));
            
            $this->setPageDetails($this->pageTable["unknown"]);
            
        }
        
        private function checkAccess($pageLoginRequirement, $pageRoleRequirement) {
            
            if ($pageLoginRequirement === false or $this->isLoggedIn === $pageLoginRequirement) {
                
                $meetsRequirement = false;
                
                if (in_array("Super Admin", $this->userAccessRoles)) {
                    
                    $meetsRequirement = true;
                    
                }
                
                if (empty($pageRoleRequirement)) {
                    
                    $meetsRequirement = true;
                    
                }
                else {
                
                    foreach ($this->userAccessRoles as $eachRole) {
                        
                        if (in_array($eachRole, $pageRoleRequirement)) {
                            
                            $meetsRequirement = true;
                            break;
                            
                        }
                        
                    }
                
                }
                
                return $meetsRequirement;
                
            }
            else {
                
                return false;
                
            }
            
        }
        
        private function getPageData($pageToCheck) {
            
            if ($pageToCheck === false) {
                
                $requiredRoles = $this->pageTable["home"]["Access Roles"];
                $requiredLogin = $this->pageTable["home"]["Login Required"];
                
                if ($this->checkAccess($requiredLogin, $requiredRoles)) {
                    
                    $this->grantAccess($this->pageTable["home"]);
                    
                }
                else {
                    
                    $this->denyAccess($this->pageTable["home"]);
                    
                }
                
            }
            elseif (isset($this->pageTable[$pageToCheck])) {
                
                $requiredRoles = $this->pageTable[$pageToCheck]["Access Roles"];
                $requiredLogin = $this->pageTable[$pageToCheck]["Login Required"];
                
                if ($this->checkAccess($requiredLogin, $requiredRoles)) {
                    
                    $this->grantAccess($this->pageTable[$pageToCheck]);
                    
                }
                else {
                    
                    $this->denyAccess($this->pageTable[$pageToCheck]);
                    
                }
                
            }
            else {
                
                $this->pageNotFound($pageToCheck);
                
            }
            
        }
        
    }

?>