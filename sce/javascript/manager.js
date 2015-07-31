/*Smart Cloud Engine Web Interface
 Copyright (C) 2015 DISIT Lab http://www.disit.org - University of Florence
 
 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 
 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.*/
function manager(id)
{
    $.ajax({
        type: "POST",
        url: "manager.php",
        data: {id: id},
        success: function (msg) {
            alert(msg);
            location.reload(true);
        },
        error: function () {
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
        success: function (msg) {
            alert("Building Jobs: " + msg);
            location.reload(true);
        },
        /*error: function(msg) {
         alert("Error");
         }*/
        error: function (jqXHR, textStatus, errorThrown) {
            alert(textStatus + " " + errorThrown);
        }
    });
}
//update the jobs for the SLAs in the KB
function updateJobs()
{
    if (!confirmationDialog("Update Jobs"))
        return;

    $.ajax({
        type: "POST",
        url: "manager.php",
        data: {id: 'updateJobs'},
        success: function (msg) {
            alert("Updating Jobs: " + msg);
            location.reload(true);
        },
        /*error: function(msg) {
         alert("Error");
         }*/
        error: function (jqXHR, textStatus, errorThrown) {
            alert(textStatus + " " + errorThrown);
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