<?php

    namespace Ridley\Objects\ESI;

    use Ridley\Core\Exceptions\ESIException;

    class Handler extends Methods {

        protected $esiURL = "https://esi.evetech.net/";
        protected $userAgent;
        protected $methodList = [];

        function __construct(
            protected $databaseConnection,
            protected $versionVariables,
            protected $accessToken = null
        ) {

            require __DIR__ . "/../../registers/esiMethods.php";
            $this->buildUserAgent();

        }

        private function buildUserAgent() {

            if (!empty($this->versionVariables["App Name"])) {

                $this->userAgent = (
                    $this->versionVariables["App Name"] . "/" . $this->versionVariables["App Version"]
                    . " (" . $this->versionVariables["Client Contact Info"] . "; +" . $this->versionVariables["App Github"] . ") "
                    . "Project-Overhaul/" . $this->versionVariables["Overhaul Version"]
                    . " (+" . $this->versionVariables["Overhaul Github"] . ")"
                );

            }
            else {

                $this->userAgent = (
                    "Project-Overhaul/" . $this->versionVariables["Overhaul Version"]
                    . " (" . $this->versionVariables["Client Contact Info"] . "; +" . $this->versionVariables["Overhaul Github"] . ") "
                );

            }

        }

        public function updateAccessToken(string $accessToken) {
            $this->accessToken = $accessToken;
        }

        public function call(string $endpoint, mixed ...$arguments) {

            if (isset($this->methodList[$endpoint])) {

                $method = $this->methodList[$endpoint]["Name"];
                $requiredArguments = $this->methodList[$endpoint]["Required Arguments"];

                if (
                    empty(
                        array_diff_key(
                            array_flip($requiredArguments),
                            $arguments
                        )
                    )
                ) {

                    return $this->$method($arguments);

                }
                else {

                    throw new ESIException("Failed to pass required arguments for the " . $endpoint . " endpoint.", 1101);

                }

            }
            else {

                throw new ESIException("The requested endpoint " . $endpoint . " does not have a registered method.", 1102);

            }

        }

        protected function register(
            string $endpoint,
            string $method,
            array $requiredArguments
        ) {

            $this->methodList[$endpoint] = ["Name" => $method, "Required Arguments" => $requiredArguments];

        }

    }

?>
