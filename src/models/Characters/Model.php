<?php

    namespace Ridley\Models\Characters;

    class Model implements \Ridley\Interfaces\Model {

        private $controller;
        private $databaseConnection;
        private $accessRoles;
        private $characterStats;

        public $comprehensiveData = [];

        public function __construct(
            private \Ridley\Core\Dependencies\DependencyManager $dependencies
        ) {

            $this->databaseConnection = $this->dependencies->get("Database");
            $this->controller = $this->dependencies->get("Controller");
            $this->accessRoles = $this->dependencies->get("Access Roles");
            $this->characterStats = $this->dependencies->get("Character Stats");

            $this->generateComprehensiveData();

        }

        private function generateComprehensiveData() {

            foreach ($this->controller->accessibleEntitites as $eachAlliance) {

                $this->comprehensiveData[$eachAlliance["ID"]] = [
                    "ID" => $eachAlliance["ID"],
                    "Name" => $eachAlliance["Name"],
                    "Relay Characters" => 0,
                    "Corporations" => []
                ];

                foreach ($eachAlliance["Corporations"] as $eachCorporation) {

                    $this->comprehensiveData[$eachAlliance["ID"]]["Corporations"][$eachCorporation["ID"]] = new \Ridley\Objects\Relays\RelayCorp(
                        $eachCorporation["ID"],
                        $this->accessRoles,
                        $this->characterStats,
                        $this->databaseConnection,
                        false,
                        $eachCorporation["Name"]
                    );

                    $this->comprehensiveData[$eachAlliance["ID"]]["Relay Characters"] += $this->comprehensiveData[$eachAlliance["ID"]]["Corporations"][$eachCorporation["ID"]]->getBreakdown()["Total Characters"];

                }

                uasort($this->comprehensiveData[$eachAlliance["ID"]]["Corporations"], function($a, $b) {
                    return $b->getBreakdown()["Total Characters"] <=> $a->getBreakdown()["Total Characters"];
                });

            }

            uasort($this->comprehensiveData, function($a, $b) {
                return $b["Relay Characters"] <=> $a["Relay Characters"];
            });

        }

    }

?>
