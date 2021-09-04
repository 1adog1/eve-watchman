<?php

    namespace Ridley\Core\Errors;
    
    class ErrorHandler extends ErrorMethods {
        
        private $knownErrors = [];
        
        function __construct(
            private $errorLogger
        ) {
            
            ini_set("display_errors", 0);
            error_reporting(E_ALL);
            
            set_error_handler([$this, "determineError"]);
            register_shutdown_function([$this, "shutdownHandler"]);
            
        }
        
        public function determineError($errorCode, $errorDetails, $errorFile, $errorLine) {
            
            $returnValue = false;
            
            $this->logError($errorCode, $errorDetails, $errorFile, $errorLine);
            
            $handleMethod = $this->getHandler($errorCode);
            
            if ($handleMethod !== false) {
                
                $returnValue = $this->$handleMethod($errorCode, $errorDetails, $errorFile, $errorLine);
                
            }
            
            $this->knownErrors[] = hash("sha256", ($errorCode . $errorDetails . $errorFile . $errorLine));
            
            return $returnValue;
            
        }
        
        public function logError($errorCode, $errorDetails, $errorFile, $errorLine) {
            
            $errorKeys = [
                E_ERROR => "Fatal Error",
                E_WARNING => "Warning",
                E_PARSE => "Parsing Error",
                E_NOTICE => "Notice",
                E_CORE_ERROR => "Core Error",
                E_CORE_WARNING => "Core Warning",
                E_COMPILE_ERROR => "Compile Error",
                E_COMPILE_WARNING => "Compile Warning",
                E_USER_ERROR => "User Error",
                E_USER_WARNING => "User Warning",
                E_USER_NOTICE => "User Notice",
                E_RECOVERABLE_ERROR => "Recoverable Error",
                E_DEPRECATED => "Deprecated Code Error",
                E_USER_DEPRECATED => "User Deprecated Code Error"
            ];
            
            if (isset($errorKeys[$errorCode])) {
                
                $errorType = $errorKeys[$errorCode];
                
            }
            else {
                
                $errorType = "Unknown Error " . $errorCode;
                
            }
            
            $escapedErrorDetails = htmlspecialchars($errorDetails);
            
            if (isset($errorFile)) {
                
                $escapedErrorDetails .= ("\nFile: " . htmlspecialchars($errorFile));
                $errorPage = htmlspecialchars($errorFile);

            }
            else {
                
                $errorPage = null;
                
            }
            
            if (isset($errorLine)) {
                
                $escapedErrorDetails .= ("\nLine: " . htmlspecialchars($errorLine));
                
            }
            
            $this->errorLogger->make_log_entry($errorType, $errorPage, logDetails: $escapedErrorDetails);
            
        }
        
        public function shutdownHandler() {
            
            $lastError = error_get_last();
            
            if (
                isset($lastError) 
                and !in_array(hash("sha256", ($lastError["type"] . $lastError["message"] . $lastError["file"] . $lastError["line"])), $this->knownErrors)
            ) {
                
                $this->logError($lastError["type"], $lastError["message"], $lastError["file"], $lastError["line"]);
                
            }
            
        }
        
    }

?>