<?php

    namespace Ridley\Objects\Admin\Groups;

    class Neucore {
        
        private $availableRoles = [];
        private $databaseConnection;
        private $logger;
        
        function __construct(
            private $dependencies,
            private $id,
            private $name
        ) {
            
            $this->databaseConnection = $this->dependencies->get("Database");
            $this->logger = $this->dependencies->get("Logging");
            
            $this->initialSetup();
        }
        
        private function initialSetup() {
            
            require __DIR__ . "/../../../registers/accessRoles.php";
            
            $checkQuery = $this->databaseConnection->prepare("SELECT * FROM access WHERE type=:type AND id=:id");
            $checkQuery->bindValue(":type", "Neucore");
            $checkQuery->bindParam(":id", $this->id, \PDO::PARAM_INT);
            $checkQuery->execute();
            $checkData = $checkQuery->fetchAll();
            
            if (empty($checkData)) {
                
                $creationQuery = $this->databaseConnection->prepare("INSERT INTO access (type, id, name, roles) VALUES (:type, :id, :name, :roles)");
                $creationQuery->bindValue(":type", "Neucore");
                $creationQuery->bindParam(":id", $this->id, \PDO::PARAM_INT);
                $creationQuery->bindParam(":name", $this->name);
                $creationQuery->bindValue(":roles", json_encode([]));
                $creationQuery->execute();
                
                $this->logger->make_log_entry(logType: "Access Group Created", logDetails: "Created Neucore Group " . $this->name . " with ID " . $this->id . ".");                
            }
            
        }
        
        private function registerRole(string $newRole) {
            
            $this->availableRoles[] = $newRole;
            
        }
        
        public function getAccessRoles() {
            
            $checkQuery = $this->databaseConnection->prepare("SELECT roles FROM access WHERE type=:type AND id=:id");
            $checkQuery->bindValue(":type", "Neucore");
            $checkQuery->bindParam(":id", $this->id, \PDO::PARAM_INT);
            $checkQuery->execute();
            $checkData = $checkQuery->fetch();
            
            if (!empty($checkData)) {
                
                return json_decode($checkData["roles"]);
                
            }
            
        }
        
        public function renderAccessPanel() {
            
            $groupRoles = $this->getAccessRoles();
            
            ?>
            
            <div class="card bg-dark text-white mt-3 mb-3">
                <h4 class="card-header"><?php echo htmlspecialchars($this->name); ?></h4>
                <div class="card-body">
                    <?php
                    
                    foreach ($this->availableRoles as $eachRole) {
                        
                        $showCheck = in_array($eachRole, $groupRoles) ? "checked" : "";
                        $boxID = "neucore-group-" . $this->id . "-" . str_replace(" ", "_", strtolower($eachRole));
                        
                        ?>
                        
                        <div class="form-check form-switch form-check-inline">
                            <input class="form-check-input acl-switch" data-type="Neucore" data-group="<?php echo htmlspecialchars($this->id); ?>" data-role="<?php echo htmlspecialchars($eachRole); ?>" type="checkbox" id="<?php echo htmlspecialchars($boxID); ?>" <?php echo $showCheck; ?>>
                            <label class="form-check-label" for="<?php echo htmlspecialchars($boxID); ?>"><?php echo htmlspecialchars($eachRole); ?></label>
                        </div>
                        
                        <?php
                        
                    }
                    
                    ?>
                </div>
            </div>
            
            <?php
        }
        
        public function updateAccessRoles($newRoles) {
            
            $updateQuery = $this->databaseConnection->prepare("UPDATE access SET roles=:roles WHERE type=:type AND id=:id");
            $updateQuery->bindValue(":roles", json_encode($newRoles));
            $updateQuery->bindValue(":type", "Neucore");
            $updateQuery->bindParam(":id", $this->id, \PDO::PARAM_INT);
            $updateQuery->execute();
            
        }
        
    }

?>