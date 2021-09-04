<?php

    namespace Ridley\Views\Home;

    class Templates {
        
        protected function mainTemplate() {
            ?>
            
            <?php $this->relayCheckTemplate(); ?>
            
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="alert alert-primary text-center">
                        <h4 class="alert-heading">Welcome to Eve Watchman!</h4>
                        <hr>
                        <p>Eve Watchman is an app designed for relaying Eve Online notifications to Discord or Slack.</p>
                        <p>To configure the application, use the login on the navbar. To add a relay character, use the login below: </p>
                        <hr>
                        Login a Relay Character: 
                        <a href="home/?action=login">
                            <img class="login-button" src="/resources/images/sso_image_dark.png">
                        </a>
                    </div>
                </div>
            </div>
            
            <?php
        }
        
        protected function relayCheckTemplate() {
            
            if ($this->loginStatus) {
                
                $hasRelayCharacter = $this->model->checkIfRelay();
                
                $statusColor = ($hasRelayCharacter) ? "success" : "danger";
                $statusText = ($hasRelayCharacter) ? "This character is logged in as a relay character." : "This character is not logged in as a relay character.";
                
                ?>
                
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="alert alert-<?php echo $statusColor; ?> text-center">
                            <h4 class="alert-heading"><?php echo $statusText; ?></h4>
                        </div>
                    </div>
                </div>
                
                <?php
                
            }
            
        }
        
        protected function metaTemplate() {
            ?>
            
            <title>Eve Watchman</title>
            <meta property="og:title" content="Eve Watchman">
            <meta property="og:description" content="The Eve Watchman App">
            <meta property="og:type" content="website">
            <meta property="og:url" content="<?php echo $_SERVER["SERVER_NAME"]; ?>">
            
            <?php
        }
        
    }

    class View extends Templates implements \Ridley\Interfaces\View {
        
        protected $controller;
        protected $model;
        protected $loginStatus;
        
        public function __construct(
            private \Ridley\Core\Dependencies\DependencyManager $dependencies
        ) {
            
            $this->controller = $this->dependencies->get("Controller");
            $this->model = $this->dependencies->get("Model");
            $this->loginStatus = $this->dependencies->get("Login Status");
            
        }
        
        public function renderContent() {
            
            $this->mainTemplate();
            
        }
        
        public function renderMeta() {
            
            $this->metaTemplate();
            
        }
        
    }

?>