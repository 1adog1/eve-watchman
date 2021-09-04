<?php

    namespace Ridley\Views\Logs;

    class Templates {
        
        protected function mainTemplate() {
            
            $actorValue = isset($_POST["actor-selection"]) ? htmlspecialchars($_POST["actor-selection"]) : "";
            $startDateValue = isset($_POST["date-start"]) ? htmlspecialchars($_POST["date-start"]) : "";
            $endDateValue = isset($_POST["date-end"]) ? htmlspecialchars($_POST["date-end"]) : "";
            
            ?>
            <div class="small text-end text-white fst-italic fw-bold"><?php $this->countingTemplate(); ?></div>
            <div class="row">
                <div class="col-lg-3">
                
                    <h3 class="text-light">Filter Log Entries</h3>
                
                    <form method="post">
                        
                        <div class="mt-4">
                            <label class="form-label text-light" for="actor-selection">Actor</label>
                            <input type="text" class="form-control" name="actor-selection" id="actor-selection" value="<?php echo $actorValue; ?>">
                        </div>
                        
                        <div class="mt-4">
                            <label class="form-label text-light" for="page-selection">Page</label>
                            <select type="text" class="form-select" name="page-selection" id="page-selection">
                                <option></option>
                                <?php
                                    
                                    $this->pagesTemplate();
                                    
                                ?>
                            </select>
                        </div>
                        
                        <label class="form-label text-light mt-4">Date</label>
                        <div class="form-floating">
                            <input type="date" class="form-control" name="date-start" id="date-start" value="<?php echo $startDateValue; ?>">
                            <label for="date-start">Start Date</label>
                        </div>
                        <div class="form-floating mt-2">
                            <input type="date" class="form-control" name="date-end" id="date-end" value="<?php echo $endDateValue; ?>">
                            <label for="date-end">End Date</label>
                        </div>
                        
                        <label class="form-label text-light mt-4">Types</label>
                            <?php
                                
                                $this->typesTemplate();
                                
                            ?>
                        <div class="d-grid mt-4">
                            <input class="btn btn-success" type="submit" value="Filter">
                        </div>
                        
                        <div class="d-flex justify-content-center mt-4">
                            <div class="btn-group" role="group">
                            <?php
                                
                                $this->paginationTemplate();
                                
                            ?>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-lg-9">
                    <table class="table table-dark table-hover align-middle text-wrap">
                        <thead class="p-4">
                            <tr class="align-middle">
                                <th scope="col" style="width: 25%;">Timestamp</th>
                                <th scope="col" style="width: 25%;">Type</th>
                                <th scope="col" style="width: 20%;">Actor</th>
                                <th scope="col" style="width: 30%;">Page</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            
                                foreach ($this->model->queryRows() as $eachRow) {
                                    
                                    $this->rowTemplate($eachRow["id"], $eachRow["timestamp"], $eachRow["type"], $eachRow["actor"], $eachRow["page"]);
                                    
                                }
                            
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="modal fade" id="log-modal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content bg-dark text-white border-secondary">
                        <div class="modal-header border-secondary">
                            <h5 class="modal-title w-50">Log Entry #<span id="modal-row-id"></span></h5>
                            <div id="modal-timing" class="me-4 w-100 text-end">
                                <div id="modal-row-timestamp"></div>
                                <div id="modal-row-time-since" class="small"></div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div id="modal-spinner">
                                <div class="d-flex justify-content-center" >
                                    <div class="spinner-border text-secondary" style="width: 75px; height: 75px;">
                                    </div>
                                </div>
                            </div>
                            <div class="alert alert-danger fw-bold" id="modal-error">
                                
                                An error occurred while trying to get this log entry! Try again?
                                
                            </div>
                            <div id="modal-data">
                            
                                <div class="row">
                                    <div class="col-lg-4 mt-1">
                                    
                                        <div class="card bg-dark text-white border-secondary">
                                            <div class="card-body">
                                                <h6 class="card-title fw-bold">Actor</h6>
                                                <p class="card-text" id="modal-row-actor"></p>
                                            </div>
                                        </div>
                                        
                                    </div>
                                    <div class="col-lg-4 mt-1">
                                    
                                        <div class="card bg-dark text-white border-secondary">
                                            <div class="card-body">
                                                <h6 class="card-title fw-bold">True IP</h6>
                                                <p class="card-text" id="modal-row-true-ip"></p>
                                            </div>
                                        </div>
                                        
                                    </div>
                                    <div class="col-lg-4 mt-1">
                                    
                                        <div class="card bg-dark text-white border-secondary">
                                            <div class="card-body">
                                                <h6 class="card-title fw-bold">Forwarded IP</h6>
                                                <p class="card-text" id="modal-row-forwarded-ip"></p>
                                            </div>
                                        </div>
                                        
                                    </div>
                                </div>
                                
                                <div class="row mt-3">
                                    <div class="col-lg-4 mt-1">
                                    
                                        <div class="card bg-dark text-white border-secondary">
                                            <div class="card-body">
                                                <h6 class="card-title fw-bold">Type</h6>
                                                <p class="card-text" id="modal-row-type"></p>
                                            </div>
                                        </div>
                                
                                    </div>
                                    <div class="col-lg-8 mt-1">
                                    
                                        <div class="card bg-dark text-white border-secondary">
                                            <div class="card-body">
                                                <h6 class="card-title fw-bold">Page</h6>
                                                <p class="card-text" id="modal-row-page"></p>
                                            </div>
                                        </div>
                                
                                    </div>
                                </div>
                                
                                <div class="row mt-3">
                                    <div class="col-12 mt-1">
                                    
                                        <div class="card bg-dark text-white border-secondary">
                                            <div class="card-body">
                                                <h6 class="card-title fw-bold">Details</h6>
                                                <p class="card-text" id="modal-row-details"></p>
                                            </div>
                                        </div>
                                
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php
        }
        
        protected function countingTemplate() {
            
            $rowCount = $this->model->getRowCount();
            $currentPage = $this->controller->getPageNumber();
            $pageStart = min($rowCount, ((100 * $currentPage) + 1));
            $pageEnd = min($rowCount, (100 * ($currentPage + 1)));
            ?>
            
                <div class="small text-end text-white fw-bold fst-italic mb-2"><?php echo $pageStart; ?> - <?php echo $pageEnd; ?> of <?php echo $rowCount; ?></div>
                
            <?php
        }
        
        protected function pagesTemplate() {
            
            foreach ($this->pageList as $eachID => $eachName) {
                
                $isSelected = (isset($_POST["page-selection"]) and $eachID === $_POST["page-selection"]) ? "selected" : "";
                ?>
                
                    <option value="<?php echo htmlspecialchars($eachID); ?>" <?php echo $isSelected; ?>><?php echo htmlspecialchars($eachName); ?></option>
                
                <?php
                
            }
            
        }
        
        protected function typesTemplate() {
            
            foreach ($this->typeList as $eachID => $eachType) {
                
                $idName = ("type-" . $eachID);
                $isActive = (isset($_POST["type-" . $eachID]) and $_POST["type-" . $eachID] === "true") ? "checked" : "";
                ?>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" value="true" name="<?php echo $idName; ?>" id="<?php echo $idName; ?>" <?php echo $isActive; ?>>
                        <label class="form-check-label text-white" for="<?php echo $idName; ?>"><?php echo $eachType["Name"]; ?></label>
                    </div>
                <?php
                
            }
            
        }
        
        protected function paginationTemplate() {
            
            $rowCount = $this->model->getRowCount();
            $currentPage = $this->controller->getPageNumber() + 1;
            $maxPage = max(1, ceil($rowCount / 100));
            $startPage = min($maxPage, max(1, $currentPage - 2));
            $endPage = min($maxPage, max(1, $currentPage + 2));
            $backButtonDisabled = ($currentPage == 1) ? "disabled" : "";
            $nextButtonDisabled = ($currentPage == $maxPage) ? "disabled" : "";
            ?>
                
                <button class="btn btn-dark pb-2" type="submit" name="page" value="1" <?php echo $backButtonDisabled; ?>><i class="bi-chevron-bar-left"></i></button>
                <button class="btn btn-dark pb-2" type="submit" name="page" value="<?php echo $currentPage - 1; ?>" <?php echo $backButtonDisabled; ?>><i class="bi-chevron-left"></i></button>
                
                <?php foreach (range($startPage, $endPage) as $num) {
                
                    $isActive = ($num == $currentPage) ? "active" : "";
                    ?>
                    
                    <input class="btn btn-dark <?php echo $isActive; ?>" type="submit" name="page" value="<?php echo $num; ?>">
                    
                <?php } ?>
                
                <button class="btn btn-dark pb-2" type="submit" name="page" value="<?php echo $currentPage + 1; ?>" <?php echo $nextButtonDisabled; ?>><i class="bi-chevron-right"></i></button>
                <button class="btn btn-dark pb-2" type="submit" name="page" value="<?php echo $maxPage; ?>" <?php echo $nextButtonDisabled; ?>><i class="bi-chevron-bar-right"></i></button>
                
            <?php
        }
        
        protected function rowTemplate($rowID, $rowTimestamp, $rowType, $rowActor, $rowPage) {
            
            $specialClass = match ($rowType) {
                "Warning", "Notice", "Core Warning", "Compile Warning", "User Warning", "User Notice", "Recoverable Error" => "table-warning",
                "Fatal Error", "Parsing Error", "Core Error", "Compile Error", "User Error", "Deprecated Code Error", "User Deprecated Code Error" => "table-danger",
                default => ""
            };
            ?>
            
            <tr class="log-entry <?php echo $specialClass; ?>" data-row-id="<?php echo $rowID; ?>" data-bs-toggle="modal" data-bs-target="#log-modal">
                <td><?php echo date("c", $rowTimestamp); ?></td>
                <td><?php echo htmlspecialchars($rowType); ?></td>
                <td><?php echo htmlspecialchars($rowActor); ?></td>
                <td><?php echo htmlspecialchars($rowPage); ?></td>
            </tr>
            
            <?php
        }
        
        protected function metaTemplate() {
            ?>
            
            <title>Site Logs</title>
            
            <script src="/resources/js/Logs.js"></script>
            
            <?php
        }
        
        protected function styleTemplate() {
            ?>
            
            tbody, td, tfoot, th, thead, tr {
                
                padding: 0.5rem !important;
                
            }
            
            i {
                
                font-size: 1rem;
                color: var(--bs-light);
                
            }
            
            td {
                
                white-space: pre-wrap;
                
            }
            
            .th {
                
                white-space: pre-wrap;
                
            }
            
            .card-text {
                
                white-space: pre-wrap;
                
            }
            
            <?php
        }
        
    }

    class View extends Templates implements \Ridley\Interfaces\View {
        
        protected $model;
        protected $controller;
        protected $pageList;
        protected $typeList;
        
        public function __construct(
            private \Ridley\Core\Dependencies\DependencyManager $dependencies
        ) {
            
            $this->model = $this->dependencies->get("Model");
            $this->controller = $this->dependencies->get("Controller");
            $this->pageList = $this->dependencies->get("Page Names");
            $this->typeList = $this->dependencies->get("Log Type Groups");
            
        }
        
        public function renderContent() {
            
            $this->mainTemplate();
            
        }
        
        public function renderMeta() {
            
            $this->metaTemplate();
            
        }
        
        public function renderStyle() {
            
            $this->styleTemplate();
            
        }
        
    }

?>