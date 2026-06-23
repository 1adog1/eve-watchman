<?php

    namespace Ridley\Apis\Logs;

    use Ridley\Core\Exceptions\UserInputException;

    class Api implements \Ridley\Interfaces\Api {

        private $databaseConnection;

        public function __construct(
            private \Ridley\Core\Dependencies\DependencyManager $dependencies
        ) {

            $this->databaseConnection = $this->dependencies->get("Database");

            if (isset($_POST["Action"])) {

                if ($_POST["Action"] == "Get_Row" and isset($_POST["ID"])) {

                    $this->getRow($_POST["ID"]);

                }
                else {

                    throw new UserInputException(
                        inputs: ["Action", "Secondary Arguments"], 
                        expected_values: ["A valid action command", "The action's arguments"], 
                        hard_coded_inputs: true,
                        value_missing: true
                    );

                }

            }
            else {

                throw new UserInputException(
                    inputs: "Action", 
                    expected_values: "An action command", 
                    hard_coded_inputs: true,
                    value_missing: true
                );

            }

        }

        private function getRow($rowID) {

            $entryQuery = $this->databaseConnection->prepare("SELECT * FROM logs WHERE id=:id");
            $entryQuery->bindParam(":id", $rowID, \PDO::PARAM_INT);
            $entryQuery->execute();

            $entryResult = $entryQuery->fetch();

            if (!is_null($entryResult) and $entryResult !== false) {

                echo json_encode($entryResult);

            }
            else {

                throw new UserInputException(
                    inputs: "Row ID", 
                    expected_values: "A valid row ID", 
                    hard_coded_inputs: true
                );

            }

        }

    }

?>
