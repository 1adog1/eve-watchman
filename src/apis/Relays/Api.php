<?php

    namespace Ridley\Apis\Relays;

    class Api implements \Ridley\Interfaces\Api {
        
        private $databaseConnection;
        
        public function __construct(
            private \Ridley\Core\Dependencies\DependencyManager $dependencies
        ) {
            
            $this->databaseConnection = $this->dependencies->get("Database");
            
        }
        
    }

?>