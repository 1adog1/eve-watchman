<?php

    namespace Ridley\Views\Relays;

    class Templates {
        
        protected function mainTemplate() {
            ?>
            
            
            
            <?php
        }
        
        protected function metaTemplate() {
            ?>
            
            <title>Relay Management</title>
            <meta property="og:title" content="Eve Watchman">
            <meta property="og:description" content="The Eve Watchman App">
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