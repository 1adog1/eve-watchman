jQuery(document).ready(function () {

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

        checkIfReadyToCreate();

    });

    $(document).on("input", "#new_token", function() {

        checkIfReadyToCreate();

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

    $("#creation_button").click(function() {

        createTimerboard();

    });

    $(".timerboard-entry").click(function() {

        queryTimerboardData($(this).attr("data-row-id"));

    });

    $("#delete_button").click(function() {

        deleteTimerboard($(this).attr("data-details-modal-id"));

    });

});

function checkIfReadyToCreate() {

    if (
        corpSelected
        && ($(".subtype-check:checkbox:checked").length)
        && (
            $("#new_url").val() !== ""
            && $("#new_url").val() !== null
        )
        && (
            $("#new_token").val() !== ""
            && $("#new_token").val() !== null
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
        url: "/timerboard_management/?core_action=api",
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

function populateDetails(timerboardData) {

    $("#delete_button").attr("data-details-modal-id", timerboardData["id"]);
    $("#modal-row-platform").text(timerboardData["type"]);
    $("#modal-row-alliance").text(timerboardData["alliancename"]);
    $("#modal-row-corporation").text(timerboardData["corporationname"]);
    $("#modal-row-characters").text(timerboardData["characters"]);

    $("#modal-row-whitelist").empty();

    for (eachType of timerboardData["whitelist"]) {

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

    var theRequest = {
        "Action": "Create_Timerboard",
        "Timerboard_Type": $("input[name=new_type]:checked").val(),
        "URL": $("#new_url").val(),
        "Token": $("#new_token").val(),
        "Corporation_ID": $("#new_corporation").val(),
        "Notification_Whitelist": selectedNotifications
    };

    return theRequest;

}

function createTimerboard() {

    $.ajax({
        url: "/timerboard_management/?core_action=api",
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

function deleteTimerboard(idToDelete) {

    $.ajax({
        url: "/timerboard_management/?core_action=api",
        type: "POST",
        data: {"Action": "Delete_Timerboard", "ID": idToDelete},
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

function queryTimerboardData(idToQuery) {

    $("#modal-data").attr("hidden", true);
    $("#modal-error").attr("hidden", true);
    $("#modal-spinner").removeAttr("hidden");

    $.ajax({
        url: "/timerboard_management/?core_action=api",
        type: "POST",
        data: {"Action": "Query_Timerboard", "ID": idToQuery},
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
