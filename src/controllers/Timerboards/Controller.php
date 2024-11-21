<?php

    namespace Ridley\Controllers\Timerboards;

    class Controller implements \Ridley\Interfaces\Controller {

        private $databaseConnection;
        private $logger;
        private $accessRoles;
        private $characterStats;

        public $notificationTypes = [
            "Upwell_Structures" => [
                "StructureLostArmor" => ["Name" => "Structure Lost Armor"],
                "StructureLostShields" => ["Name" => "Structure Lost Shields"]
            ],
            "Skyhooks" => [
                "SkyhookLostShields" => ["Name" => "Skyhook Reinforced"]
            ],
            "Skyhooks_TEMP" => [
                "unknown notification type (282)" => ["Name" => "Skyhook Reinforced"]
            ],
            "Customs_Offices" => [
                "OrbitalReinforced" => ["Name" => "Customs Office Reinforced"]
            ]
        ];

        public function __construct(
            private \Ridley\Core\Dependencies\DependencyManager $dependencies
        ) {

            $this->databaseConnection = $this->dependencies->get("Database");
            $this->logger = $this->dependencies->get("Logging");
            $this->accessRoles = $this->dependencies->get("Access Roles");
            $this->characterStats = $this->dependencies->get("Character Stats");

        }

        public function generateRequestPrefix($currentRequest) {

            if ($currentRequest === "") {

                return "WHERE ";

            }
            else {

                return " AND ";

            }
        }

        public function getFilterRequest(?string $initialCondition = null) {

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
