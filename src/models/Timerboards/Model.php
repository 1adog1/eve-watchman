<?php

    namespace Ridley\Models\Timerboards;

    class Model implements \Ridley\Interfaces\Model {

        private $controller;
        private $databaseConnection;

        public $timerboards = [];
        public $relayCorps = [];

        public function __construct(
            private \Ridley\Core\Dependencies\DependencyManager $dependencies
        ) {

            $this->controller = $this->dependencies->get("Controller");
            $this->databaseConnection = $this->dependencies->get("Database");

            $this->populateRelayCorps();
            $this->populateTimerboards();

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

        private function populateTimerboards() {

            $filterData = $this->controller->getFilterRequest();

            $timerboardsQuery =  $this->databaseConnection->prepare("SELECT * FROM timerboards " . $filterData["Request"] . " ORDER BY type, timestamp DESC;");

            foreach ($filterData["Variables"] as $eachVariable => $eachValue) {

                $timerboardsQuery->bindValue($eachVariable, $eachValue["Value"], $eachValue["Type"]);

            }

            $timerboardsQuery->execute();
            $existingTimerboards = $timerboardsQuery->fetchAll();

            foreach ($existingTimerboards as $eachTimerboard) {

                $this->timerboards[] = new \Ridley\Objects\Timerboards\Timerboard(
                    id: $eachTimerboard["id"],
                    type: $eachTimerboard["type"],
                    url: $eachTimerboard["url"],
                    token: $eachTimerboard["token"],
                    whiteListString: $eachTimerboard["whitelist"],
                    timestamp: $eachTimerboard["timestamp"],
                    corporationID: $eachTimerboard["corporationid"],
                    corporationName: $eachTimerboard["corporationname"],
                    allianceID: $eachTimerboard["allianceid"],
                    allianceName: $eachTimerboard["alliancename"],
                    newlyGenerated: false,
                    databaseConnection: $this->databaseConnection
                );

            }

        }

    }

?>
