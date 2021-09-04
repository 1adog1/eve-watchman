<?php

    namespace Ridley\Views\Home;

    class Templates {
        
        protected function mainTemplate() {
            ?>
            
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="alert alert-primary text-center">
                        <h4 class="alert-heading">Welcome to App Name!</h4>
                        <hr>
                        Here's some text to inform you of what this site is supposed to do.
                    </div>
                </div>
            </div>
            
            <?php
        }
        
        protected function metaTemplate() {
            ?>
            
            <title>App Name</title>
            <meta property="og:title" content="App Name">
            <meta property="og:description" content="A Testing App">
            <meta property="og:type" content="website">
            <meta property="og:url" content="<?php echo $_SERVER["SERVER_NAME"]; ?>">
            
            <?php
        }
        
    }

    class View extends Templates implements \Ridley\Interfaces\View {
        
        public function __construct(
            private \Ridley\Core\Dependencies\DependencyManager $dependencies
        ) {
            
        }
        
        public function renderContent() {
            
            $this->mainTemplate();
            
        }
        
        public function renderMeta() {
            
            $this->metaTemplate();
            
        }
        
    }

?>