<?php

    namespace Ridley\Interfaces;

    interface View {
        
        public function __construct(\Ridley\Core\Dependencies\DependencyManager $dependencies);
        
        public function renderContent();
        
    }

?>