<?php

    namespace Ridley\Views\Admin;

    class Templates {
        
        protected function mainTemplate() {
            ?>
            
            <div class="row">
                <div class="col-md-2">
                    <div class="d-flex align-items-start bg-dark p-3 rounded">
                        <ul class="nav nav-pills flex-column w-100" id="admin-nav" role="tablist" aria-orientation="vertical">
                            <div class="h5 text-muted mb-0">Site Administration</div>
                            <hr class="text-light">
                            <li class="nav-item" role="presentation">
                                <a href="#" class="nav-link active" id="admin-permissions-tab" data-bs-toggle="tab" data-bs-target="#admin-permissions" type="button" role="tab" aria-controls="admin-permissions" aria-selected="false">Permissions</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-10">
                    <div class="tab-content" id="admin-panes">
                        <div class="tab-pane fade show active" id="admin-permissions" role="tabpanel" aria-labelledby="admin-permissions-tab">
                            <div class="row">
                                <div class="col-lg-4" id="search-column">
                                    <?php $this->searchTemplate(); ?>
                                </div>
                                <div class="col-lg-8" id="groups-column">
                                    <?php $this->groupsTemplate(); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php
        }
        
        protected function searchTemplate() {
            
            if ($this->configVariables["Auth Type"] !== "Neucore") {
                ?>
                
                    <h3 class="text-light">Add Groups</h3>
                
                    <div class="mt-3">
                        <label class="form-label text-light" for="name-selection">Name</label>
                        <input type="text" class="form-control" name="name-selection" id="name-selection">
                    </div>
                    
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" value="True" id="strict-selection">
                        <label class="form-check-label text-light" for="strict-selection">Strict Search</label>
                    </div>
                    
                    <div class="mt-3">
                        <label class="form-label text-light" for="type-selection">Type</label>
                        <select type="text" class="form-select" name="type-selection" id="type-selection">
                            <option>Character</option>
                            <option>Corporation</option>
                            <option>Alliance</option>
                        </select>
                    </div>
                    
                    <div class="d-grid mt-4">
                        <input class="btn btn-success" id="search-button" type="button" value="Search">
                        
                        <div id="search-spinner" hidden>
                            <div class="d-flex justify-content-center">
                                <div class="spinner-border text-light"></div>
                            </div>
                        </div>
                        
                    </div>
                    
                    <h4 class="text-white mt-3">Search Results</h4>
                    
                    <div class="mt-3" id="group-search-results">
                    
                    </div>
                
                <?php
            }
        }
        
        protected function groupsTemplate() {
            
            foreach ($this->model->getGroups() as $groupName => $subGroups) {
                
                ?>
                
                <h3 class="text-light" id="<?php echo htmlspecialchars(strtolower($groupName)); ?>-group-header"><?php echo htmlspecialchars($groupName); ?> Groups</h3> 
                
                <div id="<?php echo htmlspecialchars(strtolower($groupName)); ?>-group-list">
                
                    <?php
                    
                    foreach ($subGroups as $groupID => $groupClass) {
                        
                        $groupClass->renderAccessPanel();
                        
                    }
                    
                    ?>
                
                </div>
                
                <?php
            }
            
        }
        
        protected function metaTemplate() {
            ?>
            
            <title>Admin</title>
            
            <script src="/resources/js/Admin.js"></script>
            
            <?php
        }
        
    }

    class View extends Templates implements \Ridley\Interfaces\View {
        
        protected $model;
        protected $controller;
        protected $configVariables;
        
        public function __construct(
            private \Ridley\Core\Dependencies\DependencyManager $dependencies
        ) {
            
            $this->model = $this->dependencies->get("Model");
            $this->controller = $this->dependencies->get("Controller");
            $this->configVariables = $this->dependencies->get("Configuration Variables");
            
        }
        
        public function renderContent() {
            
            $this->mainTemplate();
            
        }
        
        public function renderMeta() {
            
            $this->metaTemplate();
            
        }
        
    }

?>