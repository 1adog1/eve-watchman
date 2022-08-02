<?php

    namespace Ridley\Apis\Admin;

    class Api implements \Ridley\Interfaces\Api {

        private $availableRoles = [];
        private $databaseConnection;
        private $logger;
        private $configVariables;
        private $characterStats;
        private $userAuthorization;
        private $esiHandler;

        public function __construct(
            private \Ridley\Core\Dependencies\DependencyManager $dependencies
        ) {

            $this->databaseConnection = $this->dependencies->get("Database");
            $this->logger = $this->dependencies->get("Logging");
            $this->configVariables = $this->dependencies->get("Configuration Variables");
            $this->characterStats = $this->dependencies->get("Character Stats");
            $this->userAuthorization = $this->dependencies->get("Authorization Control");

            $this->esiHandler = new \Ridley\Objects\ESI\Handler(
                $this->databaseConnection,
                $this->userAuthorization->getAccessToken("Default", $this->characterStats["Character ID"])
            );

            require __DIR__ . "/../../registers/accessRoles.php";

            if (isset($_POST["Action"])) {

                if (
                    $_POST["Action"] == "Search"
                    and isset($_POST["Type"])
                    and isset($_POST["Term"])
                    and isset($_POST["Strict"])
                    and in_array($_POST["Strict"], ["true", "false"], true)
                ){

                    $this->getSearchResults($_POST["Type"], $_POST["Term"], $_POST["Strict"]);

                }
                elseif (
                    $_POST["Action"] == "Add_Group"
                    and isset($_POST["Type"])
                    and isset($_POST["ID"])
                    and isset($_POST["Name"])
                ){

                    $this->addGroup($_POST["Type"], $_POST["ID"], $_POST["Name"]);

                }
                elseif (
                    $_POST["Action"] == "Remove_Group"
                    and isset($_POST["Type"])
                    and isset($_POST["ID"])
                ){

                    $this->removeGroup($_POST["Type"], $_POST["ID"]);

                }
                elseif (
                    $_POST["Action"] == "Update_Group"
                    and isset($_POST["Type"])
                    and isset($_POST["ID"])
                    and isset($_POST["Change"])
                    and isset($_POST["Role"])
                ){

                    $this->updateGroup($_POST["Type"], $_POST["ID"], $_POST["Change"], $_POST["Role"]);

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

        private function registerRole(string $newRole) {

            $this->availableRoles[] = $newRole;

        }

        private function checkGroupExists($type, $id) {

            $checkQuery = $this->databaseConnection->prepare("SELECT id FROM access WHERE type=:type AND id=:id");
            $checkQuery->bindParam(":type", $type);
            $checkQuery->bindParam(":id", $id, \PDO::PARAM_INT);
            $checkQuery->execute();
            $checkData = $checkQuery->fetch();

            return !empty($checkData);

        }

        private function checkEntityExists($type, $id, $name) {

            $namesCall = $this->esiHandler->call(endpoint: "/universe/names/", ids: [$id], retries: 1);

            if ($namesCall["Success"]) {

                foreach ($namesCall["Data"] as $eachName) {

                    if ($eachName["category"] === strtolower($type) and $eachName["id"] === (int)$id and $eachName["name"] === htmlspecialchars_decode($name)) {

                        return true;

                    }

                }

                return false;

            }
            else {

                return false;

            }

        }

        private function getNamesFromIDs($ids, $type) {

            $namesCall = $this->esiHandler->call(endpoint: "/universe/names/", ids: $ids, retries: 1);

            if ($namesCall["Success"]) {

                foreach ($namesCall["Data"] as $eachName) {

                    if ($eachName["category"] == $type) {

                        $nameCombinations[$eachName["id"]] = htmlspecialchars($eachName["name"]);

                    }

                }

                return $nameCombinations;

            }
            else {

                return false;

            }

        }

        private function getSearchResults($type, $term, $strict) {

            $approvedTypes = ["Character", "Corporation", "Alliance"];

            if (in_array($type, $approvedTypes)) {

                if ($term !== "") {

                    $searchCall = $this->esiHandler->call(
                        endpoint: "/characters/{character_id}/search/",
                        character_id: $this->characterStats["Character ID"],
                        categories: [strtolower($type)],
                        search: $term,
                        strict: $strict,
                        retries: 1
                    );

                    if ($searchCall["Success"]) {

                        if (!empty($searchCall["Data"])) {

                            $combinedData = $this->getNamesFromIDs(array_slice($searchCall["Data"][strtolower($type)], 0, 1000), strtolower($type));

                            if ($combinedData !== false) {

                                $searchResults = [
                                    "Type" => $type,
                                    "Entities" => $combinedData
                                ];

                                echo json_encode($searchResults);

                            }
                            else {

                                header($_SERVER["SERVER_PROTOCOL"] . " 500 Internal Server Error");
                                trigger_error("A valid list of IDs from a search failed to convert to names.", E_USER_ERROR);

                            }

                        }
                        else {

                            header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");

                        }

                    }
                    else {

                        header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");

                    }

                }
                else {

                    header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");

                }

            }
            else {

                header($_SERVER["SERVER_PROTOCOL"] . " 400 Bad Request");
                trigger_error("Unapproved Search Type Requested.", E_USER_ERROR);

            }

        }

        private function addGroup($type, $id, $name) {

            if ($this->checkEntityExists($type, $id, $name)) {

                if (!$this->checkGroupExists($type, $id)) {

                    $creationQuery = $this->databaseConnection->prepare("INSERT INTO access (type, id, name, roles) VALUES (:type, :id, :name, :roles)");
                    $creationQuery->bindParam(":type", $type);
                    $creationQuery->bindParam(":id", $id, \PDO::PARAM_INT);
                    $creationQuery->bindParam(":name", $name);
                    $creationQuery->bindValue(":roles", json_encode([]));
                    $creationQuery->execute();

                    $this->logger->make_log_entry(logType: "Access Group Created", logDetails: "Created " . $type . " Group " . $name . " with ID " . $id . ".");

                    $addedGroup = new \Ridley\Objects\Admin\Groups\Eve($this->dependencies, $id, $name, $type);
                    $addedGroup->renderAccessPanel();

                }
                else {

                    header($_SERVER["SERVER_PROTOCOL"] . " 400 Bad Request");
                    trigger_error("A group that was requested to be added already exists.", E_USER_ERROR);

                }

            }
            else {

                header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
                trigger_error("The ID or Name of a group that was requested to be added does not exist.", E_USER_ERROR);

            }

        }

        private function removeGroup($type, $id) {

            if ($this->checkGroupExists($type, $id)) {

                $deletionQuery = $this->databaseConnection->prepare("DELETE FROM access WHERE type=:type AND id=:id");
                $deletionQuery->bindParam(":type", $type);
                $deletionQuery->bindParam(":id", $id, \PDO::PARAM_INT);
                $deletionQuery->execute();

                $this->logger->make_log_entry(logType: "Access Group Deleted", logDetails: "Deleted " . $type . " Group with ID " . $id . ".");

                echo json_encode(["Status" => "Success"]);

            }
            else {

                header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
                trigger_error("The group that was requested to be removed does not exist.", E_USER_ERROR);

            }

        }

        private function updateGroup($type, $id, $change, $role) {

            if (in_array($role, $this->availableRoles)) {

                $checkQuery = $this->databaseConnection->prepare("SELECT * FROM access WHERE type=:type AND id=:id");
                $checkQuery->bindParam(":type", $type);
                $checkQuery->bindParam(":id", $id, \PDO::PARAM_INT);
                $checkQuery->execute();
                $checkData = $checkQuery->fetch();

                if (!empty($checkData)) {

                    $oldRoles = array_unique(json_decode($checkData["roles"]));

                    switch ($change) {
                        case "Added":

                            $oldRoles[] = $role;
                            break;

                        case "Removed":

                            $searchKey = array_search($role, $oldRoles);
                            if ($searchKey !== false) {
                                unset($oldRoles[$searchKey]);
                            }
                            break;

                        default:

                            header($_SERVER["SERVER_PROTOCOL"] . " 400 Bad Request");
                            trigger_error("Invalid type of change was received for an access group.", E_USER_ERROR);

                    }

                    $newRoles = array_unique(array_values($oldRoles));

                    $updateQuery = $this->databaseConnection->prepare("UPDATE access SET roles=:roles WHERE type=:type AND id=:id");
                    $updateQuery->bindValue(":roles", json_encode($newRoles));
                    $updateQuery->bindParam(":type", $type);
                    $updateQuery->bindParam(":id", $id, \PDO::PARAM_INT);
                    $updateQuery->execute();

                    $this->logger->make_log_entry(logType: "Access Group Updated", logDetails: $type . " Group with ID " . $id . " - " . $role . " Role " . $change);

                    echo json_encode(["Status" => "Success"]);

                }
                else {

                    header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
                    trigger_error("The group for which a change was requested does not exist.", E_USER_ERROR);

                }

            }
            else {

                header($_SERVER["SERVER_PROTOCOL"] . " 400 Bad Request");
                trigger_error("A change was requested using an invalid role.", E_USER_ERROR);

            }

        }

    }

?>
