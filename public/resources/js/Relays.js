jQuery(document).ready(function () {

    webhookValidated = false;
    corpSelected = false;

    var csrfToken = $("meta[name='csrftoken']").attr("content");

    $.ajaxSetup({
        beforeSend: function (request) {
            request.setRequestHeader("CSRF-Token", csrfToken);
        }
    });

    $("[data-bs-toggle=tooltip]").tooltip();

    $(".type-collapse-control").click(function() {

        if ($(this).hasClass("collapsed")) {

            $(this).children("i").removeClass("bi-caret-up-fill");
            $(this).children("i").addClass("bi-caret-down-fill");

        }
        else {

            $(this).children("i").removeClass("bi-caret-down-fill");
            $(this).children("i").addClass("bi-caret-up-fill");

        }

    });

    $(".type-check").click(function() {

        if ($(this).is(":checked")) {

            toSelect = $(this).attr("data-notification-type");
            $("[data-notification-type=" + toSelect + "]").prop("checked", true);

        }
        else {

            toDeselect = $(this).attr("data-notification-type");
            $("[data-notification-type=" + toDeselect + "]").prop("checked", false);
        }

        checkIfReadyToCreate();

    });

    $(".subtype-check").click(function() {

        toCheckAgainst = $(this).attr("data-notification-type");

        if ($("[data-notification-type=" + toCheckAgainst + "].subtype-check").filter(":checked").length === $("[data-notification-type=" + toCheckAgainst + "].subtype-check").length) {

            $("[data-notification-type=" + toCheckAgainst + "].type-check").prop("indeterminate", false);
            $("[data-notification-type=" + toCheckAgainst + "].type-check").prop("checked", true);

        }
        else if ($("[data-notification-type=" + toCheckAgainst + "].subtype-check").filter(":checked").length === 0) {

            $("[data-notification-type=" + toCheckAgainst + "].type-check").prop("indeterminate", false);
            $("[data-notification-type=" + toCheckAgainst + "].type-check").prop("checked", false);

        }
        else {

            $("[data-notification-type=" + toCheckAgainst + "].type-check").prop("checked", false);
            $("[data-notification-type=" + toCheckAgainst + "].type-check").prop("indeterminate", true);

        }

        checkIfReadyToCreate();

    });

    $(document).on("input", "#new_url", function() {

        $("#new_type").removeClass();
        $("#new_type").addClass("spinner-border small");
        $("#new_type").css("width", "16px");
        $("#new_type").css("height", "16px");
        $("#new_server_container").attr("hidden", true);
        $("#new_channel_container").attr("hidden", true);

        try {

            incomingURL = new URL($("#new_url").val());
            urlValid = true;

        }
        catch(error) {

            urlValid = false;

        }

        if (urlValid && incomingURL.origin === "https://hooks.slack.com" && incomingURL.pathname.split("/").length === 5) {

            checkURL($("#new_url").val());

        }
        else if (urlValid && incomingURL.origin === "https://discord.com" && incomingURL.pathname.split("/").length === 5 && incomingURL.pathname.startsWith("/api/webhooks/")) {

            checkURL($("#new_url").val());

        }
        else {

            $("#new_type").removeClass();
            $("#new_type").removeAttr("style");
            $("#new_type").addClass("bi bi-question-lg");
            $("#new_server_container").attr("hidden", true);
            $("#new_channel_container").attr("hidden", true);

        }

    });

    $(document).on("change", "#new_corporation", function() {

        if ($("#new_corporation").val() !== "") {

            corpSelected = true;
            checkIfReadyToCreate();
            getCorpBreakdown($("#new_corporation").val());

        }
        else {

            corpSelected = false;
            checkIfReadyToCreate();
            $("#character_breakdown").attr("hidden", true);


        }

    });

    $("#update_server_name").click(function() {

        $("#update_server_name").prop("disabled", true);
        $("#new_server").prop("disabled", false);

    });

    $("#update_channel_name").click(function() {

        $("#update_channel_name").prop("disabled", true);
        $("#new_channel").prop("disabled", false);

    });

    $(document).on("input", "#new_channel", function() {

        checkIfReadyToCreate();

    });

    $(document).on("input", "#new_server", function() {

        checkIfReadyToCreate();

    });

    $("#creation_button").click(function() {

        createRelay();

    });

    $(".relay-entry").click(function() {

        queryRelayData($(this).attr("data-row-id"));

    });

    $("#delete_button").click(function() {

        deleteRelay($(this).attr("data-details-modal-id"));

    });

});

function checkIfReadyToCreate() {

    if (
        webhookValidated
        && corpSelected
        && ($(".subtype-check:checkbox:checked").length)
        && (
            $("#new_channel").prop("disabled")
            || (
                $("#new_channel").val() !== ""
                && $("#new_channel").val() !== null
            )
        )
        && (
            $("#new_server").prop("disabled")
            || (
                $("#new_server").val() !== ""
                && $("#new_server").val() !== null
            )
        )
    ) {

        $("#creation_button").prop("disabled", false);

    }
    else {

        $("#creation_button").prop("disabled", true);

    }

}

function getCorpBreakdown(IDToCheck) {

    $.ajax({
        url: "/relay_management/?core_action=api",
        type: "POST",
        data: {"Action": "Get_Relay_Corp", "ID": IDToCheck},
        mimeType: "application/json",
        dataType: "json",
        success: function(result) {

            $("#total_characters").text(result["Total Characters"]);
            $("#director_characters").text(result["Directors"]);
            $("#citadel_alert_characters").text(result["Citadel Alert Characters"]);

            $("#corp_role_list").empty();

            for (eachRole in result["Role Breakdown"]) {

                $("#corp_role_list").append(
                    $("<li/>")
                        .addClass("list-group-item border-secondary bg-dark text-light")
                        .append(
                            $("<div/>")
                                .addClass("row")
                                .append(
                                    $("<div/>")
                                        .addClass("col-xxl-10")
                                        .text(eachRole)
                                )
                                .append(
                                    $("<div/>")
                                        .addClass("col-xxl-2 text-end")
                                        .text(result["Role Breakdown"][eachRole])
                                )
                        )
                );

            }

            $("#character_breakdown").removeAttr("hidden");

        },
        error: function(result) {

            $("#character_breakdown").attr("hidden", true);

        }
    });

}

function checkURL(URLToCheck) {

    $.ajax({
        url: "/relay_management/?core_action=api",
        type: "POST",
        data: {"Action": "Verify_URL", "URL": URLToCheck},
        mimeType: "application/json",
        dataType: "json",
        success: function(result) {

            $("#new_server_container").removeAttr("hidden");
            $("#new_channel_container").removeAttr("hidden");

            $("#new_type").removeClass();
            $("#new_type").removeAttr("style");

            if (result["Type"] === "Slack") {

                webhookValidated = true;
                $("#new_type").addClass("bi bi-slack");

            }
            else if (result["Type"] === "Discord") {

                webhookValidated = true;
                $("#new_type").addClass("bi bi-discord");

            }
            else {

                webhookValidated = false;
                $("#new_type").addClass("bi bi-question-lg");
                $("#new_server_container").attr("hidden", true);
                $("#new_channel_container").attr("hidden", true);

            }

            if (result["Server Name"] === null || result["Server Name"] === "") {

                $("#update_server_name").prop("disabled", true);
                $("#new_server").prop("disabled", false);
                $("#new_server").val("");

            }
            else {

                $("#update_server_name").prop("disabled", false);
                $("#new_server").prop("disabled", true);
                $("#new_server").val(result["Server Name"]);

            }

            if (result["Channel Name"] === null || result["Channel Name"] === "") {

                $("#update_channel_name").prop("disabled", true);
                $("#new_channel").prop("disabled", false);
                $("#new_channel").val("");

            }
            else {

                $("#update_channel_name").prop("disabled", false);
                $("#new_channel").prop("disabled", true);
                $("#new_channel").val(result["Channel Name"]);

            }

            checkIfReadyToCreate();

        },
        error: function(result) {

            webhookValidated = false;
            $("#new_type").removeClass();
            $("#new_type").removeAttr("style");
            $("#new_type").addClass("bi bi-question-lg");
            $("#new_server_container").attr("hidden", true);
            $("#new_channel_container").attr("hidden", true);
            checkIfReadyToCreate();

        }
    });

}

function populateDetails(relayData) {

    $("#delete_button").attr("data-details-modal-id", relayData["id"]);
    $("#modal-row-ping-type").text("@" + relayData["pingtype"]);
    $("#modal-row-platform").text(relayData["type"]);
    $("#modal-row-server").text(relayData["server"]);
    $("#modal-row-channel").text(relayData["channel"]);
    $("#modal-row-alliance").text(relayData["alliancename"]);
    $("#modal-row-corporation").text(relayData["corporationname"]);
    $("#modal-row-characters").text(relayData["characters"]);

    $("#modal-row-whitelist").empty();

    for (eachType of relayData["whitelist"]) {

        $("#modal-row-whitelist").append(
            $("<div/>")
                .addClass("border border-secondary rounded m-2 p-2 text-center")
                .css("min-width", "31.75%")
                .text(eachType)
        );

    }

}

function buildCreationRequest() {

    var selectedNotifications = [];

    $(".subtype-check:checkbox:checked").each(function() {
        selectedNotifications.push($(this).attr("data-notification-subtype"));
    });

    var selectServer = (
        !$("#new_server").prop("disabled")
        && $("#new_server").val() !== ""
        && $("#new_server").val() !== null
    ) ? $("#new_server").val() : null;
    var selectChannel = (
        !$("#new_channel").prop("disabled")
        && $("#new_channel").val() !== ""
        && $("#new_channel").val() !== null
    ) ? $("#new_channel").val() : null;

    var theRequest = {
        "Action": "Create_Relay",
        "URL": $("#new_url").val(),
        "Corporation_ID": $("#new_corporation").val(),
        "Ping_Type": $("input[name=new_ping_type]:checked").val(),
        "Notification_Whitelist": selectedNotifications,
        "Server_Name": selectServer,
        "Channel_Name": selectChannel
    };

    return theRequest;

}

function createRelay() {

    $.ajax({
        url: "/relay_management/?core_action=api",
        type: "POST",
        data: buildCreationRequest(),
        mimeType: "application/json",
        dataType: "json",
        success: function(result) {

            location.reload();
            return false;

        },
        error: function(result) {

            $("#creation_button").text("Error! Refresh and Try Again!");
            $("#creation_button").removeClass("btn-outline-success");
            $("#creation_button").addClass("btn-outline-danger");
            $("#creation_button").prop("disabled", true);

        }
    });

}

function deleteRelay(idToDelete) {

    $.ajax({
        url: "/relay_management/?core_action=api",
        type: "POST",
        data: {"Action": "Delete_Relay", "ID": idToDelete},
        mimeType: "application/json",
        dataType: "json",
        success: function(result) {

            location.reload();
            return false;

        },
        error: function(result) {

            $("#delete_button").text("Error! Refresh and Try Again!");
            $("#delete_button").prop("disabled", true);

        }
    });

}

function queryRelayData(idToQuery) {

    $("#modal-data").attr("hidden", true);
    $("#modal-error").attr("hidden", true);
    $("#modal-spinner").removeAttr("hidden");

    $.ajax({
        url: "/relay_management/?core_action=api",
        type: "POST",
        data: {"Action": "Query_Relay", "ID": idToQuery},
        mimeType: "application/json",
        dataType: "json",
        success: function(result) {

            populateDetails(result);

            $("#modal-spinner").attr("hidden", true);
            $("#modal-error").attr("hidden", true);
            $("#modal-data").removeAttr("hidden");

        },
        error: function(result) {

            $("#modal-spinner").attr("hidden", true);
            $("#modal-data").attr("hidden", true);
            $("#modal-error").removeAttr("hidden");

        }
    });

}
