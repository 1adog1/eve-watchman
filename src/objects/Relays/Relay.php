<?php

    namespace Ridley\Objects\Relays;

    class Relay {

        private string $server;
        private string $channel;
        private array $whiteList;

        public function __construct(
            private string $id,
            private string $type,
            private string $channelid,
            private string $serverid,
            private string $url,
            private string $pingType,
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

            $this->getPlatformNames();

        }

        private function getPlatformNames() {

            $serverQuery = $this->databaseConnection->prepare("SELECT name FROM servers WHERE id=:id AND type=:type;");
            $serverQuery->bindParam(":id", $this->serverid);
            $serverQuery->bindParam(":type", $this->type);

            $serverQuery->execute();
            $serverResults = $serverQuery->fetchAll();

            foreach ($serverResults as $eachServer) {

                $this->server = $eachServer["name"];

            }

            $channelQuery = $this->databaseConnection->prepare("SELECT name FROM channels WHERE id=:id AND type=:type;");
            $channelQuery->bindParam(":id", $this->channelid);
            $channelQuery->bindParam(":type", $this->type);

            $channelQuery->execute();
            $channelResults = $channelQuery->fetchAll();

            foreach ($channelResults as $eachChannel) {

                $this->channel = $eachChannel["name"];

            }

        }

        private function create() {

            $insertStatement = $this->databaseConnection->prepare("INSERT INTO relays (id, type, channelid, serverid, url, pingtype, whitelist, timestamp, corporationid, corporationname, allianceid, alliancename) VALUES (:id, :type, :channelid, :serverid, :url, :pingtype, :whitelist, :timestamp, :corporationid, :corporationname, :allianceid, :alliancename)");
            $insertStatement->bindParam(":id", $this->id);
            $insertStatement->bindParam(":type", $this->type);
            $insertStatement->bindParam(":channelid", $this->channelid);
            $insertStatement->bindParam(":serverid", $this->serverid);
            $insertStatement->bindParam(":url", $this->url);
            $insertStatement->bindParam(":pingtype", $this->pingType);
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

            <tr class="relay-entry" data-row-id="<?php echo $this->id; ?>" data-bs-toggle="modal" data-bs-target="#details-modal">
                <td><?php echo htmlspecialchars($this->type); ?></td>
                <td><?php echo htmlspecialchars($this->server); ?></td>
                <td><?php echo htmlspecialchars($this->channel); ?></td>
                <td><?php echo htmlspecialchars($this->corporationName ?? ""); ?></td>
                <td><?php echo htmlspecialchars($this->allianceName ?? ""); ?></td>
                <td><?php echo htmlspecialchars(count($this->whiteList)); ?></td>
            </tr>

            <?php

        }

    }

?>
