<?php

    function updateRefreshToken($characterID, $refreshToken) {
        
        $toUpdate = $GLOBALS['MainDatabase']->prepare("UPDATE relays SET refreshtoken=:refreshtoken WHERE id=:id");
        $toUpdate->bindParam(":refreshtoken", $refreshToken);
        $toUpdate->bindParam(":id", $characterID);
        $toUpdate->execute();
        
    }

    function runCharacterCheck($idToCheck) {
        
        if (($_SERVER["REQUEST_METHOD"] == "POST" and isset($_POST["check_character"])) and ($_POST["check_character"] == $idToCheck or $_POST["check_character"] == "All")) {
        
            require $_SERVER['DOCUMENT_ROOT'] . "/../config/config.php";
            
            $toPull = $GLOBALS['MainDatabase']->prepare("SELECT * FROM relays WHERE id = :id");
            $toPull->bindParam(':id', $idToCheck);
            $toPull->execute();
            $relayCheckData = $toPull->fetchAll();
            
            if (!empty($relayCheckData)) {
                
                foreach ($relayCheckData as $throwaway => $eachRelay) {
                
                    $CharacterJson = file_get_contents("http://esi.evetech.net/latest/characters/" . $eachRelay["id"] . "/?datasource=tranquility");
                    $CharacterData = json_decode($CharacterJson, true);
                    
                    $nameToCheck = $CharacterData["name"];
                    
                    $corpIDToCheck = $CharacterData["corporation_id"];
                    $corpNameToCheck = checkCache("Corporation", $corpIDToCheck);
                    
                    if (isset($CharacterData["alliance_id"])) {
                        $allianceIDToCheck = $CharacterData["alliance_id"];
                        $allianceNameToCheck = checkCache("Alliance", $allianceIDToCheck);
                    }
                    else {
                        $allianceIDToCheck = 0;
                        $allianceNameToCheck = "[No Alliance]";
                    }
                    
                    $currentRoles = json_decode($eachRelay["roles"], true);
                    sort($currentRoles);
                    
                    $encodedAuthorization = "Basic " . base64_encode($clientid . ":" . $clientsecret);

                    $authenticationCode = $eachRelay["refreshtoken"];

                    $curlPost = curl_init();
                    curl_setopt($curlPost, CURLOPT_URL, "https://login.eveonline.com/v2/oauth/token");
                    curl_setopt($curlPost, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curlPost, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($curlPost, CURLOPT_HTTPHEADER, ["Content-Type:application/x-www-form-urlencoded", "Authorization:" . $encodedAuthorization, "Host:login.eveonline.com"]);
                    curl_setopt($curlPost, CURLOPT_POSTFIELDS, http_build_query(["grant_type" => "refresh_token", "refresh_token" => $authenticationCode]));

                    $response = json_decode(curl_exec($curlPost), true);

                    if (isset($response["access_token"])) {
                        
                        if ($response["refresh_token"] !== $eachRelay["refreshtoken"]) {
                            
                            updateRefreshToken($eachRelay["id"], $response["refresh_token"]);
                            
                            echo "<img src='/resources/images/octicons/check.svg' class='successSVG'> Refresh Token Rotated!<br>";
                            
                        }
                        else {
                            
                            echo "<img src='/resources/images/octicons/check.svg' class='successSVG'> ESI Access Good!<br>";
                            
                        }
                        
                        $authenticationToken = $response["access_token"];
                        
                        curl_close($curlPost);
                        
                        $curlGet = curl_init();
                        curl_setopt($curlGet, CURLOPT_URL, "https://esi.evetech.net/latest/characters/" . $eachRelay["id"] . "/roles/?datasource=tranquility");
                        curl_setopt($curlGet, CURLOPT_HTTPGET, true);
                        curl_setopt($curlGet, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($curlGet, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($curlGet, CURLOPT_HTTPHEADER, ["Authorization: Bearer " . $authenticationToken]);

                        $rolesToCheck = json_decode(curl_exec($curlGet), true);
                        $rolesToCheck = $rolesToCheck["roles"];
                        sort($rolesToCheck);
                        
                        curl_close($curlGet);
                        
                    }
                        
                    else {
                        
                        $rolesToCheck = ["Unknown"];
                        
                        echo "<img src='/resources/images/octicons/alert.svg' class='alertSVG'> ESI Access Revoked!<br>";
                        
                    }
                    
                    if ($corpIDToCheck == $eachRelay["corpid"]) {
                        
                        echo "<img src='/resources/images/octicons/check.svg' class='successSVG'> Corporation Accurate!<br>";
                        
                    }
                    else {
                     
                        echo "<img src='/resources/images/octicons/issue-opened.svg' class='warnSVG'> Corporation Changed to " . $corpNameToCheck . "!<br>";
                     
                    }
                    
                    if ($allianceIDToCheck == $eachRelay["allianceid"]) {
                        
                        echo "<img src='/resources/images/octicons/check.svg' class='successSVG'> Alliance Accurate!<br>";
                        
                    }
                    else {
                     
                        echo "<img src='/resources/images/octicons/issue-opened.svg' class='warnSVG'> Alliance Changed to " . $allianceNameToCheck . "!<br>";
                     
                    }
                    
                    if ($rolesToCheck == $currentRoles) {
                        
                        echo "<img src='/resources/images/octicons/check.svg' class='successSVG'> Roles Accurate!<br>";
                        
                    }
                    else {
                     
                        echo "<img src='/resources/images/octicons/issue-opened.svg' class='warnSVG'> Roles Have Changed!<br>";
                     
                    }

                }
                
            }
            
        }
        else {
            
            echo "
            <form method='post' action='/characterManager/'>
                <input type='hidden' name='check_character' id='check_character' value=" . $idToCheck . ">
                <input type='submit' value='Run Check' class='btn btn-dark btn-small'>            
            </form>
            ";
            
        }
        
    }

?>