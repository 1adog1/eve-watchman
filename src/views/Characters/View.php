<?php

    namespace Ridley\Views\Characters;

    class Templates {

        protected function mainTemplate() {
            ?>

            <div class="row">

                <div class="col-md-3">

                    <div class="d-grid">

                        <input type="checkbox" class="btn-check" id="hidden_toggle">
                        <label class="btn btn-primary btn-small" for="hidden_toggle">
                            Toggle Corporations Without Characters
                        </label>

                    </div>

                </div>

            </div>

            <hr class="text-light">

            <?php $this->allianceGroupTemplate(); ?>

            <?php $this->modalTemplate(); ?>

            <?php
        }

        protected function allianceGroupTemplate() {

            foreach ($this->model->comprehensiveData as $eachAllianceID => $eachAlliance) {
            ?>

                <div class="row text-light">

                    <div class="col-xl-3">
                        <div class="card bg-dark mt-4">
                            <div class="row">
                                <div class="col-md-3 text-center">
                                    <img class="img-fluid rounded-start m-2" src="https://images.evetech.net/alliances/<?php echo htmlspecialchars($eachAlliance["ID"]); ?>/logo?size=64">
                                </div>
                                <div class="col-md-9 d-flex align-items-center justify-content-center">
                                    <h3><?php echo htmlspecialchars($eachAlliance["Name"]); ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-9">

                        <table class="table table-dark table-hover align-middle text-start text-wrap mt-4">

                            <thead class="p-4">

                                <tr class="align-middle">
                                    <th scope="col" style="width: 5%;"></th>
                                    <th scope="col" style="width: 35%;">Name</th>
                                    <th scope="col" style="width: 15%;">Citadel Monitors</th>
                                    <th scope="col" style="width: 15%;">Directors</th>
                                    <th scope="col" style="width: 15%;">Invalid Characters</th>
                                    <th scope="col" style="width: 15%;">Valid Characters</th>
                                </tr>

                            </thead>

                            <tbody>

                                <?php
                                foreach ($eachAlliance["Corporations"] as $eachCorp) {

                                    $this->corpEntryTemplate(
                                        $eachCorp->getBreakdown()["ID"],
                                        $eachCorp->getBreakdown()["Name"],
                                        $eachCorp->getBreakdown()["Citadel Alert Characters"],
                                        $eachCorp->getBreakdown()["Directors"],
                                        $eachCorp->getBreakdown()["Invalid Characters"],
                                        $eachCorp->getBreakdown()["Total Characters"]
                                    );

                                }
                                ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            <?php
            }

        }

        protected function corpEntryTemplate($corpID, $corpName, $stationManagers, $directors, $invalidCharacters, $totalCharacters) {

            $noCharacters = false;

            if ($totalCharacters >= 1) {

                $rowColor = "table-success";

            }
            elseif ($invalidCharacters >= 1) {

                $rowColor = "table-warning";

            }
            else {

                $rowColor = "table-danger";
                $noCharacters = true;

            }
            ?>

            <tr class="corp-row <?php echo $rowColor; ?><?php echo $noCharacters ? " no-characters" : ""; ?>" data-row-id="<?php echo htmlspecialchars($corpID); ?>" data-bs-toggle="modal" data-bs-target="#corp-modal" <?php echo $noCharacters ? "hidden" : ""; ?>>
                <td><img class="img-fluid" src="https://images.evetech.net/corporations/<?php echo htmlspecialchars($corpID); ?>/logo?size=64"></td>
                <td><?php echo htmlspecialchars($corpName); ?></td>
                <td><?php echo htmlspecialchars($stationManagers); ?></td>
                <td><?php echo htmlspecialchars($directors); ?></td>
                <td class="text-danger"><?php echo htmlspecialchars($invalidCharacters); ?></td>
                <td><?php echo htmlspecialchars($totalCharacters); ?></td>
            </tr>

            <?php
        }

        protected function modalTemplate() {
            ?>

            <div class="modal fade" id="corp-modal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content bg-dark text-white border-secondary">
                        <div class="modal-header border-secondary">
                            <h5 class="modal-title w-50">Relay Character Management</h5>
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

                                An error occurred while trying to get this corp entry! Try again?

                            </div>
                            <div id="modal-data">

                                <div class="row">

                                    <div class="col-xl-7">

                                        <div id="modal-character-info">

                                            <div class="row">

                                                <div class="col-xl-2 mt-4">

                                                    <img id="modal-row-character-image" class="img-fluid rounded border border-secondary" src="">

                                                </div>
                                                <div class="col-xl-7 mt-4">

                                                    <div class="card bg-dark text-white border-secondary">
                                                        <div class="card-body">
                                                            <h6 class="card-title fw-bold">Name</h6>
                                                            <p class="card-text" id="modal-row-name"></p>
                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="col-xl-3 mt-4">

                                                    <div class="card bg-dark text-white border-secondary">
                                                        <div class="card-body">
                                                            <h6 class="card-title fw-bold">Status</h6>
                                                            <p class="card-text" id="modal-row-status"></p>
                                                        </div>
                                                    </div>

                                                </div>

                                            </div>

                                            <div class="row">

                                                <div class="col-12 mt-4">

                                                    <div class="card bg-dark text-white border-secondary">
                                                        <div class="card-body">
                                                            <h6 class="card-title fw-bold">Roles</h6>
                                                            <p class="card-text d-flex flex-wrap" id="modal-row-roles"></p>
                                                        </div>
                                                    </div>

                                                </div>

                                            </div>

                                            <div class="d-grid mt-4">

                                                <button id="delete_button" data-character-id="" class="btn btn-outline-danger">Delete Character</button>

                                            </div>

                                        </div>

                                    </div>

                                    <div class="col-xl-5 border-start border-secondary">

                                        <div class="text-center">

                                            <h5>Relay Characters</h5>

                                        </div>
                                        <table class="table table-dark table-hover align-middle text-start text-wrap mt-4">

                                            <tbody id="modal-character-list">

                                            </tbody>

                                        </table>

                                    </div>

                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php
        }

        protected function metaTemplate() {
            ?>

            <title>Character Management</title>
            <meta property="og:title" content="Eve Watchman">
            <meta property="og:description" content="The Eve Watchman App">
            <meta property="og:type" content="website">
            <meta property="og:url" content="<?php echo $_SERVER["SERVER_NAME"]; ?>">

            <script src="/resources/js/Characters.js"></script>

            <?php
        }

    }

    class View extends Templates implements \Ridley\Interfaces\View {

        protected $model;

        public function __construct(
            private \Ridley\Core\Dependencies\DependencyManager $dependencies
        ) {

            $this->model = $this->databaseConnection = $this->dependencies->get("Model");

        }

        public function renderContent() {

            $this->mainTemplate();

        }

        public function renderMeta() {

            $this->metaTemplate();

        }

    }

?>
