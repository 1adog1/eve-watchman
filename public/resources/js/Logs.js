jQuery(document).ready(function () {
    
    var csrfToken = $("meta[name='csrftoken']").attr("content");
    
    $.ajaxSetup({
        beforeSend: function (request) {
            request.setRequestHeader("CSRF-Token", csrfToken);
        }
    });
    
    $(".log-entry").click(function() {
        updateModal($(this).attr("data-row-id"));
    });
    
});

function timeSince(dateToCheck) {
    
    var currentDate = new Date();
    var secondsSince = (Math.floor((dateToCheck - currentDate) / 1000) * -1);
    
    if (secondsSince <= 60) {
        return (secondsSince + " Second(s) Ago");
    }
    else if (secondsSince <= 3600) {
        return (Math.floor(secondsSince / 60) + " Minute(s) Ago");
    }
    else if (secondsSince <= 86400) {
        return (Math.floor(secondsSince / 3600) + " Hour(s) Ago");
    }
    else {
        return (Math.floor(secondsSince / 86400) + " Day(s) Ago");
    }
    
}

function updateModal(entryID) {
    
    $("#modal-row-id").text(entryID);
    $("#modal-timing").attr("hidden", true);
    $("#modal-data").attr("hidden", true);
    $("#modal-error").attr("hidden", true);
    $("#modal-spinner").removeAttr("hidden");
    
    $.ajax({
        url: "/logs/?core_action=api",
        type: "POST",
        data: {"Action": "Get_Row", "ID": entryID},
        mimeType: "application/json",
        dataType: "json",
        success: function(result) {
            
            var stamp = new Date(result["timestamp"] * 1000);
            var month = stamp.toLocaleString("default", { month: "long", timeZone: "UTC" });
            var timeString = (month + " " + String(stamp.getUTCDate()).padStart(2, "0") + ", " + stamp.getUTCFullYear() + " - " + String(stamp.getUTCHours()).padStart(2, "0") + ":" + String(stamp.getUTCMinutes()).padStart(2, "0") + ":" + String(stamp.getUTCSeconds()).padStart(2, "0") + " UTC");
            
            $("#modal-row-timestamp").text(timeString);
            $("#modal-row-time-since").text(timeSince(stamp));
            
            $("#modal-row-actor").text(result["actor"]);
            $("#modal-row-true-ip").text(result["trueip"]);
            $("#modal-row-forwarded-ip").text(result["forwardip"]);
            $("#modal-row-type").text(result["type"]);
            $("#modal-row-page").text(result["page"]);
            $("#modal-row-details").text(result["details"]);
            
            $("#modal-spinner").attr("hidden", true);
            $("#modal-timing").removeAttr("hidden");
            $("#modal-data").removeAttr("hidden");
            
        },
        error: function(result) {
            
            $("#modal-spinner").attr("hidden", true);
            $("#modal-error").removeAttr("hidden");
            
        }
    });
    
}