<?php

    /*
        
        This is the Authorization Base Class, providing the necessary logic for Eve's OAuth 2.0 SSO Process.
        
    */
    
    namespace Ridley\Core\Authorization\Base;

    class AuthBase {
        
        protected $esiHandler;
        
        public function __construct(
            protected $authorizationLogger, 
            protected $authorizationConnection, 
            protected $authorizationVariables
        ) {
            
            $this->esiHandler = new \Ridley\Objects\ESI\Handler($authorizationConnection);
            
            $this->cleanupLogins();
            
        }
        
        public function login($loginType, $loginScopes) {
            
            $loginBytes = random_bytes(16);
            $loginState = bin2hex($loginBytes);
            
            $this->setLoginData($loginState, $loginType, $loginScopes);
            
            $loginURL = "https://login.eveonline.com/v2/oauth/authorize/?response_type=code&redirect_uri=" . urlencode($this->authorizationVariables["Client Redirect"]) . "&client_id=" . urlencode($this->authorizationVariables["Client ID"]) . "&scope=" . urlencode($loginScopes) . "&state=" . urlencode($loginState);
            
            header("Location: " . $loginURL);
            die();
            
        }
        
        
        public function getAccessToken($loginType, $characterID) {
            
            $this->refreshAccessToken($loginType, $characterID);
            
            $getToken = $this->authorizationConnection->prepare("SELECT accesstoken FROM refreshtokens WHERE type=:type AND characterid = :characterid");
            $getToken->bindParam(":type", $loginType);
            $getToken->bindParam(":characterid", $characterID);
            $getToken->execute();
            $tokenResults = $getToken->fetch(\PDO::FETCH_ASSOC);
            
            if (!empty($tokenResults)) {
                
                return $tokenResults["accesstoken"];
                
            }
            else {
                
                return false;
                
            }
            
        }
        
        protected function cleanupLogins() {
            
            $currentTime = time();
            
            $deleteSessions = $this->authorizationConnection->prepare("DELETE FROM logins WHERE expiration < :current_time");
            $deleteSessions->bindParam(":current_time", $currentTime);
            $deleteSessions->execute();
            
        }
        
        protected function setLoginData($state, $type, $scopes) {
            
            $loginExpiration = time() + 300;
            
            $pullLogin = $this->authorizationConnection->prepare("INSERT INTO logins (type, state, scopes, expiration) VALUES (:type, :state, :scopes, :expiration)");
            $pullLogin->bindParam(":type", $type);
            $pullLogin->bindParam(":state", $state);
            $pullLogin->bindParam(":scopes", $scopes);
            $pullLogin->bindParam(":expiration", $loginExpiration);
            $pullLogin->execute();
            
        }
        
        protected function getLoginData($state) {
            
            $pullLogin = $this->authorizationConnection->prepare("SELECT * FROM logins WHERE state=:state");
            $pullLogin->bindParam(":state", $state);
            $pullLogin->execute();
            $loginData = $pullLogin->fetch(\PDO::FETCH_ASSOC);
            
            if (!empty($loginData)) {
                
                return ["State" => $loginData["state"], "Type" => $loginData["type"], "Scopes" => $loginData["scopes"]];
                
            }
            else {
                
                return false;
                
            }
            
        }
        
        protected function loginSuccess($characterID) {
            
            $returnURL = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
            
            header("Location: " . $returnURL);
            die();
            
        }
        
        protected function loginFailure($failReason) {
            
            $this->authorizationLogger->make_log_entry("Login Failure", "Authorization Handler", "Unknown Actor", $failReason);
            
            $returnURL = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
            
            header("Location: " . $returnURL);
            die();
            
        }
        
        private function confirmCharacterID($characterID, $characterName) {
            
            $namesCall = $this->esiHandler->call(endpoint: "/universe/names/", ids: [$characterID], retries: 1);
            
            if ($namesCall["Success"]) {
                
                foreach ($namesCall["Data"] as $eachName) {
                    
                    if ($eachName["category"] == "character" and $eachName["name"] == $characterName) {
                        
                        return true;
                        
                    }
                    else {
                        
                        return false;
                        
                    }
                    
                }
            
            }
            else {
                
                return false;
                
            }
            
        }
        
        private function grantAccessToken($grantType, $codeOrToken) {
            
            $tokenURL = "https://login.eveonline.com/v2/oauth/token";
            
            $tokenAuthorization = "Basic " . base64_encode($this->authorizationVariables["Client ID"] . ":" . $this->authorizationVariables["Client Secret"]);
            
            $tokenContent = [
                "grant_type" => $grantType
            ];
            
            if ($grantType == "authorization_code") {
                
                $tokenContent["code"] = $codeOrToken;
                
            }
            elseif ($grantType == "refresh_token") {
                
                $tokenContent["refresh_token"] = $codeOrToken;
                
            }
            
            $tokenOptions = [
                "http" => [
                    "header" => [
                        ("Authorization: " . $tokenAuthorization),
                        "Content-Type: application/x-www-form-urlencoded",
                        "Host: login.eveonline.com"
                    ],
                    "method" => "POST",
                    "content" => http_build_query($tokenContent)
                ]
            ];
            
            $tokenContext = stream_context_create($tokenOptions);
            
            $tokenResponse = @file_get_contents($tokenURL, false, $tokenContext);
            
            $tokenStatusData = $http_response_header[0];
            
            if (str_contains($tokenStatusData, "200")) {
            
                $tokenResponseData = json_decode($tokenResponse, true);
                
                if (isset($tokenResponseData["access_token"])) {
                    
                    return $tokenResponseData;
                    
                }
                else {
                    
                    return false;
                    
                }
            
            }
            else {
                
                return false;
                
            }
            
        }
        
        protected function receiveCallback() {
            
            if (isset($_GET["state"])) {
                
                $loginData = $this->getLoginData($_GET["state"]);
                
                if ($loginData !== false and $loginData["State"] === $_GET["state"]) {
                
                    if (isset($_GET["code"])) {
                        
                        $tokenData = $this->grantAccessToken("authorization_code", $_GET["code"]);
                        
                        if ($tokenData !== false) {
                            
                            $accessToken = $tokenData["access_token"];
                        
                            $accessArray = explode(".", $accessToken);
                            $accessHeader = json_decode(base64_decode($accessArray[0]), true);
                            $accessPayload = json_decode(base64_decode($accessArray[1]), true);
                            $accessSignature = $accessArray[2];
                            
                            if (isset($accessPayload["scp"])) {
                                
                                if (is_array($accessPayload["scp"])) {
                                    
                                    $accessScopes = $accessPayload["scp"];
                                    
                                }
                                else {
                                    
                                    $accessScopes = explode(" ", $accessPayload["scp"]);
                                    
                                }
                                
                            }
                            else {
                                
                                $accessScopes = explode(" ", "");
                                
                            }
                            
                            $expectedScopes = explode(" ", $loginData["Scopes"]);
                            
                            sort($accessScopes);
                            sort($expectedScopes);
                            
                            if ($accessScopes === $expectedScopes) {
                            
                                $accessSubject = explode(":", $accessPayload["sub"]);
                                $accessCharacterID = $accessSubject[2];
                                
                                if ($this->confirmCharacterID($accessCharacterID, $accessPayload["name"]) === true) {
                                    
                                    if (isset($tokenData["refresh_token"])) {
                                        
                                        $this->storeRefreshToken($loginData["Type"], $accessCharacterID, $tokenData, $accessScopes, $accessPayload["exp"]);
                                        
                                    }
                                    
                                    if ($loginData["Type"] === "Default") {
                                    
                                        $this->loginSuccess($accessCharacterID);
                                    
                                    }
                                    
                                }
                                else {
                                    
                                    $this->loginFailure("Character ID does not resolve or does not correspond with the expected Character Name.");
                                    
                                }
                            
                            }
                            else {
                                
                                $this->loginFailure("Scopes do not match those expected.");
                                
                            }
                        
                        }
                        else {
                            
                            $this->loginFailure("Code failed to translate to access code.");
                            
                        }
                        
                    }
                    else {
                        
                        $this->loginFailure("Code was not returned to callback.");
                        
                    }
                    
                }
                else {
                    
                    $this->loginFailure("State does not match.");
                    
                }
                
            }
            else {
                
                $this->loginFailure("State was not given.");
                
            }
            
        }
        
        private function storeRefreshToken($loginType, $characterID, $tokenData, $tokenScopes, $tokenExpiration) {
            
            $checkToken = $this->authorizationConnection->prepare("SELECT * FROM refreshtokens WHERE type=:type AND characterid = :characterid");
            $checkToken->bindParam(":type", $loginType);
            $checkToken->bindParam(":characterid", $characterID);
            $checkToken->execute();
            $tokenResults = $checkToken->fetch(\PDO::FETCH_ASSOC);
            
            if (empty($tokenResults)) {
                
                $insertToken = $this->authorizationConnection->prepare("INSERT INTO refreshtokens (type, characterid, scopes, refreshtoken, accesstoken, recheck) VALUES (:type, :characterid, :scopes, :refreshtoken, :accesstoken, :recheck)");
                $insertToken->bindParam(":type", $loginType);
                $insertToken->bindParam(":characterid", $characterID);
                $insertToken->bindValue(":scopes", json_encode($tokenScopes));
                $insertToken->bindParam(":refreshtoken", $tokenData["refresh_token"]);
                $insertToken->bindParam(":accesstoken", $tokenData["access_token"]);
                $insertToken->bindParam(":recheck", $tokenExpiration);
                $insertToken->execute();
                
            }
            else {
                
                $updateToken = $this->authorizationConnection->prepare("UPDATE refreshtokens SET scopes=:scopes, refreshtoken=:refreshtoken, accesstoken=:accesstoken, recheck=:recheck WHERE type=:type AND characterid=:characterid");
                $updateToken->bindParam(":type", $loginType);
                $updateToken->bindParam(":characterid", $characterID);
                $updateToken->bindValue(":scopes", json_encode($tokenScopes));
                $updateToken->bindParam(":refreshtoken", $tokenData["refresh_token"]);
                $updateToken->bindParam(":accesstoken", $tokenData["access_token"]);
                $updateToken->bindParam(":recheck", $tokenExpiration);
                $updateToken->execute();
                
            }
            
        }
        
        private function refreshAccessToken($loginType, $characterID) {
            
            $recheckTime = time() + 15;
            
            $checkToken = $this->authorizationConnection->prepare("SELECT DISTINCT * FROM refreshtokens WHERE type=:type AND characterid = :characterid AND recheck <= :currenttime");
            $checkToken->bindParam(":type", $loginType);
            $checkToken->bindParam(":characterid", $characterID);
            $checkToken->bindParam(":currenttime", $recheckTime);
            
            if ($checkToken->execute()) {
                
                while ($tokenResults = $checkToken->fetch(\PDO::FETCH_ASSOC)) {
                    
                    $tokenData = $this->grantAccessToken("refresh_token", $tokenResults["refreshtoken"]);
                    
                    if ($tokenData !== false) {
                        
                        $accessToken = $tokenData["access_token"];
                    
                        $accessArray = explode(".", $accessToken);
                        $accessHeader = json_decode(base64_decode($accessArray[0]), true);
                        $accessPayload = json_decode(base64_decode($accessArray[1]), true);
                        $accessSignature = $accessArray[2];
                        
                        if (isset($accessPayload["scp"])) {
                            
                            if (is_array($accessPayload["scp"])) {
                                
                                $accessScopes = $accessPayload["scp"];
                                
                            }
                            else {
                                
                                $accessScopes = explode(" ", $accessPayload["scp"]);
                                
                            }
                            
                        }
                        else {
                            
                            $accessScopes = explode(" ", "");
                            
                        }
                        
                        $this->storeRefreshToken($loginType, $characterID, $tokenData, $accessScopes, $accessPayload["exp"]);
                        
                    }
                    else {
                        
                        trigger_error("An attempt to refresh an access token failed.", E_USER_ERROR);
                        
                    }
                    
                }
                
            }
            else {
                
                trigger_error("Failed to query database while trying to refresh a token.", E_USER_ERROR);
                
            }
            
        }
        
    }

?>