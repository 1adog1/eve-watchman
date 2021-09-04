<?php

    namespace Ridley\Core\Dependencies;

    class DependencyManager {
        
        private $stored = [];
        
        public function register(string $key, mixed $value) {
            
            $this->stored[$key] = $value;
            
        }
        
        public function get($dependency) {
            
            if (isset($this->stored[$dependency])) {
                
                return $this->stored[$dependency];
                
            }
            else {
                
                trigger_error("The requested dependency '" . $dependency . "' does not exist.", E_USER_ERROR);
                
            }
            
        }
        
    }

?>