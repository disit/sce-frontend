<?php
/* Smart Cloud Engine Web Interface
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
  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA. */
include_once "header.php"; //include header
include_once "settings.php";
global $config;
?>
<html>
    <head> 
        <title>Cluster Nodes Status</title> 
        <link rel="stylesheet" type="text/css" href="css/reset.css" />
        <link rel="stylesheet" type="text/css" href="css/style.css" />
        <link rel="stylesheet" type="text/css" href="css/typography.css" />
        <script type="text/javascript" src="javascript/sce.js"></script>
        <script type="text/javascript" src="javascript/jquery-2.1.0.min.js"></script>
        <script type="text/javascript">
            var global_sort_filters = ''; //used to store the global sort filters

            $(document).ready(function () {
                //refreshTable();
                loadTable();
            });

            function rfc3986EncodeURIComponent(str) {
                return encodeURIComponent(str).replace(/[!'()*]/g, escape);
            }

            function getSortFilter(sortFilters, variable) {
                //var query = window.location.search.substring(1);
                var query = sortFilters.substring(1);
                var vars = query.split('&');
                for (var i = 0; i < vars.length; i++) {
                    var pair = vars[i].split('=');
                    if (decodeURIComponent(pair[0]) == variable) {
                        return decodeURIComponent(pair[1]);
                    }
                }
            }

            function refreshTable() {
                //get text filter parameters
                var filters = '';
                var ampersand = '';
                $('.FILTER').each(function (index) {
                    filters += $(this).val() != '' ? (ampersand + $(this).attr('name') + '=' + encodeURIComponent($(this).val())) : '';
                    filters != '' ? ampersand = '&' : '';
                });
                //load table
                $('#tableHolder').load('nodes-status.php' + (location.search != '' ? (location.search + '&' + filters) : '?' + filters), function () {
                    setTimeout(refreshTable, <?php echo $config['refreshTime']; ?>);
                });
            }

            //called by clicking on a sort field in nodes-status.php and by $(document).ready of this php
            function loadTable(sortFilters) {
                //get text filter parameters
                var filters = '';
                $('.FILTER').each(function (index) {
                    filters += $(this).val() != '' ? ('&' + $(this).attr('name') + '=' + encodeURIComponent($(this).val())) : '';
                });
                //load table
                //if sortFilters is defined (called by nodes-status.php) set global sort filters with current values
                if (sortFilters) {
                    var orderBy = getSortFilter(sortFilters, 'orderby');
                    var sort = getSortFilter(sortFilters, 'sort');
                    var page = getSortFilter(sortFilters, 'page');
                    //global_sort_filters = "orderby=" + getSortFilter(sortFilters, 'orderby') + "&sort=" + getSortFilter(sortFilters, 'sort') + "&page=" + getSortFilter(sortFilters, 'page');
                    if (typeof orderBy != 'undefined' && typeof sort != 'undefined') {
                        global_sort_filters = "orderby=" + orderBy + "&sort=" + sort;
                        if (typeof page != 'undefined')
                            global_sort_filters += "&page=" + page;
                    } else if (typeof page != 'undefined')
                        global_sort_filters = "page=" + page;
                    $('#tableHolder').load('nodes-status.php' + sortFilters + filters);
                }
                else {
                    var parameters = '?';
                    parameters += global_sort_filters;
                    parameters += (global_sort_filters != '' ? filters : (filters != '' ? filters.substr(1) : ''));
                    $('#tableHolder').load('nodes-status.php' + parameters, function () {
                        setTimeout(loadTable, <?php echo $config['refreshTime']; ?>);
                    });
                }
            }
        </script>
    </head>
    <body>
        <br>
        <div id='filtersTable'>
            <table>
                <tr class='even' >
                    <td><input type="text" name="FILTER_ID" class="FILTER" id="FILTER_ID"></td>
                    <td><input type="text" name="FILTER_DATE" class="FILTER" id="FILTER_DATE"></td>
                    <td><input type="text" name="FILTER_IP_ADDRESS" class="FILTER" id="FILTER_ID"></td>
                    <td><input type="text" name="FILTER_SCHEDULER_INSTANCE_ID" class="FILTER" id="FILTER_SCHEDULER_INSTANCE_ID"></td>
                    <td><input type="text" name="FILTER_CPU_LOAD" class="FILTER" id="FILTER_CPU_LOAD"></td>
                    <td><input type="text" name="FILTER_FREE_PHYSICAL_MEMORY" class="FILTER" id="FILTER_FREE_PHYSICAL_MEMORY"></td>
                    <td><input type="text" name="FILTER_JOBS_EXECUTED" class="FILTER" id="FILTER_JOBS_EXECUTED"></td>
                    <td><input type="text" name="FILTER_SCHED_NAME" class="FILTER" id="FILTER_SCHED_NAME"></td>
                    <td><input type="text" name="FILTER_RUNNING_SINCE" class="FILTER" id="FILTER_RUNNING_SINCE"></td>
                    <td><input type="text" name="FILTER_CLUSTERED" class="FILTER" id="FILTER_CLUSTERED"></td>
                    <td><input type="text" name="FILTER_PERSISTENCE" class="FILTER" id="FILTER_PERSISTENCE"></td>
                    <td><input type="text" name="FILTER_REMOTE_SCHEDULER" class="FILTER" id="FILTER_REMOTE_SCHEDULER"></td>
                    <td><input type="text" name="FILTER_CURRENTLY_EXECUTING_JOBS" class="FILTER" id="FILTER_CURRENTLY_EXECUTING_JOBS"></td>
                    <td><input type="text" name="FILTER_CPU_LOAD_JVM" class="FILTER" id="FILTER_CPU_LOAD_JVM"></td>
                    <td><input type="text" name="FILTER_SYSTEM_LOAD_AVERAGE" class="FILTER" id="FILTER_SYSTEM_LOAD_AVERAGE"></td>
                    <td><input type="text" name="FILTER_OPERATING_SYSTEM_VERSION" class="FILTER" id="FILTER_OPERATING_SYSTEM_VERSION"></td>
                    <td><input type="text" name="FILTER_COMMITTED_VIRTUAL_MEMORY" class="FILTER" id="FILTER_COMMITTED_VIRTUAL_MEMORY"></td>
                    <td><input type="text" name="FILTER_OPERATING_SYSTEM_NAME" class="FILTER" id="FILTER_OPERATING_SYSTEM_NAME"></td>
                    <td><input type="text" name="FILTER_FREE_SWAP_SPACE" class="FILTER" id="FILTER_FREE_SWAP_SPACE"></td>
                    <td><input type="text" name="FILTER_PROCESS_CPU_TIME" class="FILTER" id="FILTER_PROCESS_CPU_TIME"></td>
                    <td><input type="text" name="FILTER_TOTAL_PHYSICAL_MEMORY" class="FILTER" id="FILTER_TOTAL_PHYSICAL_MEMORY"></td>
                    <td><input type="text" name="FILTER_NUMBER_OF_PROCESSORS" class="FILTER" id="FILTER_NUMBER_OF_PROCESSORS"></td>
                    <td><input type="text" name="FILTER_OPERATING_SYSTEM_ARCHITECTURE" class="FILTER" id="FILTER_OPERATING_SYSTEM_ARCHITECTURE"></td>
                    <td><input type="text" name="FILTER_TOTAL_SWAP_SPACE" class="FILTER" id="FILTER_TOTAL_SWAP_SPACE"></td>
                    <td><input type="text" name="FILTER_IS_SCHEDULER_STANDBY" class="FILTER" id="FILTER_IS_SCHEDULER_STANDBY"></td>
                    <td><input type="text" name="FILTER_IS_SCHEDULER_SHUTDOWN" class="FILTER" id="FILTER_IS_SCHEDULER_SHUTDOWN"></td>
                    <td><input type="text" name="FILTER_IS_SCHEDULER_STARTED" class="FILTER" id="FILTER_IS_SCHEDULER_STARTED"></td>
                    <td><input type="text" name="FILTER_TOTAL_DISK_SPACE" class="FILTER" id="FILTER_TOTAL_DISK_SPACE"></td>
                    <td><input type="text" name="FILTER_UNALLOCATED_DISK_SPACE" class="FILTER" id="FILTER_UNALLOCATED_DISK_SPACE"></td>
                    <td><input type="text" name="FILTER_USABLE_DISK_SPACE" class="FILTER" id="FILTER_USABLE_DISK_SPACE"></td>
                </tr>
            </table>
        </div>
        <div id="tableHolder"></div>
        <br><a class="pointer button" title="View the job list" href="jobs.php">Jobs</a>&emsp;
        <a class="pointer button" title="View the trigger list" href="triggers.php">Triggers</a>&emsp;
        <a class="pointer button" title="Create a new job" href="newJob.php">New Job</a>&emsp;
        <a class="pointer button" title="Create a new job without trigger" href="newJob.php?dormantJob">New Job (dormant)</a>&emsp;
        <a class="pointer button" title="Create a new trigger" href="newTrigger.php">New Trigger</a>&emsp;
        <a class="pointer button" title="Starts the Scheduler's threads that fire Triggers. When a scheduler is first created it is in 'stand-by' mode, and will not fire triggers. The scheduler can also be put into stand-by mode by clicking 'Standby Scheduler'. The misfire/recovery process will be started, if it is the initial call to this action on this scheduler instance." href="#" onclick="startScheduler();
                return false;">Start Scheduler</a>&emsp;
        <a class="pointer button" title="Temporarily halts the Scheduler's firing of Triggers. When 'Start Scheduler' is called (to bring the scheduler out of stand-by mode), trigger misfire instructions will NOT be applied during the start - any misfires will be detected immediately afterward. The scheduler can be re-started at any time" href="#" onclick="standbyScheduler();
                return false;">Standby Scheduler</a>&emsp;
        <a class="pointer button" title="Halts the Scheduler's firing of Triggers, and cleans up all resources associated with the Scheduler, waiting jobs to complete (the scheduler cannot be re-started and requires Tomcat restart)" href="#" onclick="shutdownScheduler();
                return false;">Shutdown Scheduler</a>&emsp;
        <a class="pointer button" title="Halts the Scheduler's firing of Triggers, and cleans up all resources associated with the Scheduler (the scheduler cannot be re-started and requires Tomcat restart)" href="#" onclick="forceShutdownScheduler();
                return false;">Force Shutdown Scheduler</a>&emsp;
        <a class="pointer button" title="Pause all triggers, after using this method 'Resume Triggers' must be called to clear the scheduler's state of 'remembering' that all new triggers will be paused as they are added" href="#" onclick="pauseAll();
                return false;">Pause Triggers</a>&emsp;
        <a class="pointer button" title="Resume (un-pause) all triggers on every group" href="#" onclick="resumeAll();
                return false;">Resume Triggers</a>&emsp;
        <a class="pointer button" title="View the nodes status" href="nodes-status-static.php">Nodes Status</a>&emsp;
        <a class="pointer button" title="View the nodes status log" href="nodes-static.php">Nodes Log</a>&emsp;
        <a class="pointer button" title="View the log" href="log-static.php">Log</a>&emsp;
        <a class="pointer button" title="Truncate the catalina log file of this scheduler" href="#" onclick="truncateCatalinaLog();
                return false;">Truncate Catalina Log</a>&emsp;
        <br><br>
        <!--<a class="pointer" title="Back" href="#" onclick="history.back();">Back</a>&emsp;-->
        <a class="pointer button" title="Back" href="#" onclick="if (document.referrer) {
                    window.open(document.referrer, '_self');
                } else {
                    history.go(-1);
                }
                return false;">Back</a>&emsp;
        <a class="pointer button" title="Home" href="index.php">Home</a>&emsp;
        <a class="pointer button" title="Clears (deletes) all scheduling data - all Jobs, Triggers, Calendars" href="#" onclick="clearScheduler();
                return false;">Clear Scheduler</a>&emsp;
        <br><br>
    </body>
</html>
