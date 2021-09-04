<?php

    spl_autoload_register(function ($class) {
        
        $knownPrefixes = [
            "Ridley\\Core\\" => (__DIR__ . "/../"),
            "Ridley\\Apis\\" => (__DIR__ . "/../../apis/"),
            "Ridley\\Controllers\\" => (__DIR__ . "/../../controllers/"),
            "Ridley\\Models\\" => (__DIR__ . "/../../models/"),
            "Ridley\\Views\\" => (__DIR__ . "/../../views/"),
            "Ridley\\Objects\\" => (__DIR__ . "/../../objects/"),
            "Ridley\\Interfaces\\" => (__DIR__ . "/../../interfaces/"),
            "Ridley\\Tests\\" => (__DIR__ . "/../../tests/")
        ];
        
        $prefixFound = false;
        
        foreach ($knownPrefixes as $eachPrefix => $eachDirectory) {
            
            $len = strlen($eachPrefix);
            if (str_starts_with($class, $eachPrefix)) {
                
                $prefix = $eachPrefix;
                $directory = $eachDirectory;
                $relativeClass = substr($class, $len);
                
                $fileToImport = $directory . str_replace("\\", "/", $relativeClass) . ".php";
                
                if (file_exists($fileToImport)) {
                    
                    require $fileToImport;
                    
                }
                else {
                    
                    error_log("Couldn't find file " . $fileToImport . " for class " . $class . ".");
                    
                }
                
                $prefixFound = true;
                
                break;
                
            }
            
        }
        
        if ($prefixFound === false) {
            
            error_log("Failed to resolve a prefix for class " . $class . ".");
            
            return;
            
        }

    });

?>