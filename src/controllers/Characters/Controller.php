<?php

    namespace Ridley\Controllers\Characters;

    class Controller implements \Ridley\Interfaces\Controller {

        private $databaseConnection;
        private $logger;
        private $accessRoles;
        private $characterStats;
        private $esiHandler;

        public $accessibleEntitites = [];

        public function __construct(
            private \Ridley\Core\Dependencies\DependencyManager $dependencies
        ) {

            $this->databaseConnection = $this->dependencies->get("Database");
            $this->logger = $this->dependencies->get("Logging");
            $this->accessRoles = $this->dependencies->get("Access Roles");
            $this->characterStats = $this->dependencies->get("Character Stats");
            $this->esiHandler = new \Ridley\Objects\ESI\Handler($this->databaseConnection);

            $this->generateEntities();

        }

        private function generateEntities() {

            if (in_array("Super Admin", $this->accessRoles)) {

                foreach ($this->getAllAlliances() as $eachAlliance) {

                    if (is_null($eachAlliance)) {

                        $this->accessibleEntitites[] = [
                            "Name" => "No Alliance",
                            "ID" => 0,
                            "Corporations" => $this->getNoAllianceEntities()
                        ];

                    }
                    else {

                        $this->accessibleEntitites[] = $this->getAllianceEntities((int)$eachAlliance);

                    }

                }

            }
            elseif (in_array("Configure Alliance", $this->accessRoles)) {

                if (isset($this->characterStats["Alliance ID"])) {

                    $this->accessibleEntitites[] = $this->getAllianceEntities((int)$this->characterStats["Alliance ID"]);

                }
                else {

                    $this->accessibleEntitites[] = [
                        "Name" => "No Alliance",
                        "ID" => 0,
                        "Corporations" => $this->getNoAllianceEntities()
                    ];

                }

            }
            elseif (in_array("Configure Corporation", $this->accessRoles)) {

                $corporationCall = $this->esiHandler->call(endpoint: "/corporations/{corporation_id}/", corporation_id: (int)$this->characterStats["Corporation ID"], retries: 1);

                if ($corporationCall["Success"]) {

                    $this->accessibleEntitites[] = [
                        "Name" => isset($this->characterStats["Alliance Name"]) ? : "No Alliance",
                        "ID" => isset($this->characterStats["Alliance ID"]) ? (int)$this->characterStats["Alliance ID"] : 0,
                        "Corporations" => [
                            [
                                "Name" => $corporationCall["Data"]["name"],
                                "ID" => (int)$this->characterStats["Corporation ID"]
                            ]
                        ]
                    ];

                }

            }

        }

        private function getAllAlliances() {

            $alliancesQuery = $this->databaseConnection->prepare("SELECT DISTINCT allianceid FROM relaycharacters");

            $alliancesQuery->execute();
            $allianceQueryResults = $alliancesQuery->fetchAll(\PDO::FETCH_COLUMN, 0);

            return $allianceQueryResults;

        }

        private function getAllianceEntities(int $allianceID) {

            $allianceInfo = [
                "Name" => null,
                "ID" => null,
                "Corporations" => []
            ];

            $corporationsCall = $this->esiHandler->call(endpoint: "/alliances/{alliance_id}/corporations/", alliance_id: (int)$allianceID, retries: 1);

            if ($corporationsCall["Success"]) {

                $namesCall = $this->esiHandler->call(endpoint: "/universe/names/", ids: array_merge($corporationsCall["Data"], [(int)$allianceID]), retries: 1);

                if ($namesCall["Success"]) {

                    foreach ($namesCall["Data"] as $eachName) {

                        if ($eachName["category"] === "alliance") {

                            $allianceInfo["Name"] = $eachName["name"];
                            $allianceInfo["ID"] = (int)$eachName["id"];

                        }
                        else {

                            $allianceInfo["Corporations"][] = [
                                "Name" => $eachName["name"],
                                "ID" => (int)$eachName["id"]
                            ];

                        }

                    }

                }

            }

            return $allianceInfo;

        }

        private function getNoAllianceEntities() {

            $corporationList = [];

            $noAllianceQuery = $this->databaseConnection->prepare("SELECT DISTINCT corporationid, corporationname FROM relaycharacters WHERE allianceid IS NULL");

            $noAllianceQuery->execute();
            $noAllianceQueryResults = $noAllianceQuery->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($noAllianceQueryResults as $eachCorporation) {

                $corporationList[] = [
                    "Name" => $eachCorporation["corporationname"],
                    "ID" => $eachCorporation["corporationid"]
                ];

            }

            return $corporationList;

        }

    }

?>
