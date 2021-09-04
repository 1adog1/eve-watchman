<?php

    namespace Ridley\Models\Admin;

    class Model implements \Ridley\Interfaces\Model {
        
        private $knownGroups;
        private $controller;
        
        public function __construct(
            private \Ridley\Core\Dependencies\DependencyManager $dependencies
        ) {
            
            $this->controller = $this->dependencies->get("Controller");
            $this->knownGroups = $this->controller->passKnownGroups();
            
        }
        
        public function getGroups() {
            
            $activeGroups = [];
            
            foreach ($this->knownGroups as $subGroupName => $subGroups) {
                
                if (!empty($subGroups)) {
                    
                    $activeGroups[$subGroupName] = $subGroups;
                    
                }
                
            }
            
            return $activeGroups;
            
        }
        
    }
?>