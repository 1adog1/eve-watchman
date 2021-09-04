<?php

    namespace Ridley\Objects\ESI;

    class Handler extends Methods {
        
        protected $esiURL = "https://esi.evetech.net/";
        protected $methodList = [];
        
        function __construct(
            protected $databaseConnection, 
            protected $accessToken = null
        ) {
            
            require __DIR__ . "/../../registers/esiMethods.php";
            
        }
        
        public function call(string $endpoint, mixed ...$arguments) {
            
            if (isset($this->methodList[$endpoint])) {
                
                $method = $this->methodList[$endpoint]["Name"];
                $requiredArguments = $this->methodList[$endpoint]["Required Arguments"];
                
                if (
                    empty(
                        array_diff_key(
                            array_flip($requiredArguments), 
                            $arguments
                        )
                    )
                ) {
                
                    return $this->$method($arguments);
                    
                }
                else {
                    
                    trigger_error("Failed to pass required arguments for the " . $endpoint . " endpoint.", E_USER_ERROR);
                    
                }
                
            }
            else {
                
                trigger_error("The requested endpoint " . $endpoint . " does not have a registered method.", E_USER_ERROR);
                
            }
            
        }
        
        protected function register(
            string $endpoint, 
            string $method, 
            array $requiredArguments
        ) {
            
            $this->methodList[$endpoint] = ["Name" => $method, "Required Arguments" => $requiredArguments];
            
        }
        
    }

?>