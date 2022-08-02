<?php

    namespace Ridley\Views\Relays;

    class Templates {

        protected function mainTemplate() {

            $relayCounter = count($this->model->relays);

            ?>

            <div class="row">

                <div class="col-md-3">

                    <div class="d-grid">

                        <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#creation-modal">
                            New Relay
                        </button>

                    </div>

                </div>
                <div class="col-md-6">
                </div>
                <div class="col-md-3 small text-end text-light fw-bold fst-italic">

                    <span id="poll_counter"><?php echo $relayCounter; ?></span> Relays

                </div>

            </div>

            <hr class="text-light">

            <div class="row justify-content-center text-center text-light">

                <div class="col-md-10">

                    <h2 class="mt-3">Existing Relays</h2>

                    <table class="table table-dark table-hover align-middle text-start text-wrap mt-4">

                        <thead class="p-4">

                            <tr class="align-middle">
                                <th scope="col" style="width: 15%;">Platform</th>
                                <th scope="col" style="width: 15%;">Server</th>
                                <th scope="col" style="width: 15%;">Channel</th>
                                <th scope="col" style="width: 20%;">Corporation</th>
                                <th scope="col" style="width: 20%;">Alliance</th>
                                <th scope="col" style="width: 15%;">Types</th>
                            </tr>

                        </thead>

                        <tbody>

                            <?php $this->relayLister(); ?>

                        </tbody>

                    </table>

                </div>

            </div>

            <?php $this->creationModalTemplate(); ?>

            <?php $this->detailsModalTemplate(); ?>

            <?php
        }

        protected function relayLister() {

            foreach ($this->model->relays as $eachRelay) {

                $eachRelay->render();

            }

        }

        protected function creationModalTemplate() {

            ?>

            <div id="creation-modal" class="modal fade" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">

                <div class="modal-dialog modal-xl">

                    <div class="modal-content bg-dark text-light border-secondary">

                        <div class="modal-header border-secondary">

                            <h5 class="modal-title">Create a New Relay</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

                        </div>
                        <div class="modal-body">

                            <div class="row">

                                <div class="col-xl-4">

                                    <label for="new_url" class="form-label h5">Webhook URL</label>

                                    <div class="input-group">

                                        <span class="input-group-text"><i id="new_type" class="bi bi-question-lg"></i></span>
                                        <input type="password" id="new_url" class="form-control">

                                    </div>

                                </div>

                                <div class="col-xl-4">

                                    <div id="new_server_container" hidden>

                                        <label for="new_server" class="form-label h5">Linked Server</label>

                                        <div class="input-group">
                                            <input type="text" id="new_server" class="form-control">
                                            <button class="btn btn-outline-warning" type="button" id="update_server_name" disabled>Update</button>
                                        </div>

                                    </div>

                                </div>

                                <div class="col-xl-4">

                                    <div id="new_channel_container" hidden>

                                        <label for="new_channel" class="form-label h5">Linked Channel</label>

                                        <div class="input-group">
                                            <input type="text" id="new_channel" class="form-control">
                                            <button class="btn btn-outline-warning" type="button" id="update_channel_name" disabled>Update</button>
                                        </div>

                                    </div>

                                </div>

                            </div>

                            <div class="row mt-4">

                                <div class="col-xl-4">

                                    <h5 class="mb-2">Corporation Selection</h5>

                                    <select class="form-select" id="new_corporation" aria-label="Relay Corporation Selection">

                                        <option selected></option>

                                        <?php echo $this->listRelayCorps(); ?>

                                    </select>

                                    <div class="mt-4 mb-2" id="character_breakdown" hidden>

                                        <h5 class="mb-2">Character Breakdown</h5>

                                        <div class="card border-secondary bg-dark">

                                            <ul class="list-group list-group-flush small">

                                                <li class="list-group-item border-secondary bg-dark text-light">

                                                    <span id="total_characters"></span> Total Characters

                                                </li>

                                                <li class="list-group-item border-secondary bg-dark text-light">

                                                    <span id="director_characters"></span> Directors

                                                </li>

                                                <li class="list-group-item border-secondary bg-dark text-light">

                                                    <span id="citadel_alert_characters"></span> Citadel Alert Characters


                                                </li>

                                            </ul>

                                            <div class="card-body">

                                                <div class="d-grid">

                                                    <button id="corp_role_expander" class="btn btn-sm btn-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#corp_role_card" aria-expanded="false" aria-controls="corp_role_card">Toggle All Roles</button>

                                                </div>

                                            </div>

                                        </div>

                                        <div class="collapse mt-4" id="corp_role_card">

                                            <h5 class="mb-2">Role Breakdown</h5>

                                            <div class="card border-secondary bg-dark">

                                                <ul id="corp_role_list" class="list-group list-group-flush small">



                                                </ul>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                                <div class="col-xl-1">

                                </div>

                                <div class="col-xl-2">

                                    <h5 class="mb-2">Ping Type</h5>

                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="new_ping_type" id="everyone_ping_type" value="everyone" checked>
                                        <label class="form-check-label" for="everyone_ping_type">
                                            @everyone
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="new_ping_type" id="channel_ping_type" value="channel">
                                        <label class="form-check-label" for="channel_ping_type">
                                            @channel
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="new_ping_type" id="here_ping_type" value="here">
                                        <label class="form-check-label" for="here_ping_type">
                                            @here
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="new_ping_type" id="none_ping_type" value="none">
                                        <label class="form-check-label" for="none_ping_type">
                                            No Ping
                                        </label>
                                    </div>

                                </div>

                                <div class="col-xl-5">

                                    <h5 class="mb-2">Notification Whitelist</h5>

                                    <?php echo $this->listNotificationTypes(); ?>

                                </div>

                            </div>

                            <div class="d-grid mt-4">

                                <button id="creation_button" class="btn btn-outline-success" disabled>Create</button>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

            <?php

        }

        protected function detailsModalTemplate() {

            ?>

            <div id="details-modal" class="modal fade" tabindex="-1" aria-hidden="true">

                <div class="modal-dialog modal-xl">

                    <div class="modal-content bg-dark text-light border-secondary">

                        <div class="modal-header border-secondary">

                            <h5 class="modal-title">Relay Details</h5>
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

                                An error occurred while trying to get this entry! Try again?

                            </div>
                            <div id="modal-data">

                                <div class="row">
                                    <div class="col-lg-3 mt-1">

                                        <div class="card bg-dark text-white border-secondary">
                                            <div class="card-body">
                                                <h6 class="card-title fw-bold">Ping Type</h6>
                                                <p class="card-text" id="modal-row-ping-type"></p>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="col-lg-3 mt-1">

                                        <div class="card bg-dark text-white border-secondary">
                                            <div class="card-body">
                                                <h6 class="card-title fw-bold">Platform</h6>
                                                <p class="card-text" id="modal-row-platform"></p>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="col-lg-3 mt-1">

                                        <div class="card bg-dark text-white border-secondary">
                                            <div class="card-body">
                                                <h6 class="card-title fw-bold">Server</h6>
                                                <p class="card-text" id="modal-row-server"></p>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="col-lg-3 mt-1">

                                        <div class="card bg-dark text-white border-secondary">
                                            <div class="card-body">
                                                <h6 class="card-title fw-bold">Channel</h6>
                                                <p class="card-text" id="modal-row-channel"></p>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-lg-4 mt-1">

                                        <div class="card bg-dark text-white border-secondary">
                                            <div class="card-body">
                                                <h6 class="card-title fw-bold">Alliance</h6>
                                                <p class="card-text" id="modal-row-alliance"></p>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="col-lg-4 mt-1">

                                        <div class="card bg-dark text-white border-secondary">
                                            <div class="card-body">
                                                <h6 class="card-title fw-bold">Corporation</h6>
                                                <p class="card-text" id="modal-row-corporation"></p>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="col-lg-4 mt-1">

                                        <div class="card bg-dark text-white border-secondary">
                                            <div class="card-body">
                                                <h6 class="card-title fw-bold">Characters</h6>
                                                <p class="card-text" id="modal-row-characters"></p>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-12 mt-1">

                                        <div class="card bg-dark text-white border-secondary">
                                            <div class="card-body">
                                                <h6 class="card-title fw-bold">Whitelisted Notifications</h6>
                                                <div class="d-flex flex-wrap small" id="modal-row-whitelist"></div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <div class="d-grid mt-4">

                                    <button id="delete_button" data-details-modal-id="" class="btn btn-outline-danger">Delete Relay</button>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

            <?php
        }

        protected function listRelayCorps() {

            foreach ($this->model->relayCorps as $eachCorpID => $eachCorp) {

                $labelName = (!is_null($eachCorp["Alliance Name"])) ? ($eachCorp["Name"] . " [" . $eachCorp["Alliance Name"] . "]") : $eachCorp["Name"];
                ?>

                <option value="<?php echo htmlspecialchars($eachCorpID); ?>"><?php echo htmlspecialchars($labelName); ?></option>

                <?php
            }

        }

        protected function listNotificationTypes() {

            $typeNumber = 0;

            foreach ($this->controller->notificationTypes as $eachType => $includedNotifications) {

                $subTypeNumber = 0;
                $typeNumber++;

                $typeID = "New_Type_" . $typeNumber;
                $collapseID = "Type_" . $typeNumber . "_Collapse";
                $collapseControlID = "Type_" . $typeNumber . "_Collapse_Control";

                ?>

                <div class="form-check">

                    <input class="form-check-input type-check" data-notification-type="<?php echo htmlspecialchars($eachType); ?>" type="checkbox" id="<?php echo $typeID; ?>">
                    <label class="form-check-label" for="<?php echo $typeID; ?>"><?php echo htmlspecialchars(str_replace("_", " ", $eachType)); ?></label>
                    <a class="collapsed type-collapse-control" data-bs-toggle="collapse" role="button" href="#<?php echo $collapseID; ?>" aria-expanded="false" aria-controls="<?php echo $collapseID; ?>"><i id="<?php echo $collapseControlID; ?>" class="bi bi-caret-down-fill text-light"></i></a>

                </div>

                <div class="ms-4 mt-2 mb-2 collapse" id="<?php echo $collapseID; ?>">

                    <?php

                    foreach ($includedNotifications as $eachSubtype => $subtypeData) {

                        $subTypeNumber++;

                        $subTypeID = "New_Type_" . $typeNumber . "_SubType_" . $subTypeNumber;

                        if (isset($subtypeData["Tooltip"])) {
                            ?>

                            <div class="form-check">
                                <input class="form-check-input subtype-check" data-notification-type="<?php echo htmlspecialchars($eachType); ?>" data-notification-subtype="<?php echo htmlspecialchars($eachSubtype); ?>" type="checkbox" id="<?php echo $subTypeID; ?>">
                                <label class="form-check-label" for="<?php echo $subTypeID; ?>"><a href="#" class="text-light" style="text-decoration-style: dotted;" data-bs-toggle="tooltip" title="<?php echo htmlspecialchars($subtypeData["Tooltip"]); ?>"><?php echo htmlspecialchars($subtypeData["Name"]); ?></a></label>
                            </div>

                            <?php
                        }

                        else {
                            ?>

                            <div class="form-check">
                                <input class="form-check-input subtype-check" data-notification-type="<?php echo htmlspecialchars($eachType); ?>" data-notification-subtype="<?php echo htmlspecialchars($eachSubtype); ?>" type="checkbox" id="<?php echo $subTypeID; ?>">
                                <label class="form-check-label" for="<?php echo $subTypeID; ?>"><?php echo htmlspecialchars($subtypeData["Name"]); ?></label>
                            </div>

                            <?php
                        }
                    }

                    ?>

                </div>

                <?php
            }

        }

        protected function metaTemplate() {
            ?>

            <title>Relay Management</title>
            <meta property="og:title" content="Eve Watchman">
            <meta property="og:description" content="The Eve Watchman App">
            <meta property="og:type" content="website">
            <meta property="og:url" content="<?php echo $_SERVER["SERVER_NAME"]; ?>">

            <script src="/resources/js/Relays.js"></script>

            <?php
        }

    }

    class View extends Templates implements \Ridley\Interfaces\View {

        protected $controller;
        protected $model;
        protected $databaseConnection;

        public function __construct(
            private \Ridley\Core\Dependencies\DependencyManager $dependencies
        ) {

            $this->controller = $this->dependencies->get("Controller");
            $this->model = $this->dependencies->get("Model");
            $this->databaseConnection = $this->dependencies->get("Database");

        }

        public function renderContent() {

            $this->mainTemplate();

        }

        public function renderMeta() {

            $this->metaTemplate();

        }

    }

?>
