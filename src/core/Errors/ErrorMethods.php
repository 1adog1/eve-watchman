<?php

    namespace Ridley\Core\Errors;
    
    class ErrorMethods {
        
        private $methodDirectory = [];
        
        public function register(int $errorCode, string $methodName) {
            
            $this->methodDirectory[$errorCode] = $methodName;
            
        }
        
        protected function getHandler($errorCode) {
            
            if (isset($this->methodDirectory[$errorCode])) {
                
                return $this->methodDirectory[$errorCode];
                
            }
            else {
                
                return false;
                
            }
            
        }
        
    }

?>