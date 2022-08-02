<?php

    namespace Ridley\Objects\Relays\Webhooks\Discord;

    class Webhook {

        public $webhookType = "Discord";
        public $webhookVerified = false;
        public $serverID;
        public $serverName = null;
        public $channelID;
        public $channelName = null;

        public function __construct(
            private $databaseConnection,
            private $url
        ) {

            $this->setInfo();
            $this->setDestinationInfo();

        }

        private function setInfo() {

            $parsedWebhook = parse_url($this->url);
            $splitPath = explode("/", $parsedWebhook["path"]);
            $webhookID = $splitPath[3];
            $webhookToken = $splitPath[4];

            $discordURL = "https://discord.com/api/webhooks/";
            $webhookURL = $discordURL . $webhookID . "/" . $webhookToken;

            $verificationData = @file_get_contents($webhookURL);

            $verificationStatusData = $http_response_header[0];

            if (str_contains($verificationStatusData, "200")) {

                $parsedVerification = json_decode($verificationData, true);

                $this->webhookVerified = true;
                $this->serverID = $parsedVerification["guild_id"];
                $this->channelID = $parsedVerification["channel_id"];

            }

        }

        private function setDestinationInfo() {

            $serverQuery = $this->databaseConnection->prepare("SELECT name FROM servers WHERE type=:type AND id=:serverid");
            $serverQuery->bindValue(":type", "Discord");
            $serverQuery->bindParam(":serverid", $this->serverID);
            $serverQuery->execute();
            $serverData = $serverQuery->fetch();

            if (!empty($serverData)) {

                $this->serverName = $serverData["name"];

                $channelQuery = $this->databaseConnection->prepare("SELECT name FROM channels WHERE type=:type AND id=:channelid AND serverid=:serverid");
                $channelQuery->bindValue(":type", "Discord");
                $channelQuery->bindParam(":channelid", $this->channelID);
                $channelQuery->bindParam(":serverid", $this->serverID);
                $channelQuery->execute();
                $channelData = $channelQuery->fetch();

                if (!empty($channelData)) {

                    $this->channelName = $channelData["name"];

                }
            }

        }

        public function commitDestinationInfo(
            $newServerName = null,
            $newChannelName = null
        ) {

            if (!is_null($newServerName) and $newServerName !== "") {

                $this->serverName = $newServerName;

                $checkQuery = $this->databaseConnection->prepare("SELECT * FROM servers WHERE type=:type AND id=:id");
                $checkQuery->bindValue(":type", "Discord");
                $checkQuery->bindParam(":id", $this->serverID);
                $checkQuery->execute();
                $checkData = $checkQuery->fetch();

                if (!empty($checkData)) {

                    $updateStatement = $this->databaseConnection->prepare("UPDATE servers SET name = :name WHERE type=:type AND id=:id");
                    $updateStatement->bindValue(":type", "Discord");
                    $updateStatement->bindParam(":id", $this->serverID);
                    $updateStatement->bindParam(":name", $newServerName);
                    $updateStatement->execute();

                }
                else {

                    $insertStatement = $this->databaseConnection->prepare("INSERT INTO servers (type, id, name) VALUES (:type, :id, :name)");
                    $insertStatement->bindValue(":type", "Discord");
                    $insertStatement->bindParam(":id", $this->serverID);
                    $insertStatement->bindParam(":name", $newServerName);
                    $insertStatement->execute();

                }

            }

            if (!is_null($newChannelName) and $newChannelName !== "") {

                $this->channelName = $newChannelName;

                $checkQuery = $this->databaseConnection->prepare("SELECT * FROM channels WHERE type=:type AND id=:id AND serverid=:serverid");
                $checkQuery->bindValue(":type", "Discord");
                $checkQuery->bindParam(":id", $this->channelID);
                $checkQuery->bindParam(":serverid", $this->serverID);
                $checkQuery->execute();
                $checkData = $checkQuery->fetch();

                if (!empty($checkData)) {

                    $updateStatement = $this->databaseConnection->prepare("UPDATE channels SET name = :name WHERE type=:type AND id=:id AND serverid=:serverid");
                    $updateStatement->bindValue(":type", "Discord");
                    $updateStatement->bindParam(":id", $this->channelID);
                    $updateStatement->bindParam(":name", $newChannelName);
                    $updateStatement->bindParam(":serverid", $this->serverID);
                    $updateStatement->execute();

                }
                else {

                    $insertStatement = $this->databaseConnection->prepare("INSERT INTO channels (type, id, name, serverid) VALUES (:type, :id, :name, :serverid)");
                    $insertStatement->bindValue(":type", "Discord");
                    $insertStatement->bindParam(":id", $this->channelID);
                    $insertStatement->bindParam(":name", $newChannelName);
                    $insertStatement->bindParam(":serverid", $this->serverID);
                    $insertStatement->execute();

                }

            }

        }

        public function sendMessage($messageData) {

            $context = [
                "http" => [
                    "ignore_errors" => true,
                    "header" => [
                        "accept: application/json",
                        "Content-Type: application/json"
                    ],
                    "method" => "POST",
                    "content" => json_encode($messageData)
                ]
            ];

            $request = file_get_contents(
                filename: $this->url,
                context: stream_context_create($context)
            );

            return str_contains($http_response_header[0], "204");

        }

    }

?>
