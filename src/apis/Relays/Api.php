<?php

    namespace Ridley\Apis\Relays;

    class Api implements \Ridley\Interfaces\Api {

        private $databaseConnection;
        private $logger;
        private $accessRoles;
        private $characterStats;
        private $esiHandler;

        public $approvedNotifications = [
            "StructureDestroyed",
            "StructureLostArmor",
            "StructureLostShields",
            "StructureUnderAttack",
            "StructureAnchoring",
            "StructureFuelAlert",
            "StructureOnline",
            "StructureUnanchoring",
            "StructureServicesOffline",
            "StructureWentHighPower",
            "StructureWentLowPower",
            "StructureImpendingAbandonmentAssetsAtRisk",
            "StructuresReinforcementChanged",
            "OwnershipTransferred",
            "MoonminingLaserFired",
            "MoonminingAutomaticFracture",
            "MoonminingExtractionCancelled",
            "MoonminingExtractionFinished",
            "MoonminingExtractionStarted",
            "EntosisCaptureStarted",
            "SovCommandNodeEventStarted",
            "SovStructureReinforced",
            "SovStructureDestroyed",
            "SovAllClaimAquiredMsg",
            "SovAllClaimLostMsg",
            "SovStructureSelfDestructRequested",
            "SovStructureSelfDestructFinished",
            "SovStructureSelfDestructCancel",
            "SkyhookOnline",
            "SkyhookLostShields",
            "SkyhookUnderAttack",
            "SkyhookDestroyed",
            "SkyhookDeployed",
            "unknown notification type (281)",
            "unknown notification type (282)",
            "unknown notification type (283)",
            "unknown notification type (284)",
            "unknown notification type (285)",
            "OrbitalAttacked",
            "OrbitalReinforced",
            "TowerAlertMsg",
            "TowerResourceAlertMsg",
            "AllAnchoringMsg",
            "CorpTaxChangeMsg",
            "CorpNewCEOMsg",
            "CorpVoteCEORevokedMsg", 
            "CorpVoteMsg",
            "CorpNewsMsg"
        ];

        private $approvedPingValues = [
            "everyone",
            "channel",
            "here",
            "none"
        ];

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
                    $_POST["Action"] == "Get_Relay_Corp"
                    and isset($_POST["ID"])
                ){

                    $this->getRelayCorp($_POST["ID"]);

                }
                elseif (
                    $_POST["Action"] == "Verify_URL"
                    and isset($_POST["URL"])
                ){

                    $urlData = $this->getWebhookData($_POST["URL"]);

                    if ($urlData["Verified"]) {

                        echo json_encode($urlData);

                    }
                    else {

                        header($_SERVER["SERVER_PROTOCOL"] . " 400 Bad Request");

                    }

                }
                elseif (
                    $_POST["Action"] == "Create_Relay"
                    and isset($_POST["URL"])
                    and isset($_POST["Corporation_ID"])
                    and is_numeric($_POST["Corporation_ID"])
                    and isset($_POST["Ping_Type"])
                    and in_array($_POST["Ping_Type"], $this->approvedPingValues)
                    and isset($_POST["Notification_Whitelist"])
                    and is_array($_POST["Notification_Whitelist"])
                    and !array_diff($_POST["Notification_Whitelist"], $this->approvedNotifications)
                    and isset($_POST["Server_Name"])
                    and isset($_POST["Channel_Name"])
                ){

                    $this->createRelay(
                        $_POST["URL"],
                        $_POST["Corporation_ID"],
                        $_POST["Ping_Type"],
                        $_POST["Notification_Whitelist"],
                        $_POST["Server_Name"],
                        $_POST["Channel_Name"]
                    );

                }
                elseif (
                    $_POST["Action"] == "Query_Relay"
                    and isset($_POST["ID"])
                ){

                    $this->queryRelayData(
                        $_POST["ID"]
                    );

                }
                elseif (
                    $_POST["Action"] == "Delete_Relay"
                    and isset($_POST["ID"])
                ){

                    $this->deleteRelay(
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

        private function createRelay(
            $incomingURL,
            $incomingCorporationID,
            $incomingPingType,
            $incomingWhitelist,
            $incomingServerName,
            $incomingChannelName
        ) {

            $success = false;

            $relayAffiliation = [
                "corporation" => [
                    "ID" => (int)$incomingCorporationID,
                    "Name" => null
                ],
                "alliance" => [
                    "ID" => null,
                    "Name" => null
                ],
            ];


            $webhook = $this->getWebhookData($incomingURL, true);

            if (
                $webhook !== false
                and (
                    !is_null($webhook->serverName)
                    or (
                        !is_null($incomingServerName)
                        and $incomingServerName !== ""
                    )
                )
                and (
                    !is_null($webhook->channelName)
                    or (
                        !is_null($incomingChannelName)
                        and $incomingChannelName != ""
                    )
                )
            ) {

                if (
                    (
                        !is_null($incomingServerName)
                        and $incomingServerName !== ""
                    )
                    or (
                        !is_null($incomingChannelName)
                        and $incomingChannelName !== ""
                    )
                ) {

                    $webhook->commitDestinationInfo(
                        $incomingServerName,
                        $incomingChannelName
                    );

                }
                $corporationCall = $this->esiHandler->call(endpoint: "/corporations/{corporation_id}/", corporation_id: (int)$relayAffiliation["corporation"]["ID"], retries: 1);

                if ($corporationCall["Success"]) {

                    $relayAffiliation["alliance"]["ID"] = (isset($corporationCall["Data"]["alliance_id"])) ? (int)$corporationCall["Data"]["alliance_id"] : null;

                    if (
                        $this->verifyAccess(
                            $relayAffiliation["corporation"]["ID"],
                            $relayAffiliation["alliance"]["ID"]
                        )
                    ) {

                        $namesToCheck = [$relayAffiliation["corporation"]["ID"]];

                        if (!is_null($relayAffiliation["alliance"]["ID"])) {
                            $namesToCheck[] = $relayAffiliation["alliance"]["ID"];
                        }

                        $namesCall = $this->esiHandler->call(endpoint: "/universe/names/", ids: $namesToCheck, retries: 1);

                        if ($namesCall["Success"]) {

                            $allianceName = null;

                            foreach ($namesCall["Data"] as $eachName) {

                                $relayAffiliation[$eachName["category"]]["Name"] = $eachName["name"];

                            }

                            switch ($webhook->webhookType) {
                                case "Slack":
                                    $confirmationMessageData = [
                                        "text" => "A New Relay Has Been Created!",
                                    	"blocks" => [
                                    		[
                                    			"type" => "header",
                                    			"text" => [
                                    				"type" => "plain_text",
                                    				"text" => "A New Relay Has Been Created!",
                                    				"emoji" => True
                                    			]
                                    		],
                                    		[
                                    			"type" => "divider"
                                    		],
                                    		[
                                    			"type" => "section",
                                    			"text" => [
                                                    "type" => "mrkdwn",
                                                    "text" => "*Relay For:* " . $relayAffiliation["corporation"]["Name"] . (!is_null($relayAffiliation["alliance"]["Name"]) ? (" [" . $relayAffiliation["alliance"]["Name"] . "]") : "") . "\n*Ping Type:* " . $incomingPingType . "\n*Approved Notifications:* \n```\n" . implode("\n", $incomingWhitelist) . "\n```"
                                                ]
                                    		]
                                        ]
                                    ];
                                    break;
                                case "Discord":
                                    $confirmationMessageData = [
                                        "content" => "**A New Relay Has Been Created!**",
                                        "embeds" => [
                                            [
                                                "fields" => [
                                                    ["name" => "Relay For", "value" => ($relayAffiliation["corporation"]["Name"] . (!is_null($relayAffiliation["alliance"]["Name"]) ? (" [" . $relayAffiliation["alliance"]["Name"] . "]") : "")), "inline" => false],
                                                    ["name" => "Ping Type", "value" => $incomingPingType, "inline" => false],
                                                    ["name" => "Approved Notifications", "value" => ("```\n" . implode("\n", $incomingWhitelist) . "\n```"), "inline" => false]
                                                ]
                                            ]
                                        ]
                                    ];
                                    break;
                            }

                            if ($webhook->sendMessage($confirmationMessageData)) {

                                $idBytes = random_bytes(32);
                                $uniqueID = bin2hex($idBytes);

                                $currentTime = time();

                                $relay = new \Ridley\Objects\Relays\Relay(
                                    id: $uniqueID,
                                    type: $webhook->webhookType,
                                    channelid: $webhook->channelID,
                                    serverid: $webhook->serverID,
                                    url: $incomingURL,
                                    pingType: $incomingPingType,
                                    whiteListString: json_encode($incomingWhitelist),
                                    timestamp: $currentTime,
                                    corporationID: $relayAffiliation["corporation"]["ID"],
                                    corporationName: $relayAffiliation["corporation"]["Name"],
                                    allianceID: $relayAffiliation["alliance"]["ID"],
                                    allianceName: $relayAffiliation["alliance"]["Name"],
                                    newlyGenerated: true,
                                    databaseConnection: $this->databaseConnection
                                );

                                $this->logger->make_log_entry(
                                    logType: "Relay Created",
                                    logDetails: "Relay for " . $relayAffiliation["corporation"]["Name"] . (!is_null($relayAffiliation["alliance"]["Name"]) ? (" [" . $relayAffiliation["alliance"]["Name"] . "]") : "") . " going to the " . $webhook->channelName . " channel of the " . $webhook->serverName . " " . $webhook->webhookType . " Server created. \nID: " . $uniqueID
                                );

                                echo json_encode(["Success" => true]);
                                $success = true;

                            }

                        }

                    }

                }

            }

            if (!$success) {

                header($_SERVER["SERVER_PROTOCOL"] . " 400 Bad Request");

            }

        }

        private function deleteRelay($incomingID) {
            $success = false;

            $deletionQuery = $this->databaseConnection->prepare("SELECT
                relays.id , relays.type , relays.corporationid , relays.corporationname , relays.allianceid , relays.alliancename , servers.name AS server, channels.name AS channel
                FROM relays
                LEFT JOIN servers
                ON relays.type = servers.type AND relays.serverid = servers.id
                LEFT JOIN channels
                ON relays.type = channels.type AND relays.channelid = channels.id
                WHERE relays.id=:id;"
            );
            $deletionQuery->bindParam(":id", $incomingID);

            $deletionQuery->execute();
            $deletionQueryResults = $deletionQuery->fetchAll();

            foreach ($deletionQueryResults as $eachRelay) {

                if (
                    $this->verifyAccess(
                        $eachRelay["corporationid"],
                        $eachRelay["allianceid"]
                    )
                ) {

                    $deletionRequest = $this->databaseConnection->prepare("DELETE FROM relays WHERE id=:id;");
                    $deletionRequest->bindParam(":id", $incomingID);
                    $deletionRequest->execute();

                    $this->logger->make_log_entry(
                        logType: "Relay Deleted",
                        logDetails: "Relay for " . $eachRelay["corporationname"] . " [" . $eachRelay["alliancename"] . "] going to the " . $eachRelay["channel"] . " channel of the " . $eachRelay["server"] . " " . $eachRelay["type"] . " Server deleted. \nID: " . $eachRelay["id"]
                    );

                    echo json_encode(["Success" => true]);
                    $success = true;

                }

            }

            if (!$success) {

                header($_SERVER["SERVER_PROTOCOL"] . " 400 Bad Request");

            }

        }

        private function queryRelayData($incomingID) {
            $success = false;

            $dataQuery = $this->databaseConnection->prepare("SELECT
                relays.id, relays.type, relays.pingtype, relays.whitelist, relays.timestamp, relays.corporationid, relays.corporationname, relays.allianceid, relays.alliancename, servers.name AS server, channels.name AS channel, COUNT(relaycharacters.id) AS characters
                FROM relays
                LEFT JOIN servers
                ON relays.type = servers.type AND relays.serverid = servers.id
                LEFT JOIN channels
                ON relays.type = channels.type AND relays.channelid = channels.id
                LEFT JOIN relaycharacters
                ON relays.corporationid = relaycharacters.corporationid
                WHERE relays.id=:id;"
            );
            $dataQuery->bindParam(":id", $incomingID);

            $dataQuery->execute();
            $dataResults = $dataQuery->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($dataResults as $eachRelay) {

                if (
                    $this->verifyAccess(
                        $eachRelay["corporationid"],
                        $eachRelay["allianceid"]
                    )
                ) {

                    $resultsToExport = $eachRelay;
                    $resultsToExport["whitelist"] = json_decode($resultsToExport["whitelist"], true);

                    echo json_encode($resultsToExport);
                    $success = true;

                }

            }

            if (!$success) {

                header($_SERVER["SERVER_PROTOCOL"] . " 400 Bad Request");

            }

        }

        private function getWebhookData($incomingURL, $returnObject = false) {

            $webhookData = [
                "Verified" => false,
                "Type" => null,
                "Server ID" => null,
                "Server Name" => null,
                "Channel ID" => null,
                "Channel Name" => null
            ];

            if (filter_var($incomingURL)) {

                $parsedURL = parse_url($incomingURL);

                if (isset($parsedURL["scheme"]) and $parsedURL["scheme"] === "https") {

                    if (
                        isset($parsedURL["host"])
                        and $parsedURL["host"] === "hooks.slack.com"
                        and isset($parsedURL["path"])
                        and str_starts_with($parsedURL["path"], "/services/")
                        and count(explode("/", $parsedURL["path"])) === 5
                    ) {

                        $webhookData["Verified"] = true;
                        $webhookData["Type"] = "Slack";

                        $webhook = new \Ridley\Objects\Relays\Webhooks\Slack\Webhook($this->databaseConnection, $incomingURL);

                        $webhookData["Server ID"] = $webhook->serverID;
                        $webhookData["Server Name"] = htmlspecialchars($webhook->serverName ?? "");
                        $webhookData["Channel ID"] = $webhook->channelID;
                        $webhookData["Channel Name"] = htmlspecialchars($webhook->channelName ?? "");

                    }
                    elseif (
                        isset($parsedURL["host"])
                        and $parsedURL["host"] === "discord.com"
                        and isset($parsedURL["path"])
                        and str_starts_with($parsedURL["path"], "/api/webhooks/")
                        and count(explode("/", $parsedURL["path"])) === 5
                    ) {

                        $webhookData["Verified"] = true;
                        $webhookData["Type"] = "Discord";

                        $webhook = new \Ridley\Objects\Relays\Webhooks\Discord\Webhook($this->databaseConnection, $incomingURL);

                        if ($webhook->webhookVerified) {

                            $webhookData["Server ID"] = $webhook->serverID;
                            $webhookData["Server Name"] = htmlspecialchars($webhook->serverName ?? "");
                            $webhookData["Channel ID"] = $webhook->channelID;
                            $webhookData["Channel Name"] = htmlspecialchars($webhook->channelName ?? "");

                        }
                        else {

                            $webhookData["Verified"] = false;

                        }

                    }

                }

            }

            if ($returnObject) {

                if ($webhookData["Verified"]) {

                    return $webhook;

                }
                else {

                    return false;

                }

            }
            else {

                return $webhookData;

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
