<?php

    namespace Ridley\Apis\Timerboards;

    class Api implements \Ridley\Interfaces\Api {

        private $databaseConnection;
        private $configVariables;
        private $logger;
        private $accessRoles;
        private $characterStats;
        private $esiHandler;

        public $approvedNotifications = [
            "StructureLostArmor",
            "StructureLostShields",
            "SkyhookLostShields",
            "unknown notification type (282)",
            "OrbitalReinforced"
        ];

        private $approvedTypes = [];
        private $approvedDomains = [];

        public function __construct(
            private \Ridley\Core\Dependencies\DependencyManager $dependencies
        ) {

            $this->databaseConnection = $this->dependencies->get("Database");
            $this->configVariables = $this->dependencies->get("Configuration Variables");
            $this->logger = $this->dependencies->get("Logging");
            $this->accessRoles = $this->dependencies->get("Access Roles");
            $this->characterStats = $this->dependencies->get("Character Stats");
            $this->esiHandler = new \Ridley\Objects\ESI\Handler($this->databaseConnection);

            $this->approvedTypes = $this->configVariables["Approved Timerboard Types"];
            $this->approvedDomains = $this->configVariables["Approved Timerboard Domains"];

            if (isset($_POST["Action"])) {

                if (
                    $_POST["Action"] == "Get_Relay_Corp"
                    and isset($_POST["ID"])
                ){

                    $this->getRelayCorp($_POST["ID"]);

                }
                elseif (
                    $_POST["Action"] == "Create_Timerboard"
                    and in_array($_POST["Timerboard_Type"], $this->approvedTypes)
                    and isset($_POST["URL"])
                    and isset($_POST["Token"])
                    and isset($_POST["Corporation_ID"])
                    and is_numeric($_POST["Corporation_ID"])
                    and isset($_POST["Notification_Whitelist"])
                    and is_array($_POST["Notification_Whitelist"])
                    and !array_diff($_POST["Notification_Whitelist"], $this->approvedNotifications)
                ){

                    $this->createTimerboard(
                        $_POST["Timerboard_Type"],
                        $_POST["URL"],
                        $_POST["Token"],
                        $_POST["Corporation_ID"],
                        $_POST["Notification_Whitelist"]
                    );

                }
                elseif (
                    $_POST["Action"] == "Query_Timerboard"
                    and isset($_POST["ID"])
                ){

                    $this->queryTimerboardData(
                        $_POST["ID"]
                    );

                }
                elseif (
                    $_POST["Action"] == "Delete_Timerboard"
                    and isset($_POST["ID"])
                ){

                    $this->deleteTimerboard(
                        $_POST["ID"]
                    );

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

        private function getRelayCorp($incomingID) {

            $relayCorp = new \Ridley\Objects\Relays\RelayCorp(
                $incomingID,
                $this->accessRoles,
                $this->characterStats,
                $this->databaseConnection
            );

            $corpInfo = $relayCorp->getBreakdown();

            if ($corpInfo !== false) {

                echo json_encode($corpInfo);

            }
            else {

                header($_SERVER["SERVER_PROTOCOL"] . " 400 Bad Request");

            }

        }

        private function createTimerboard(
            $incomingType,
            $incomingURL,
            $incomingToken,
            $incomingCorporationID,
            $incomingWhitelist
        ) {

            $success = false;

            $timerboadAffiliation = [
                "corporation" => [
                    "ID" => (int)$incomingCorporationID,
                    "Name" => null
                ],
                "alliance" => [
                    "ID" => null,
                    "Name" => null
                ],
            ];

            $corporationCall = $this->esiHandler->call(endpoint: "/corporations/{corporation_id}/", corporation_id: (int)$timerboadAffiliation["corporation"]["ID"], retries: 1);

            if ($corporationCall["Success"]) {

                $timerboadAffiliation["alliance"]["ID"] = (isset($corporationCall["Data"]["alliance_id"])) ? (int)$corporationCall["Data"]["alliance_id"] : null;

                if (
                    $this->verifyAccess(
                        $timerboadAffiliation["corporation"]["ID"],
                        $timerboadAffiliation["alliance"]["ID"]
                    )
                ) {

                    $namesToCheck = [$timerboadAffiliation["corporation"]["ID"]];

                    if (!is_null($timerboadAffiliation["alliance"]["ID"])) {
                        $namesToCheck[] = $timerboadAffiliation["alliance"]["ID"];
                    }

                    $namesCall = $this->esiHandler->call(endpoint: "/universe/names/", ids: $namesToCheck, retries: 1);

                    if ($namesCall["Success"]) {

                        $allianceName = null;

                        foreach ($namesCall["Data"] as $eachName) {

                            $timerboadAffiliation[$eachName["category"]]["Name"] = $eachName["name"];

                        }

                        $urlDomain = parse_url($incomingURL, PHP_URL_HOST);

                        if (in_array($urlDomain, $this->approvedDomains)) {

                            $idBytes = random_bytes(32);
                            $uniqueID = bin2hex($idBytes);

                            $currentTime = time();

                            $timerboard = new \Ridley\Objects\Timerboards\Timerboard(
                                id: $uniqueID,
                                type: $incomingType,
                                url: $incomingURL,
                                token: $incomingToken,
                                whiteListString: json_encode($incomingWhitelist),
                                timestamp: $currentTime,
                                corporationID: $timerboadAffiliation["corporation"]["ID"],
                                corporationName: $timerboadAffiliation["corporation"]["Name"],
                                allianceID: $timerboadAffiliation["alliance"]["ID"],
                                allianceName: $timerboadAffiliation["alliance"]["Name"],
                                newlyGenerated: true,
                                databaseConnection: $this->databaseConnection
                            );

                            $this->logger->make_log_entry(
                                logType: "Timerboard Created",
                                logDetails: "Timerboard for " . $timerboadAffiliation["corporation"]["Name"] . (!is_null($timerboadAffiliation["alliance"]["Name"]) ? (" [" . $timerboadAffiliation["alliance"]["Name"] . "]") : "") . " going to " . $incomingType . " created. \nID: " . $uniqueID
                            );

                            echo json_encode(["Success" => true]);
                            $success = true;

                        }

                    }

                }

            }

            if (!$success) {

                header($_SERVER["SERVER_PROTOCOL"] . " 400 Bad Request");

            }

        }

        private function deleteTimerboard($incomingID) {
            $success = false;

            $deletionQuery = $this->databaseConnection->prepare("SELECT
                id, type, corporationid, corporationname, allianceid, alliancename
                FROM timerboards
                WHERE id=:id;"
            );
            $deletionQuery->bindParam(":id", $incomingID);

            $deletionQuery->execute();
            $deletionQueryResults = $deletionQuery->fetchAll();

            foreach ($deletionQueryResults as $eachTimerboard) {

                if (
                    $this->verifyAccess(
                        $eachTimerboard["corporationid"],
                        $eachTimerboard["allianceid"]
                    )
                ) {

                    $deletionRequest = $this->databaseConnection->prepare("DELETE FROM timerboards WHERE id=:id;");
                    $deletionRequest->bindParam(":id", $incomingID);
                    $deletionRequest->execute();

                    $this->logger->make_log_entry(
                        logType: "Timerboard Deleted",
                        logDetails: "Timerboard for " . $eachTimerboard["corporationname"] . " [" . $eachTimerboard["alliancename"] . "] going to " . $eachTimerboard["type"] . " deleted. \nID: " . $eachTimerboard["id"]
                    );

                    echo json_encode(["Success" => true]);
                    $success = true;

                }

            }

            if (!$success) {

                header($_SERVER["SERVER_PROTOCOL"] . " 400 Bad Request");

            }

        }

        private function queryTimerboardData($incomingID) {
            $success = false;

            $dataQuery = $this->databaseConnection->prepare("SELECT
                timerboards.id, timerboards.type, timerboards.whitelist, timerboards.timestamp, timerboards.corporationid, timerboards.corporationname, timerboards.allianceid, timerboards.alliancename, COUNT(relaycharacters.id) AS characters
                FROM timerboards
                LEFT JOIN relaycharacters
                ON timerboards.corporationid = relaycharacters.corporationid
                WHERE timerboards.id=:id;"
            );
            $dataQuery->bindParam(":id", $incomingID);

            $dataQuery->execute();
            $dataResults = $dataQuery->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($dataResults as $eachTimerboard) {

                if (
                    $this->verifyAccess(
                        $eachTimerboard["corporationid"],
                        $eachTimerboard["allianceid"]
                    )
                ) {

                    $resultsToExport = $eachTimerboard;
                    $resultsToExport["whitelist"] = json_decode($resultsToExport["whitelist"], true);

                    echo json_encode($resultsToExport);
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
