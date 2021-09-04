<?php

    namespace Ridley\Core\Site;

    class SiteCore {
        
        private $navArray;
        private $pageLink;
        private $pageCode;
        private $loginStatus;
        private $characterStats;
        private $hasModel;
        private $hasController;
        private $hasAPI;
        
        private $pageAPI;
        private $pageController;
        private $pageModel;
        private $pageView;

        function __construct(
            private $dependencies
        ) {
            
            $this->navArray = $this->dependencies->get("Nav Links");
            $this->pageLink = $this->dependencies->get("Page Link");
            $this->pageCode = $this->dependencies->get("Page Code");
            $this->csrfToken = $this->dependencies->get("CSRF Token");
            $this->loginStatus = $this->dependencies->get("Login Status");
            $this->characterStats = $this->dependencies->get("Character Stats");
            
            $this->hasModel = $this->dependencies->get("Page Has Model");
            $this->hasController = $this->dependencies->get("Page Has Controller");
            $this->hasAPI = $this->dependencies->get("Page Has API");
            
            
            if (isset($_GET["core_action"]) and $_GET["core_action"] == "api") {
                
                $this->loadAPI();
                
            }
            else {
                
                $this->loadPage();
                
            }
            
        }
        
        private function loadAPI() {
            
            if ($this->hasAPI === true) {
                
                if (isset($_SERVER["HTTP_CSRF_TOKEN"]) and $_SERVER["HTTP_CSRF_TOKEN"] === $this->csrfToken) {
                
                    $apiClassName = "\\Ridley\\Apis\\" . $this->pageCode . "\\Api";
                    $this->pageAPI = new $apiClassName($this->dependencies);
                    
                }
                else {
                    
                    header($_SERVER["SERVER_PROTOCOL"] . " 403 Forbidden");
                    
                }
            
            }
            else {
                
                header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
                
            }
            
        }
        
        private function loadPage() {
            
            if ($this->hasController === true) {
                
                $controllerClassName = "\\Ridley\\Controllers\\" . $this->pageCode . "\\Controller";
                $this->pageController = new $controllerClassName($this->dependencies);
                
                $this->dependencies->register("Controller", $this->pageController);
            
            }
            
            if ($this->hasModel === true) {
                
                $modelClassName = "\\Ridley\\Models\\" . $this->pageCode . "\\Model";
                $this->pageModel = new $modelClassName($this->dependencies);
                
                $this->dependencies->register("Model", $this->pageModel);
            
            }
            
            $pageClassName = "\\Ridley\\Views\\" . $this->pageCode . "\\View";
            $this->pageView = new $pageClassName($this->dependencies);
            
            require __DIR__ . "/../Site/SiteBase.php";
            
        }
        
        public function renderCSRFToken() {
            
            ?>
            
            <meta name="csrftoken" content="<?php echo $this->csrfToken ?>">
            
            <?php
            
        }
        
        public function renderAuth() {
            
            if ($this->loginStatus) {
                
                ?>
                
                <li class="nav-item align-self-xl-center me-4 mb-1">
                    <span class="navbar-text text-break h5 font-weight-bold"><?php echo $this->characterStats["Character Name"] ?></span>
                </li>
                
                <?php
                
                if (isset($this->characterStats["Alliance Name"])) {
                    
                    ?>
                    
                    <li class="nav-item align-self-xl-center me-4 mb-1">
                        <span class="navbar-text font-weight-bold"><?php echo $this->characterStats["Alliance Name"] ?></span>
                        <br>
                        <span class="navbar-text font-weight-bold"><?php echo $this->characterStats["Corporation Name"] ?></span>
                    </li>
                    
                    <?php
                
                }
                else {
                    
                    ?>
                    
                    <li class="nav-item align-self-xl-center me-4 mb-1">
                        <span class="navbar-text font-weight-bold"><?php echo $this->characterStats["Corporation Name"] ?></span>
                    </li>
                    
                    <?php
                    
                }
                
                ?>
                
                <li class="nav-item align-self-xl-center me-4 mb-1">
                    <a href="<?php echo $this->pageLink ?>/?core_action=logout" class="btn btn-block btn-outline-danger" role="button">Logout</a>
                </li>
                
                <?php
                
            }
            else {
                
                ?>
                
                 <li class="nav-item align-self-xl-center me-4 mb-1">
                    <a href="<?php echo $this->pageLink ?>/?core_action=login">
                        <img class="login-button" src="/resources/images/sso_image.png">
                    </a>
                 </li>
                 
                <?php
                
            }
            
        }
        
        public function renderNav() {
            
            foreach ($this->navArray as $eachLink) {
                if ($eachLink["Code"] == $this->pageCode) {
                    
                    ?>
                    
                    <li class="nav-item mb-1">
                        <a class="nav-link active" href="/<?php echo $eachLink["Link"] ?>/"><?php echo $eachLink["Name"] ?></a>
                    </li>
                    
                    <?php
                    
                }
                else {
                    
                    ?>
                    
                    <li class="nav-item mb-1">
                        <a class="nav-link" href="/<?php echo $eachLink["Link"] ?>/"><?php echo $eachLink["Name"] ?></a>
                    </li>
                    
                    <?php
                    
                }

            }
            
        }
        
        public function renderMeta() {
            
            if (method_exists($this->pageView, "renderMeta")) {
                
                $this->pageView->renderMeta();
                
            }
            
        }
        
        public function renderStyle() {
            
            if (method_exists($this->pageView, "renderStyle")) {
                
                $this->pageView->renderStyle();
                
            }
            
        }
        
        public function renderContent() {
            
            if (method_exists($this->pageView, "renderContent")) {
                
                $this->pageView->renderContent();
                
            }
            
        }
        
    }

?>