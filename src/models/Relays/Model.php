<?php

    namespace Ridley\Models\Relays;

    class Model implements \Ridley\Interfaces\Model {

        private $controller;
        private $databaseConnection;

        public $relays = [];
        public $relayCorps = [];

        public function __construct(
            private \Ridley\Core\Dependencies\DependencyManager $dependencies
        ) {

            $this->controller = $this->dependencies->get("Controller");
            $this->databaseConnection = $this->dependencies->get("Database");

            $this->populateRelayCorps();
            $this->populateRelays();

        }

        private function populateRelayCorps() {

            $filterData = $this->controller->getFilterRequest(initialCondition: "WHERE status=:status");

            $relayCharactersQuery = $this->databaseConnection->prepare("SELECT * FROM relaycharacters " . $filterData["Request"] . ";");

            $relayCharactersQuery->bindValue(":status", "Valid");

            foreach ($filterData["Variables"] as $eachVariable => $eachValue) {

                $relayCharactersQuery->bindValue($eachVariable, $eachValue["Value"], $eachValue["Type"]);

            }

            $relayCharactersQuery->execute();
            $relayCharacters = $relayCharactersQuery->fetchAll();

            foreach ($relayCharacters as $eachCharacter) {

                if (!isset($this->relayCorps[$eachCharacter["corporationid"]])) {

                    $this->relayCorps[$eachCharacter["corporationid"]] = [
                        "Name" => $eachCharacter["corporationname"],
                        "Alliance Name" => $eachCharacter["alliancename"]
                    ];

                }

            }

        }

        private function populateRelays() {

            $filterData = $this->controller->getFilterRequest();

            $relaysQuery =  $this->databaseConnection->prepare("SELECT * FROM relays " . $filterData["Request"] . " ORDER BY type, serverid, channelid, timestamp DESC;");

            foreach ($filterData["Variables"] as $eachVariable => $eachValue) {

                $relaysQuery->bindValue($eachVariable, $eachValue["Value"], $eachValue["Type"]);

            }

            $relaysQuery->execute();
            $existingRelays = $relaysQuery->fetchAll();

            foreach ($existingRelays as $eachRelay) {

                $this->relays[] = new \Ridley\Objects\Relays\Relay(
                    id: $eachRelay["id"],
                    type: $eachRelay["type"],
                    channelid: $eachRelay["channelid"],
                    serverid: $eachRelay["serverid"],
                    url: $eachRelay["url"],
                    pingType: $eachRelay["pingtype"],
                    whiteListString: $eachRelay["whitelist"],
                    timestamp: $eachRelay["timestamp"],
                    corporationID: $eachRelay["corporationid"],
                    corporationName: $eachRelay["corporationname"],
                    allianceID: $eachRelay["allianceid"],
                    allianceName: $eachRelay["alliancename"],
                    newlyGenerated: false,
                    databaseConnection: $this->databaseConnection
                );

            }

        }

    }

?>
