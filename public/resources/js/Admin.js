jQuery(document).ready(function () {
    
    var csrfToken = $("meta[name='csrftoken']").attr("content");
    
    $.ajaxSetup({
        beforeSend: function (request) {
            request.setRequestHeader("CSRF-Token", csrfToken);
        }
    });
    
    $("#search-button").click(function () {
        
        getSearchResults();
        
    });
    
    $(document).on("click", ".acl-add-button", function () {
        
        addGroup(
            $(this).attr("data-group-type"), 
            $(this).attr("data-group-id"), 
            $(this).attr("data-group-name")
        );
        
    });
    
    $(document).on("click", ".acl-delete-button", function () {
        
        removeGroup(
            $(this).attr("data-type"), 
            $(this).attr("data-group")
        );
        
    });
    
    $(document).on("click", ".acl-switch", function () {
        
        updateGroup(
            $(this).attr("data-type"), 
            $(this).attr("data-group"),
            $(this).attr("data-role"),
            $(this).attr("id")
        );
        
    });
    
});

function removeEmptySections() {
    
    for (eachType of ["character", "corporation", "alliance"]) {
        
        if ($("#" + eachType + "-group-list").length && !$("#" + eachType + "-group-list").children().length) {
            
            $("#" + eachType + "-group-header").remove();
            $("#" + eachType + "-group-list").remove();
            
        }
        
    }
    
}

function addSectionIfMissing(upperCaseType) {
    
    type = upperCaseType.toLowerCase()
    precedingSection = false
    
    for (eachType of ["character", "corporation", "alliance"]) {
        
        if ($("#" + eachType + "-group-list").length) {
            
            precedingSection = eachType;
            
        }
        else if (eachType === type) {
            
            if (!precedingSection) {
                
                $("#groups-column").prepend(
                    $("<div/>")
                        .attr("id", type + "-group-list")
                )
                .prepend(
                    $("<h3/>")
                        .addClass("text-light")
                        .attr("id", type + "-group-header")
                        .text(upperCaseType + " Groups")
                );
                
            }
            else {
                
                $("#" + precedingSection + "-group-list").after(
                    $("<h3/>")
                        .addClass("text-light")
                        .attr("id", type + "-group-header")
                        .text(upperCaseType + " Groups")
                )
                $("#" + type + "-group-header").after(
                    $("<div/>")
                        .attr("id", type + "-group-list")
                );
                
            }
            
        }
        
    }
    
}

function generateImageLink(type, id) {
    
    imageType = type.toLowerCase() + "s";
    imageSource = "https://images.evetech.net/" + imageType + "/" + id + "/";
    
    if (imageType === "characters") {
        
        imageSource += "portrait";
        
    }
    else {
        
        imageSource += "logo";
        
    }
    
    imageSource += "?size=128";
    
    return imageSource;
    
}

function renderSearchResult(type, id, name) {
    
    $("#group-search-results").append(
        $("<div/>")
            .addClass("card text-white bg-dark mt-3")
            .append(
                $("<div/>")
                    .addClass("row g-0")
                    .append(
                        $("<div/>")
                            .addClass("col-3")
                            .append(
                                $("<img>")
                                    .addClass("img-fluid rounded-start")
                                    .attr("src", generateImageLink(type, id))
                            )
                    )
                    .append(
                        $("<div/>")
                            .addClass("col-9")
                            .append(
                                $("<div/>")
                                    .addClass("card-header")
                                    .text(name)
                            )
                            .append(
                                $("<div/>")
                                    .addClass("card-body d-grid")
                                    .append(
                                        $("<button/>")
                                            .addClass("btn btn-success acl-add-button")
                                            .attr("type", "button")
                                            .attr("data-group-type", type)
                                            .attr("data-group-id", eachResult)
                                            .attr("data-group-name", name)
                                            .text("Add To ACL")
                                    )
                            )
                    )
            )
    );
    
}

function getSearchResults() {
    
    $("#search-button").attr("hidden", true);
    $("#search-spinner").removeAttr("hidden");
    $("#group-search-results").empty();
    
    dataObject = {
        "Action": "Search", 
        "Type": $("#type-selection").val(), 
        "Term": $("#name-selection").val(), 
        "Strict": $("#strict-selection").is(":checked")
    };
    
    $.ajax({
        url: "/admin/?core_action=api",
        type: "POST",
        data: dataObject,
        mimeType: "application/json",
        dataType: "json",
        success: function(result) {
            
            for (eachResult in result["Entities"]) {
                
                renderSearchResult(result["Type"], eachResult, result["Entities"][eachResult]);
                
            }
            
            $("#search-spinner").attr("hidden", true);
            $("#search-button").removeAttr("hidden");
            
        },
        error: function(result) {
            
            $("#group-search-results").append(
                $("<div/>")
                    .addClass("alert alert-warning")
                    .text("No Search Results Found")
            );
            
            $("#search-spinner").attr("hidden", true);
            $("#search-button").removeAttr("hidden");
            
        }
    });
    
}

function addGroup(type, id, name) {
    
    $("button[data-group-id='" + id + "'][data-group-type='" + type + "']").prop("disabled", true);
    
    dataObject = {
        "Action": "Add_Group", 
        "Type": type, 
        "ID": id, 
        "Name": name
    };
    
    $.ajax({
        url: "/admin/?core_action=api",
        type: "POST",
        data: dataObject,
        mimeType: "application/json",
        dataType: "html",
        success: function(result) {
            
            addSectionIfMissing(type);
            $("#" + type.toLowerCase() + "-group-list").append(result);
            
            $("button[data-group-id='" + id + "'][data-group-type='" + type + "']").text("Added Successfully");
            
        },
        error: function(result) {
            
            $("button[data-group-id='" + id + "'][data-group-type='" + type + "']")
            .removeClass("btn-success")
            .addClass("btn-danger")
            .text("Failed To Add");
            
        }
    });
    
}

function removeGroup(type, id) {
    
    $("input[data-group='" + id + "'][data-type='" + type + "']").prop("disabled", true);
    $("button[data-group='" + id + "'][data-type='" + type + "']").prop("disabled", true);
    
    dataObject = {
        "Action": "Remove_Group", 
        "Type": type, 
        "ID": id
    };
    
    $.ajax({
        url: "/admin/?core_action=api",
        type: "POST",
        data: dataObject,
        mimeType: "application/json",
        dataType: "json",
        success: function(result) {
            
            $(".access-card[data-group='" + id + "'][data-type='" + type + "']").remove();
            removeEmptySections();
            
        },
        error: function(result) {
            
            $("input[data-group='" + id + "'][data-type='" + type + "']").prop("disabled", false);
            $("button[data-group='" + id + "'][data-type='" + type + "']").prop("disabled", false);
            
        }
    });
    
}

function updateGroup(type, id, role, switch_id) {
    
    $("#" + switch_id).prop("disabled", true);
    
    dataObject = {
        "Action": "Update_Group", 
        "Type": type, 
        "ID": id, 
        "Role": role, 
        "Change": ($("#" + switch_id).is(":checked") ? "Added" : "Removed")
    };
    
    $.ajax({
        url: "/admin/?core_action=api",
        type: "POST",
        data: dataObject,
        mimeType: "application/json",
        dataType: "json",
        success: function(result) {
            
            $("#" + switch_id).prop("disabled", false);
            
        },
        error: function(result) {
            
            
            
        }
    });
    
}