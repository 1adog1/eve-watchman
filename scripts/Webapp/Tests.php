<?php

    require __DIR__ . "/../../src/core/Autoloader/Autoloader.php";
    
    $handler = new Ridley\Core\Testing\Handler(
        printResults: true, 
        detailedOutput: false
    );
    
    $handler->runSuite();
    $handler->getResults();

?>