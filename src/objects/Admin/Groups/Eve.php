<?php

    namespace Ridley\Objects\Admin\Groups;

    class Eve {
        
        private $availableRoles = [];
        private $databaseConnection;
        private $logger;
        
        function __construct(
            private $dependencies,
            private $id,
            private $name,
            private $type
        ) {
            
            $this->databaseConnection = $this->dependencies->get("Database");
            $this->logger = $this->dependencies->get("Logging");
            
            $this->initialSetup();
        }
        
        private function initialSetup() {
            
            require __DIR__ . "/../../../registers/accessRoles.php";
            
        }
        
        private function registerRole(string $newRole) {
            
            $this->availableRoles[] = $newRole;
            
        }
        
        public function getAccessRoles() {
            
            $checkQuery = $this->databaseConnection->prepare("SELECT roles FROM access WHERE type=:type AND id=:id");
            $checkQuery->bindParam(":type", $this->type);
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
            
            <div class="card bg-dark text-white mt-3 mb-3 access-card" data-type="<?php echo htmlspecialchars($this->type); ?>" data-group="<?php echo htmlspecialchars($this->id); ?>">
                <h4 class="card-header"><?php echo htmlspecialchars($this->name); ?></h4>
                <div class="card-body">
                    <?php
                    
                    foreach ($this->availableRoles as $eachRole) {
                        
                        $showCheck = in_array($eachRole, $groupRoles) ? "checked" : "";
                        $boxID = strtolower($this->type) . "-group-" . $this->id . "-" . str_replace(" ", "_", strtolower($eachRole));
                        
                        ?>
                        
                        <div class="form-check form-switch form-check-inline">
                            <input class="form-check-input acl-switch" data-type="<?php echo htmlspecialchars($this->type); ?>" data-group="<?php echo htmlspecialchars($this->id); ?>" data-role="<?php echo htmlspecialchars($eachRole); ?>" type="checkbox" id="<?php echo htmlspecialchars($boxID); ?>" <?php echo $showCheck; ?>>
                            <label class="form-check-label" for="<?php echo htmlspecialchars($boxID); ?>"><?php echo htmlspecialchars($eachRole); ?></label>
                        </div>
                        
                        <?php
                        
                    }
                    
                    ?>
                    <button class="btn btn-sm btn-outline-danger acl-delete-button" data-type="<?php echo htmlspecialchars($this->type); ?>" data-group="<?php echo htmlspecialchars($this->id); ?>">Delete Group</button>
                </div>
            </div>
            
            <?php
        }
        
        public function updateAccessRoles($newRoles) {
            
            $updateQuery = $this->databaseConnection->prepare("UPDATE access SET roles=:roles WHERE type=:type AND id=:id");
            $updateQuery->bindValue(":roles", json_encode($newRoles));
            $updateQuery->bindParam(":type", $this->type);
            $updateQuery->bindParam(":id", $this->id, \PDO::PARAM_INT);
            $updateQuery->execute();
            
        }
        
    }

?>