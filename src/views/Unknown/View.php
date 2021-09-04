<?php

    namespace Ridley\Views\Unknown;

    class Templates {
        
        protected function mainTemplate() {
            ?>
            
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="alert alert-danger text-center">
                        <h4 class="alert-heading">Access Denied!</h4>
                        <hr>
                        You either don't have access to this page, or it doesn't exist.
                    </div>
                </div>
            </div>
            
            <?php
        }
        
        protected function metaTemplate() {
            ?>
            
            <title>App Name</title>
            <meta property="og:title" content="Access Denied">
            <meta property="og:description" content="You either don't have access to this page, or it doesn't exist.">
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