function sce(id)
{
    $.ajax({
        type: "POST",
        url: "sce.php",
        data: {id: id},
        success: function(msg) {
            alert(msg);
            location.reload(true);
        },
        error: function() {
            alert("Error");
        }
    });
}
//Delete the identified Job from the Scheduler and any associated Trigger
function deleteJob(jobName, jobGroup)
{
    if (!confirmationDialog("Delete Job"))
        return;

    $.ajax({
        type: "POST",
        url: "sce.php",
        data: {id: 'deleteJob', jobName: jobName, jobGroup: jobGroup},
        success: function(msg) {
            alert("Deleting Job " + jobName + "." + jobGroup + ": " + msg);
            location.reload(true);
        },
        error: function() {
            alert("Error");
        }
    });
}
function resumeJob(jobName, jobGroup)
{
    $.ajax({
        type: "POST",
        url: "sce.php",
        data: {id: 'resumeJob', jobName: jobName, jobGroup: jobGroup},
        success: function(msg) {
            alert("Resuming Job " + jobName + "." + jobGroup + ": " + msg);
            location.reload(true);
        },
        error: function() {
            alert("Error");
        }
    });
}
function pauseJob(jobName, jobGroup)
{
    $.ajax({
        type: "POST",
        url: "sce.php",
        data: {id: 'pauseJob', jobName: jobName, jobGroup: jobGroup},
        success: function(msg) {
            alert("Pausing Job " + jobName + "." + jobGroup + ": " + msg);
            location.reload(true);
        },
        error: function() {
            alert("Error");
        }
    });
}
function interruptJob(jobName, jobGroup)
{
    $.ajax({
        type: "POST",
        url: "sce.php",
        data: {id: 'interruptJob', jobName: jobName, jobGroup: jobGroup},
        success: function(msg) {
            alert("Interrupting Job " + jobName + "." + jobGroup + ": " + msg);
            location.reload(true);
        },
        error: function() {
            alert("Error");
        }
    });
}
//Remove the indicated Trigger from the scheduler
function unscheduleJob(triggerName, triggerGroup)
{
    if (!confirmationDialog("Delete Trigger"))
        return;

    $.ajax({
        type: "POST",
        url: "sce.php",
        data: {id: 'unscheduleJob', triggerName: triggerName, triggerGroup: triggerGroup},
        success: function(msg) {
            alert("Deleting Trigger " + triggerName + "." + triggerGroup + ": " + msg);
            location.reload(true);
        },
        error: function() {
            alert("Error");
        }
    });
}
//trigger the job
function triggerJob(jobName, jobGroup)
{
    $.ajax({
        type: "POST",
        url: "sce.php",
        data: {id: 'triggerJob', jobName: jobName, jobGroup: jobGroup},
        success: function(msg) {
            alert("Triggering Job " + jobName + "." + jobGroup + ": " + msg);
            location.reload(true);
        },
        error: function() {
            alert("Error");
        }
    });
}
function resumeTrigger(triggerName, triggerGroup)
{
    $.ajax({
        type: "POST",
        url: "sce.php",
        data: {id: 'resumeTrigger', triggerName: triggerName, triggerGroup: triggerGroup},
        success: function(msg) {
            alert("Resuming Trigger " + triggerName + "." + triggerGroup + ": " + msg);
            location.reload(true);
        },
        error: function() {
            alert("Error");
        }
    });
}
function pauseTrigger(triggerName, triggerGroup)
{
    $.ajax({
        type: "POST",
        url: "sce.php",
        data: {id: 'pauseTrigger', triggerName: triggerName, triggerGroup: triggerGroup},
        success: function(msg) {
            alert("Pausing Trigger " + triggerName + "." + triggerGroup + ": " + msg);
            location.reload(true);
        },
        error: function() {
            alert("Error");
        }
    });
}
function startScheduler()
{
    if (!confirmationDialog("Start Scheduler"))
        return;

    $.ajax({
        type: "POST",
        url: "sce.php",
        data: {id: 'startScheduler'},
        success: function(msg) {
            alert("Starting Scheduler : " + msg);
            location.reload(true);
        },
        error: function() {
            alert("Error");
        }
    });
}
function standbyScheduler()
{
    if (!confirmationDialog("Standby Scheduler"))
        return;

    $.ajax({
        type: "POST",
        url: "sce.php",
        data: {id: 'standbyScheduler'},
        success: function(msg) {
            alert("Standby Scheduler: " + msg);
            location.reload(true);
        },
        error: function() {
            alert("Error");
        }
    });
}
function shutdownScheduler()
{
    if (!confirmationDialog("Shutdown Scheduler"))
        return;

    $.ajax({
        type: "POST",
        url: "sce.php",
        data: {id: 'shutdownScheduler'},
        success: function(msg) {
            alert("Shutdown Scheduler: " + msg);
            location.reload(true);
        },
        error: function() {
            alert("Error");
        }
    });
}
function forceShutdownScheduler()
{
    if (!confirmationDialog("Force Shutdown Scheduler"))
        return;

    $.ajax({
        type: "POST",
        url: "sce.php",
        data: {id: 'forceShutdownScheduler'},
        success: function(msg) {
            alert("Shutdown Scheduler: " + msg);
            location.reload(true);
        },
        error: function() {
            alert("Error");
        }
    });
}
function clearScheduler()
{
    if (!confirmationDialog("Clear Scheduler"))
        return;

    $.ajax({
        type: "POST",
        url: "sce.php",
        data: {id: 'clearScheduler'},
        success: function(msg) {
            alert("Clear Scheduler: " + msg);
            location.reload(true);
        },
        error: function() {
            alert("Error");
        }
    });
}
function pauseAll()
{
    if (!confirmationDialog("Pause Triggers"))
        return;

    $.ajax({
        type: "POST",
        url: "sce.php",
        data: {id: 'pauseAll'},
        success: function(msg) {
            alert("Pausing Triggers: " + msg);
            location.reload(true);
        },
        error: function() {
            return "Error";
        }
    });
}
function resumeAll()
{
    if (!confirmationDialog("Resume Triggers"))
        return;

    $.ajax({
        type: "POST",
        url: "sce.php",
        data: {id: 'resumeAll'},
        success: function(msg) {
            alert("Resuming Triggers: " + msg);
            location.reload(true);
        },
        error: function() {
            return "Error";
        }
    });
}
function truncateCatalinaLog()
{
    if (!confirmationDialog("Truncate Catalina Log"))
        return;

    $.ajax({
        type: "POST",
        url: "sce.php",
        data: {id: 'truncateCatalinaLog'},
        success: function(msg) {
            alert("Truncate Catalina Log : " + msg);
            location.reload(true);
        },
        error: function() {
            alert("Error");
        }
    });
}

//toggle link and icon
function toggle(id)
{
    if (document.getElementById(id).getAttribute("onClick") === "#DIV1") {
        var img = document.getElementsByTagName("img")[0];
        img.setAttribute("src", "icons/play.png");
        img.parentNode.setAttribute("href", "#DIV2");
    }
    else
    {
        var img = document.getElementsByTagName("img")[0];
        img.setAttribute("src", "icons/pause.png");
        img.parentNode.setAttribute("href", "#DIV1");
    }

}

//post data to postTrigger.php to schedule/reschedule a job with a new/modified trigger
function postTrigger(startNow, startAt, endAt, jobName, jobGroup, modifiedByCalendar, triggerDescription, triggerName, triggerGroup, priority, repeatCount, intervalInSeconds, misfire, action)
{
    $.ajax({
        type: "POST",
        url: "postTrigger.php",
        data: {startNow: startNow, startAt: startAt, endAt: endAt, jobName: jobName, jobGroup: jobGroup, modifiedByCalendar: modifiedByCalendar, triggerDescription: triggerDescription, triggerName: triggerName, triggerGroup: triggerGroup, priority: priority, repeatCount: repeatCount, intervalInSeconds: intervalInSeconds, misfire: misfire, action: action},
        success: function(msg) {
            alert(msg);
            //location.reload(true);
        },
        error: function() {
            return "Error";
        }
    });
}

function confirmationDialog(title) {
    if (confirm(title + "\n\nAre you sure?")) {
        return true;
    } else {
        return false;
    }
}