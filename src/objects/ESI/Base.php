<?php

    namespace Ridley\Objects\ESI;

    use Ridley\Core\Exceptions\ESIException;

    class Base {

        private $defaultSuccessCodes = ["200", "204"];
        private $defaultCompatibilityDate = "2026-06-01";

        private function hashRequest(string $url, string $method, ?array $payload, ?string $accessToken) {

            $hashingArray = [
                "URL" => $url,
                "Method" => $method,
                "Payload" => $payload,
                "Subject" => $this->unpackAccessToken($accessToken)["Payload"]["sub"] ?? null
            ];

            return hash("sha256", json_encode($hashingArray, JSON_UNESCAPED_SLASHES));

        }

        private function unpackAccessToken(?string $accessToken) {

            if (isset($accessToken)) {

                $accessArray = explode(".", $accessToken);
                $accessHeader = json_decode(base64_decode($accessArray[0]), true);
                $accessPayload = json_decode(base64_decode($accessArray[1]), true);
                $accessSignature = $accessArray[2];

            }

            return [
                "Token" => $accessToken ?? null,
                "Header" => $accessHeader ?? null,
                "Payload" => $accessPayload ?? null,
                "Signature" => $accessSignature ?? null
            ];

        }

        private function cleanupCache() {

            $currentTime = time();

            $deleteCacheEntries = $this->databaseConnection->prepare("DELETE FROM esicache WHERE expiration <= :current_time");
            $deleteCacheEntries->bindParam(":current_time", $currentTime);
            $deleteCacheEntries->execute();

        }

        private function checkCache(string $endpoint, string $hash) {

            $cacheRequest = $this->databaseConnection->prepare("SELECT response FROM esicache WHERE endpoint=:endpoint AND hash=:hash AND expiration > :currenttime");
            $cacheRequest->bindParam(":endpoint", $endpoint);
            $cacheRequest->bindParam(":hash", $hash);
            $cacheRequest->bindValue(":currenttime", time());

            if ($cacheRequest->execute()) {

                $cacheData = $cacheRequest->fetch();

                if (!empty($cacheData)) {

                    return json_decode($cacheData["response"], true);

                }
                else {

                    return false;

                }

            }
            else {

                throw new ESIException("Failed to query ESI Cache.", 1001);

            }

        }

        private function populateCache(string $endpoint, string $hash, array $response, int $expires) {

            $response_to_save = json_encode($response, JSON_UNESCAPED_SLASHES);

            $insertSession = $this->databaseConnection->prepare("INSERT INTO esicache (endpoint, hash, expiration, response) VALUES (:endpoint, :hash, :expiration, :response)");
            $insertSession->bindParam(":endpoint", $endpoint);
            $insertSession->bindParam(":hash", $hash);
            $insertSession->bindParam(":expiration", $expires);
            $insertSession->bindParam(":response", $response_to_save);

            $insertSession->execute();

        }

        private function buildContext(string $method, ?array $payload, ?string $accessToken, ?string $compatibilityDate) {

            $compatibilityDateHeader = $compatibilityDate ?? $this->defaultCompatibilityDate;

            $context = [
                "http" => [
                    "ignore_errors" => true,
                    "header" => [
                        "accept: application/json",
                        "X-Compatibility-Date: " . $compatibilityDateHeader,
                        "X-User-Agent: " . $this->userAgent
                    ],
                    "method" => $method
                ]
            ];

            if (!empty($payload)) {
                $context["http"]["header"][] = "Content-Type: application/json";
                $context["http"]["content"] = json_encode($payload);
            }
            if (!empty($accessToken)) {
                $context["http"]["header"][] = ("Authorization: Bearer " . $accessToken);
            }

            return $context;

        }

        private function parseHeaders(array $headerList) {

            $statusCode = (int)(explode(" ", $headerList[0])[1]);

            $parsedHeaders = ["Status Code" => $statusCode, "Headers" => []];

            foreach (array_slice($headerList, 1) as $eachHeader) {

                $splitHeader = explode(": ", $eachHeader, 2);
                $headerTitle = $splitHeader[0];
                $headerData = $splitHeader[1];

                $parsedHeaders["Headers"][$headerTitle] = $headerData;

            }

            return $parsedHeaders;

        }

        private function checkForSuccess($responseCode, $customSuccessCodes) {

            $successCodes = array_unique(array_merge($this->defaultSuccessCodes, $customSuccessCodes));

            return in_array($responseCode, $successCodes);

        }

        protected function makeRequest(
            string $endpoint,
            string $url,
            string $method = "GET",
            ?array $payload = null,
            ?string $accessToken = null,
            ?string $compatibilityDate = null,
            bool $expectResponse = true,
            array $successCodes = [],
            int $cacheTime = 0,
            int $retries = 0
        ) {

            $responseData = ["Success" => false, "Data" => [], "Status Code" => null, "Headers" => null];

            $this->cleanupCache();

            $cacheCheck = $this->checkCache(
                $endpoint,
                $this->hashRequest($url, $method, $payload, $accessToken)
            );

            if ($cacheCheck !== false) {

                $responseData = $cacheCheck;

                return $responseData;

            }
            else {

                for ($remainingRetries = $retries; $remainingRetries >= 0; $remainingRetries--) {

                    $requestContext = stream_context_create(
                        $this->buildContext($method, $payload, $accessToken, $compatibilityDate)
                    );

                    $request = file_get_contents(
                        filename: $url,
                        context: $requestContext
                    );

                    $responseHeaders = $this->parseHeaders($http_response_header);
                    $responseData["Status Code"] = $responseHeaders["Status Code"];
                    $responseData["Headers"] = $responseHeaders["Headers"];

                    if ($this->checkForSuccess($responseData["Status Code"], $successCodes)) {

                        $responseData["Success"] = true;

                        if ($expectResponse) {

                            try {
                                $responseData["Data"] = json_decode(
                                    json: $request, 
                                    associative: true,
                                    flags: JSON_THROW_ON_ERROR
                                );
                            }
                            catch (\Exception $error) {}

                            if (isset($responseData["Headers"]["Expires"])) {

                                $expiry = strtotime($responseData["Headers"]["Expires"]);

                            }
                            else {

                                $expiry = time() + $cacheTime;

                            }

                            $this->populateCache(
                                $endpoint,
                                $this->hashRequest($url, $method, $payload, $accessToken),
                                $responseData,
                                $expiry
                            );

                        }

                        return $responseData;

                    }
                    elseif ($remainingRetries <= 0) {

                        $responseData["Success"] = false;

                        if ($expectResponse) {

                            try {
                                $responseData["Data"] = json_decode(
                                    json: $request, 
                                    associative: true,
                                    flags: JSON_THROW_ON_ERROR
                                );
                            }
                            catch (\Exception $error) {}

                        }

                        return $responseData;

                    }

                }

            }

        }

    }

?>
