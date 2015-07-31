function manager(id)
{
    $.ajax({
        type: "POST",
        url: "manager.php",
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
//build the jobs for the SLAs in the KB
function buildJobs()
{
    if (!confirmationDialog("Build Jobs"))
        return;
    
    $.ajax({
        type: "POST",
        url: "manager.php",
        data: {id: 'buildJobs'},
        success: function(msg) {
            alert("Building Jobs: " + msg);
            location.reload(true);
        },
        error: function() {
            alert("Error");
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