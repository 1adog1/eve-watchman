<?php

    namespace Ridley\Objects\ESI;

    class Base {
        
        private $defaultSuccessCodes = ["200", "204"];
        
        private function hashRequest(string $url, string $method, ?array $payload, ?string $accessToken) {
            
            $hashingArray = [
                "URL" => $url, 
                "Method" => $method, 
                "Payload" => $payload, 
                "Authentication" => $accessToken
            ];
            
            return hash("sha256", json_encode($hashingArray, JSON_UNESCAPED_SLASHES));
            
        }
        
        private function cleanupCache() {
            
            $currentTime = time();
            
            $deleteCacheEntries = $this->databaseConnection->prepare("DELETE FROM esicache WHERE expiration <= :current_time");
            $deleteCacheEntries->bindParam(":current_time", $currentTime);
            $deleteCacheEntries->execute();
            
        }
        
        private function checkCache(string $endpoint, string $hash) {
            
            $cacheRequest = $this->databaseConnection->prepare("SELECT response FROM esicache WHERE endpoint=:endpoint AND hash=:hash AND expiration > :currenttime");
            $cacheRequest->bindParam(":endpoint", $endpoint);
            $cacheRequest->bindParam(":hash", $hash);
            $cacheRequest->bindValue(":currenttime", time());
            
            if ($cacheRequest->execute()) {
                
                $cacheData = $cacheRequest->fetch();
                
                if (!empty($cacheData)) {
                    
                    return json_decode($cacheData["response"], true);
                    
                }
                else {
                    
                    return false;
                    
                }
                
            }
            else {
                
                trigger_error("Failed to query ESI Cache.", E_USER_ERROR);
                
            }
            
        }
        
        private function populateCache(string $endpoint, string $hash, string $response, int $expires) {
            
            $insertSession = $this->databaseConnection->prepare("INSERT INTO esicache (endpoint, hash, expiration, response) VALUES (:endpoint, :hash, :expiration, :response)");
            $insertSession->bindParam(":endpoint", $endpoint);
            $insertSession->bindParam(":hash", $hash);
            $insertSession->bindParam(":expiration", $expires);
            $insertSession->bindParam(":response", $response);

            $insertSession->execute();
            
        }
        
        private function buildContext(string $method, ?array $payload, ?string $accessToken) {
            
            $context = [
                "http" => [
                    "ignore_errors" => true, 
                    "header" => [
                        "accept: application/json"
                    ], 
                    "method" => $method
                ]
            ];
            
            if (!empty($payload)) {
                $context["http"]["header"][] = "Content-Type: application/json";
                $context["http"]["content"] = json_encode($payload);
            }
            if (!empty($accessToken)) {
                $context["http"]["header"][] = ("Authorization: Bearer " . $accessToken);
            }
            
            return $context;
            
        }
        
        private function parseHeaders(array $headerList) {
            
            $parsedHeaders = ["Status Code" => $headerList[0], "Headers" => []];
            
            foreach (array_slice($headerList, 1) as $eachHeader) {
                
                $splitHeader = explode(":", $eachHeader);
                $headerTitle = $splitHeader[0];
                $headerData = implode(":", array_slice($splitHeader, 1));
                
                $parsedHeaders["Headers"][$headerTitle] = $headerData;
                
            }
            
            return $parsedHeaders;
            
        }
        
        private function checkForSuccess($responseCode, $customSuccessCodes) {
            
            $successCodes = array_unique(array_merge($this->defaultSuccessCodes, $customSuccessCodes));
            
            foreach ($successCodes as $eachCode) {
                
                if (str_contains($responseCode, $eachCode)) {
                    
                    return true;
                    
                }
                
            }
            
            return false;
            
        }
        
        protected function makeRequest(
            string $endpoint, 
            string $url, 
            string $method = "GET", 
            array $payload = null, 
            string $accessToken = null, 
            bool $expectResponse = true, 
            array $successCodes = [], 
            int $cacheTime = 0, 
            int $retries = 0
        ) {
            
            $responseData = ["Success" => false, "Data" => null];
            
            $this->cleanupCache();
            
            $cacheCheck = $this->checkCache(
                $endpoint, 
                $this->hashRequest($url, $method, $payload, $accessToken)
            );
            
            if ($cacheCheck !== false) {
                
                $responseData["Success"] = true;
                $responseData["Data"] = $cacheCheck;
                
                return $responseData;
                
            }
            else {
                
                for ($remainingRetries = $retries; $remainingRetries >= 0; $remainingRetries--) {
                    
                    $requestContext = stream_context_create(
                        $this->buildContext($method, $payload, $accessToken)
                    );
                    
                    $request = file_get_contents(
                        filename: $url, 
                        context: $requestContext
                    );
                    
                    $responseHeaders = $this->parseHeaders($http_response_header);
                    
                    if ($this->checkForSuccess($responseHeaders["Status Code"], $successCodes)) {
                        
                        $responseData["Success"] = true;
                        
                        if ($expectResponse) {
                            
                            $responseData["Data"] = json_decode($request, true);
                            
                            if (isset($responseHeaders["Headers"]["Expires"])) {
                                
                                $expiry = strtotime($responseHeaders["Headers"]["Expires"]);
                                
                            }
                            else {
                                
                                $expiry = time() + $cacheTime;
                                
                            }
                            
                            $this->populateCache(
                                $endpoint, 
                                $this->hashRequest($url, $method, $payload, $accessToken), 
                                $request, 
                                $expiry
                            );
                            
                        }
                        
                        return $responseData;
                        
                    }
                    elseif ($remainingRetries <= 0) {
                        
                        $responseData["Success"] = false;
                        
                        if ($expectResponse) {
                            
                            $responseData["Data"] = json_decode($request, true);
                            
                        }
                        
                        return $responseData;
                        
                    }
                    
                }
                
            }
            
        }
        
    }

?>