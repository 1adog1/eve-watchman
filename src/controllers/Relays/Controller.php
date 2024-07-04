<?php

    namespace Ridley\Controllers\Relays;

    class Controller implements \Ridley\Interfaces\Controller {

        private $databaseConnection;
        private $logger;
        private $accessRoles;
        private $characterStats;

        public $notificationTypes = [
            "Upwell_Attacks" => [
                "StructureDestroyed" => ["Name" => "Structure Destroyed"],
                "StructureLostArmor" => ["Name" => "Structure Lost Armor"],
                "StructureLostShields" => ["Name" => "Structure Lost Shields"],
                "StructureUnderAttack" => ["Name" => "Structure Under Attack", "Tooltip" => "Sent when an upwell structure drops below 95% shield, or 100% armor / structure."]
            ],
            "Upwell_Management" => [
                "StructureAnchoring" => ["Name" => "Structure Anchoring"],
                "StructureFuelAlert" => ["Name" => "Structure Fuel Alert", "Tooltip" => "Sent when a citadel drops below 24 hours of fuel remaining."],
                "StructureOnline" => ["Name" => "Structure Online"],
                "StructureUnanchoring" => ["Name" => "Structure Unanchoring"],
                "StructureServicesOffline" => ["Name" => "Structure Services Offline"],
                "StructureWentHighPower" => ["Name" => "Structure High Power"],
                "StructureWentLowPower" => ["Name" => "Structure Low Power"],
                "StructureImpendingAbandonmentAssetsAtRisk" => ["Name" => "Structure Impending Abandonment", "Tooltip" => "Sent when an upwell structure belonging to the corporation is about to become abandoned."],
                "StructuresReinforcementChanged" => ["Name" => "Structure Reinforcement Changed"],
                "OwnershipTransferred" => ["Name" => "Structure Ownership Transfer", "Tooltip" => "Sent when an upwell structure or customs office is transferred to or from the corporation, as well as when the corporation acquires a sovereignty structure."]
            ],
            "Moon_Detonations" => [
                "MoonminingLaserFired" => ["Name" => "Moon Manually Detonated"],
                "MoonminingAutomaticFracture" => ["Name" => "Moon Automatically Detonated"]
            ],
            "Moon_Management" => [
                "MoonminingExtractionCancelled" => ["Name" => "Moon Extraction Cancelled"],
                "MoonminingExtractionFinished" => ["Name" => "Moon Extraction Finished"],
                "MoonminingExtractionStarted" => ["Name" => "Moon Extraction Started"]
            ],
            "Sov_Attacks" => [
                "EntosisCaptureStarted" => ["Name" => "Entosis Capture Started"],
                "SovCommandNodeEventStarted" => ["Name" => "Command Node Event Started"],
                "SovStructureReinforced" => ["Name" => "Sovereignty Structure Reinforced"],
                "SovStructureDestroyed" => ["Name" => "Sovereignty Structure Destroyed"]
            ],
            "Sov_Management" => [
                "SovAllClaimAquiredMsg" => ["Name" => "Sovereignty Claim Acquired", "Tooltip" => "Sent when the alliance acquires a Territorial Claim Unit."],
                "SovAllClaimLostMsg" => ["Name" => "Sovereignty Claim Lost", "Tooltip" => "Sent when the alliance loses a Territorial Claim Unit."],
                "SovStructureSelfDestructRequested" => ["Name" => "Sovereignty Structure Self Destruct Requested"],
                "SovStructureSelfDestructFinished" => ["Name" => "Sovereignty Structure Self Destruct Finished"],
                "SovStructureSelfDestructCancel" => ["Name" => "Sovereignty Structure Self Destruct Cancelled"]
            ],
            "Skyhook_TEMP" => [
                "unknown notification type (281)" => ["Name" => "Skyhook Online"],
                "unknown notification type (282)" => ["Name" => "Skyhook Reinforced"],
                "unknown notification type (283)" => ["Name" => "Skyhook Under Attack", "Tooltip" => "Sent when an orbital skyhook drops below 95% shield, or 100% armor / structure."],
                "unknown notification type (285)" => ["Name" => "Skyhook Anchoring"]
            ],
            "Customs_Office" => [
                "OrbitalAttacked" => ["Name" => "Customs Office Under Attack"],
                "OrbitalReinforced" => ["Name" => "Customs Office Reinforced"]
            ],
            "Starbase_Attack" => [
                "TowerAlertMsg" => ["Name" => "Tower Under Attack", "Tooltip" => "Sent when any Tower or Starbase Component belonging to the corporation comes under attack. Has a one hour cooldown after being posted for a specific moon."]
            ],
            "Starbase_Management" => [
                "TowerResourceAlertMsg" => ["Name" => "Tower Fuel Alert", "Tooltip" => "Sent once per hour whilst a tower has less than 24 hours of fuel remaining."],
                "AllAnchoringMsg" => ["Name" => "Tower Anchoring", "Tooltip" => "Sent when a tower belonging to the corporation begins anchoring, or when a tower begins anchoring in a system that the corporation owns the Territorial Claim Unit for."]
            ], 
            "Corporation_Management" => [
                "CorpNewCEOMsg" => ["Name" => "CEO Retired", "Tooltip" => "Sent when a CEO manually retires and names their successor."],
                "CorpVoteCEORevokedMsg" => ["Name" => "CEO Vote Called", "Tooltip" => "Sent only to a CEO when they lose their privileges due to a CEO vote being initiated."],
                "CorpTaxChangeMsg" => ["Name" => "Tax Rate Changed"]
            ],
            "Corporation_Shareholder_Events" => [
                "CorpVoteMsg" => ["Name" => "Vote Called"],
                "CorpNewsMsg" => ["Name" => "Vote Implemented", "Tooltip" => "Sent when certain vote types (Creation of Shares and Expulsion of a Shareholder) are implemented by the CEO."]
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
