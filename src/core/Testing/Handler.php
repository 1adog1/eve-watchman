<?php

namespace Ridley\Core\Testing;

class Handler {
    
    private $registeredTests = [];
    private $results = ["Passing" => 0, "Failing" => 0];
    
    function __construct(
        private bool $printResults = true, 
        private bool $detailedOutput = false
    ) {
        
        require __DIR__ . "/../../registers/tests.php";
        
    }
    
    public function registerTest(string $testName, string $testClass) {
        
        $className = "\\Ridley\\Tests\\" . $testClass . "\\Test";
        $interfaceName = "\\Ridley\\Interfaces\\Test";
        
        if (class_exists($className)) {
            
            if (is_subclass_of($className, $interfaceName)) {
        
                $this->registeredTests[] = [
                    "Name" => $testName,
                    "Class" => $testClass
                ];
                
            }
            else {
                
                fwrite(STDOUT, "\n[ERROR] The test with name " . $testName . " does not implement the required interface!");
                
            }
        
        }
        else {
            
            fwrite(STDOUT, "\n[ERROR] The test with name " . $testName . " does not exist!");
            
        }
        
    }
    
    public function runSuite() {
        
        foreach ($this->registeredTests as $eachTest) {
            
            $className = "\\Ridley\\Tests\\" . $eachTest["Class"] . "\\Test";
            $testObject = new $className($this->printResults, $this->detailedOutput);
            
            if ($testObject->runTest()) {
                
                $this->results["Passing"]++;
                
            }
            else {
                
                $this->results["Failing"]++;
                
            }
            
        }
        
    }
    
    public function getResults() {
        
        $totalTests = count($this->registeredTests);
        
        fwrite(STDOUT, "\n
Test Results
------------
Passing: " . $this->results["Passing"] . " / " . $totalTests . "
Failing: " . $this->results["Failing"] . " / " . $totalTests . "
        ");
        
    }
    
}

?>