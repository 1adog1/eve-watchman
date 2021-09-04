<?php

    namespace Ridley\Interfaces;

    interface Test {
        
        public function __construct(bool $printResult, bool $detailedOutput);
        
        public function runTest(): bool;
        
    }

?>