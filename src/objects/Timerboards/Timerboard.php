<?php

    namespace Ridley\Objects\Timerboards;

    class Timerboard {

        private array $whiteList;

        public function __construct(
            private string $id,
            private string $type,
            private string $url,
            private string $token,
            private string $whiteListString,
            private int $timestamp,
            private int $corporationID,
            private string $corporationName,
            private ?int $allianceID,
            private ?string $allianceName,
            private bool $newlyGenerated = false,
            private $databaseConnection = null
        ) {

            if ($this->newlyGenerated) {
                $this->create();
            }

            $this->whiteList = json_decode($this->whiteListString, true);

        }

        private function create() {

            $insertStatement = $this->databaseConnection->prepare("INSERT INTO timerboards (id, type, url, token, whitelist, timestamp, corporationid, corporationname, allianceid, alliancename) VALUES (:id, :type, :url, :token, :whitelist, :timestamp, :corporationid, :corporationname, :allianceid, :alliancename)");
            $insertStatement->bindParam(":id", $this->id);
            $insertStatement->bindParam(":type", $this->type);
            $insertStatement->bindParam(":url", $this->url);
            $insertStatement->bindParam(":token", $this->token);
            $insertStatement->bindParam(":whitelist", $this->whiteListString);
            $insertStatement->bindParam(":timestamp", $this->timestamp);
            $insertStatement->bindParam(":corporationid", $this->corporationID);
            $insertStatement->bindParam(":corporationname", $this->corporationName);
            $insertStatement->bindParam(":allianceid", $this->allianceID);
            $insertStatement->bindParam(":alliancename", $this->allianceName);
            $insertStatement->execute();

        }

        public function render() {

            ?>

            <tr class="timerboard-entry" data-row-id="<?php echo $this->id; ?>" data-bs-toggle="modal" data-bs-target="#details-modal">
                <td><?php echo htmlspecialchars($this->type); ?></td>
                <td><?php echo htmlspecialchars($this->corporationName ?? ""); ?></td>
                <td><?php echo htmlspecialchars($this->allianceName ?? ""); ?></td>
                <td><?php echo htmlspecialchars(count($this->whiteList)); ?></td>
            </tr>

            <?php

        }

    }

?>
