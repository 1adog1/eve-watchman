<?php

    namespace Ridley\Objects\Relays;

    class RelayCorp {

        private $verified = false;
        private $breakdown = [
            "ID" => null,
            "Name" => null,
            "Alliance ID" => null,
            "Alliance Name" => null,
            "Total Characters" => 0,
            "Invalid Characters" => 0,
            "Directors" => 0,
            "Citadel Alert Characters" => 0,
            "Role Breakdown" => []
        ];
        private $characters = [];

        public function __construct(
            private $id,
            private $accessRoles,
            private $characterStats,
            private $databaseConnection,
            private $importCharacters = false,
            private $manualName = null
        ) {

            $this->breakdown["ID"] = $this->id;
            $this->breakdown["Name"] = $this->manualName;
            $this->initialize();

        }

        public function getBreakdown() {

            if ($this->verified or !is_null($this->manualName)) {

                return $this->breakdown;

            }
            else {

                return false;

            }

        }

        public function getCharacters() {
            
            return $this->characters;

        }

        private function initialize() {

            $filterData = $this->getFilterRequest(initialCondition: "WHERE corporationid=:corporationid");

            $relayCharactersQuery = $this->databaseConnection->prepare("SELECT * FROM relaycharacters " . $filterData["Request"] . ";");

            $relayCharactersQuery->bindValue(":corporationid", $this->id);

            foreach ($filterData["Variables"] as $eachVariable => $eachValue) {

                $relayCharactersQuery->bindValue($eachVariable, $eachValue["Value"], $eachValue["Type"]);

            }

            $relayCharactersQuery->execute();
            $relayCharacters = $relayCharactersQuery->fetchAll();

            foreach ($relayCharacters as $eachCharacter) {

                $characterRoles = json_decode($eachCharacter["roles"]);

                if ($this->importCharacters) {

                    $this->characters[$eachCharacter["id"]] = new \Ridley\Objects\Characters\RelayCharacter(
                        (int)$eachCharacter["id"],
                        $eachCharacter["name"],
                        $eachCharacter["status"],
                        (int)$eachCharacter["corporationid"],
                        $eachCharacter["corporationname"],
                        (int)$eachCharacter["allianceid"],
                        $eachCharacter["alliancename"],
                        $characterRoles
                    );

                }

                if ($eachCharacter["status"] === "Valid") {

                    if (!$this->verified) {

                        $this->breakdown["Name"] = $eachCharacter["corporationname"];
                        $this->breakdown["Alliance ID"] = $eachCharacter["allianceid"];
                        $this->breakdown["Alliance Name"] = $eachCharacter["alliancename"];
                        $this->verified = true;
                    }

                    $this->breakdown["Total Characters"]++;

                    if (in_array("Director", $characterRoles)) {

                        $this->breakdown["Directors"]++;

                    }
                    if (
                        in_array("Director", $characterRoles)
                        or in_array("Station_Manager", $characterRoles)
                    ) {

                        $this->breakdown["Citadel Alert Characters"]++;

                    }

                    foreach ($characterRoles as $eachRole) {

                        $decodedName = str_replace("_", " ", $eachRole);

                        if (!str_contains($decodedName, " Take ") and !str_contains($decodedName, " Query ")) {

                            if (!isset($this->breakdown["Role Breakdown"][$decodedName])) {

                                $this->breakdown["Role Breakdown"][$decodedName] = 0;

                            }

                            $this->breakdown["Role Breakdown"][$decodedName]++;

                        }

                    }

                    ksort($this->breakdown["Role Breakdown"]);

                }
                else {

                    $this->breakdown["Invalid Characters"]++;

                }

            }

        }

        private function generateRequestPrefix($currentRequest) {

            if ($currentRequest === "") {

                return "WHERE ";

            }
            else {

                return " AND ";

            }
        }

        private function getFilterRequest(?string $initialCondition = null) {

            $filterDetails = [
                "Request" => (!is_null($initialCondition)) ? $initialCondition : "",
                "Variables" => []
            ];

            if (in_array("Super Admin", $this->accessRoles)) {



            }
            elseif (in_array("Configure Alliance", $this->accessRoles)) {

                if (isset($this->characterStats["Alliance ID"])) {

                    $filterDetails["Request"] .= $this->generateRequestPrefix($filterDetails["Request"]) . "allianceid=:allianceid";
                    $filterDetails["Variables"][":allianceid"] = ["Value" => $this->characterStats["Alliance ID"], "Type" => \PDO::PARAM_INT];

                }
                else {

                    $filterDetails["Request"] .= $this->generateRequestPrefix($filterDetails["Request"]) . "allianceid IS NULL";

                }

            }
            elseif (in_array("Configure Corporation", $this->accessRoles)) {

                $filterDetails["Request"] .= $this->generateRequestPrefix($filterDetails["Request"]) . "corporationid=:corporationid";
                $filterDetails["Variables"][":corporationid"] = ["Value" => $this->characterStats["Corporation ID"], "Type" => \PDO::PARAM_INT];

            }
            else {

                $filterDetails["Request"] .= $this->generateRequestPrefix($filterDetails["Request"]) . "false";

            }

            return $filterDetails;

        }

    }

?>
