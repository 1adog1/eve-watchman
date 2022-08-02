<?php

    namespace Ridley\Apis\Characters;

    class Api implements \Ridley\Interfaces\Api {

        private $databaseConnection;
        private $logger;
        private $accessRoles;
        private $characterStats;
        private $esiHandler;

        public function __construct(
            private \Ridley\Core\Dependencies\DependencyManager $dependencies
        ) {

            $this->databaseConnection = $this->dependencies->get("Database");
            $this->logger = $this->dependencies->get("Logging");
            $this->accessRoles = $this->dependencies->get("Access Roles");
            $this->characterStats = $this->dependencies->get("Character Stats");
            $this->esiHandler = new \Ridley\Objects\ESI\Handler($this->databaseConnection);

            if (isset($_POST["Action"])) {

                if (
                    $_POST["Action"] == "Get_Corp_Info"
                    and isset($_POST["ID"])
                ){

                    $this->getCorpInfo($_POST["ID"]);

                }
                elseif (
                    $_POST["Action"] == "Get_Character_Info"
                    and isset($_POST["ID"])
                ){

                    $this->getCharacterInfo($_POST["ID"]);

                }
                elseif (
                    $_POST["Action"] == "Delete_Character"
                    and isset($_POST["ID"])
                ){

                    $this->deleteCharacter($_POST["ID"]);

                }
                else {

                    header($_SERVER["SERVER_PROTOCOL"] . " 400 Bad Request");
                    trigger_error("No valid combination of action and required secondary arguments was received.", E_USER_ERROR);

                }

            }
            else {

                header($_SERVER["SERVER_PROTOCOL"] . " 400 Bad Request");
                trigger_error("Request is missing the action argument.", E_USER_ERROR);

            }

        }

        private function getCharacterInfo($incomingID) {

            $success = false;

            $characterQuery = $this->databaseConnection->prepare("SELECT * FROM relaycharacters WHERE id=:id;");
            $characterQuery->bindParam(":id", $incomingID);

            $characterQuery->execute();
            $characterQueryResults = $characterQuery->fetchAll();

            foreach ($characterQueryResults as $eachCharacter) {

                if (
                    $this->verifyAccess(
                        $eachCharacter["corporationid"],
                        $eachCharacter["allianceid"]
                    )
                ) {

                    $exportCharacter = new \Ridley\Objects\Characters\RelayCharacter(
                        (int)$eachCharacter["id"],
                        $eachCharacter["name"],
                        $eachCharacter["status"],
                        (int)$eachCharacter["corporationid"],
                        $eachCharacter["corporationname"],
                        (int)$eachCharacter["allianceid"],
                        $eachCharacter["alliancename"],
                        json_decode($eachCharacter["roles"])
                    );

                    echo json_encode($exportCharacter->exportComplex());
                    $success = true;

                }

            }

            if (!$success) {

                header($_SERVER["SERVER_PROTOCOL"] . " 400 Bad Request");

            }

        }

        private function getCorpInfo($incomingID) {

            $characterList = [];

            $corpData = new \Ridley\Objects\Relays\RelayCorp(
                $incomingID,
                $this->accessRoles,
                $this->characterStats,
                $this->databaseConnection,
                true
            );

            $characterData = $corpData->getCharacters();

            if ($characterData !== false) {

                foreach ($characterData as $eachCharacter) {

                    $characterList[] = $eachCharacter->exportSimple();

                }

            }

            echo json_encode($characterList);

        }

        private function deleteCharacter($incomingID) {

            $success = false;

            $deletionQuery = $this->databaseConnection->prepare("SELECT * FROM relaycharacters WHERE id=:id;");
            $deletionQuery->bindParam(":id", $incomingID);

            $deletionQuery->execute();
            $deletionQueryResults = $deletionQuery->fetchAll();

            foreach ($deletionQueryResults as $eachCharacter) {

                if (
                    $this->verifyAccess(
                        $eachCharacter["corporationid"],
                        $eachCharacter["allianceid"]
                    )
                ) {

                    $deletionRequest = $this->databaseConnection->prepare("DELETE FROM relaycharacters WHERE id=:id;");
                    $deletionRequest->bindParam(":id", $incomingID);
                    $deletionRequest->execute();

                    $this->logger->make_log_entry(
                        logType: "Relay Character Deleted",
                        logDetails: $eachCharacter["name"] . " (" . $eachCharacter["corporationname"] . ") [" . $eachCharacter["alliancename"] . "] with status " . $eachCharacter["status"] . " deleted. \nID: " . $eachCharacter["id"]
                    );

                    echo json_encode(["Success" => true]);
                    $success = true;

                }

            }

            if (!$success) {

                header($_SERVER["SERVER_PROTOCOL"] . " 400 Bad Request");

            }

        }

        private function verifyAccess($checkCorporation, $checkAlliance) {

            $hasAccess = (
                in_array("Super Admin", $this->accessRoles)
                or (
                    in_array("Configure Alliance", $this->accessRoles)
                    and (
                        (
                            isset($this->characterStats["Alliance ID"])
                            and (int)$checkAlliance === (int)$this->characterStats["Alliance ID"]
                        )
                        or (
                            !isset($this->characterStats["Alliance ID"])
                            and is_null($checkAlliance)
                        )
                    )
                )
                or (
                    in_array("Configure Corporation", $this->accessRoles)
                    and (int)$checkCorporation === (int)$this->characterStats["Corporation ID"]
                )
            );

            return $hasAccess;

        }

    }

?>
