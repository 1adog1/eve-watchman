jQuery(document).ready(function () {

    var csrfToken = $("meta[name='csrftoken']").attr("content");

    $.ajaxSetup({
        beforeSend: function (request) {
            request.setRequestHeader("CSRF-Token", csrfToken);
        }
    });

    $("[data-bs-toggle=tooltip]").tooltip();

    $(".corp-row").click(function() {

        getCorpInfo($(this).attr("data-row-id"));

    });

    $(document).on("click", ".character-row", function() {

        getCharacterInfo($(this).attr("data-row-id"));

    });

    $("#delete_button").click(function() {

        deleteCharacter($(this).attr("data-character-id"));

    });

    $("#hidden_toggle").click(function() {

        if ($(this).is(":checked")) {

            $(".no-characters").removeAttr("hidden");


        }
        else {

            $(".no-characters").attr("hidden", true);

        }

    });

});

function populateCharacterList(characterList) {

    $("#modal-character-list").empty();

    if (characterList.length != 0) {

        for (eachCharacter of characterList) {

            rowColor = (eachCharacter["Valid"] ? "table-success" : "table-danger");

            specialPermission = "";

            if (eachCharacter["Is Director"]) {

                specialPermission = "bi-key";

            }
            else if (eachCharacter["Is Station Manager"]) {

                specialPermission = "bi-building";

            }

            $("#modal-character-list").append(
                $("<tr/>")
                    .addClass("character-row " + rowColor)
                    .attr("data-row-id", eachCharacter["ID"])
                    .append(
                        $("<td/>")
                            .append(
                                $("<img>")
                                    .addClass("img-fluid rounded")
                                    .attr("src", "https://images.evetech.net/characters/" + eachCharacter["ID"] + "/portrait?size=64")
                            )
                    )
                    .append(
                        $("<td/>")
                            .text(eachCharacter["Name"])
                    )
                    .append(
                        $("<td/>")
                            .addClass("text-end")
                            .append(
                                $("<i/>")
                                    .addClass("bi " + specialPermission)
                            )
                    )
            );

        }

    }
    else {

        $("#modal-character-list").append(
            $("<div/>")
                .addClass("text-center text-danger fs-6")
                .text("No Characters Found!")
        );

    }

}

function populateCharacterDetails(characterDetails) {

    $("#modal-row-character-image").attr("src", "https://images.evetech.net/characters/" + characterDetails["ID"] + "/portrait?size=128");
    $("#modal-row-name").text(characterDetails["Name"]);
    $("#modal-row-status").text(characterDetails["Status"]);
    $("#delete_button").attr("data-character-id", characterDetails["ID"]);

    $("#modal-row-roles").empty();

    for (eachRole of characterDetails["Roles"]) {

        if (!eachRole.includes("Query") && !eachRole.includes("Take")) {

            specialRole = "border-secondary";

            if (eachRole == "Director") {

                specialRole = "border-success";

            }
            else if (eachRole == "Station_Manager") {

                specialRole = "border-warning";

            }

            $("#modal-row-roles").append(
                $("<div/>")
                    .addClass("border rounded m-2 p-2 text-center " + specialRole)
                    .css("min-width", "47.25%")
                    .text(eachRole.replaceAll("_", " "))
            );

        }

    }

}

function deleteCharacter(characterID) {

    $.ajax({
        url: "/character_management/?core_action=api",
        type: "POST",
        data: {"Action": "Delete_Character", "ID": characterID},
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

function getCharacterInfo(characterID) {

    $("#modal-character-info").attr("hidden", true);

    $.ajax({
        url: "/character_management/?core_action=api",
        type: "POST",
        data: {"Action": "Get_Character_Info", "ID": characterID},
        mimeType: "application/json",
        dataType: "json",
        success: function(result) {

            populateCharacterDetails(result);

            $("#modal-character-info").removeAttr("hidden");

        },
        error: function(result) {



        }
    });

}

function getCorpInfo(corpID) {

    $("#modal-data").attr("hidden", true);
    $("#modal-character-info").attr("hidden", true);
    $("#modal-error").attr("hidden", true);
    $("#modal-spinner").removeAttr("hidden");

    $.ajax({
        url: "/character_management/?core_action=api",
        type: "POST",
        data: {"Action": "Get_Corp_Info", "ID": corpID},
        mimeType: "application/json",
        dataType: "json",
        success: function(result) {

            populateCharacterList(result);

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
