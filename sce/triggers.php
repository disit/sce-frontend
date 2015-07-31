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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"> 
    <head> 
        <title>Triggers</title> 
        <link rel="stylesheet" type="text/css" href="css/reset.css" />
        <link rel="stylesheet" type="text/css" href="css/style.css" />
        <link rel="stylesheet" type="text/css" href="css/typography.css" />
        <script type="text/javascript" src="javascript/sce.js"></script>
        <script type="text/javascript" src="javascript/jquery-2.1.0.min.js"></script>
    </head> 
    <body>
        <?php
        include_once "header.php"; //include header
        include_once "settings.php";
        global $config;
        //DATABASE SETTINGS
        //$config['host'] = "localhost";
        //$config['user'] = "root";
        //$config['pass'] = "centos";
        //$config['database'] = "quartz";
        $config['table'] = "QRTZ_TRIGGERS";
        //$config['nicefields'] = true; //true or false | "Field Name" or "field_name"
        //$config['perpage'] = 10;
        //$config['showpagenumbers'] = true; //true or false
        //$config['showprevnext'] = true; //true or false

        include './Pagination.php';
        $Pagination = new Pagination();

        //trigger states legend
        $trigger_waiting = "The normal state of a trigger, waiting for its fire time to arrive and be acquired for firing by a scheduler."; //WAITING 
        $trigger_paused = "It means that one of the scheduler.pause-() methods was used. The trigger is not eligible for being fired until it is resumed."; //PAUSED 
        $trigger_acquired = "A scheduler node has identified this trigger as the next trigger it will fire - may still be waiting for its fire time to arrive. After it fires the trigger will be updated (per its repeat settings, if any) and placed back into the WAITING state (or be deleted if it does not repeat again)."; //ACQUIRED 
        $trigger_blocked = "The trigger is prevented from being fired because it relates to a StatefulJob that is already executing. When the stateful job completes its execution, all triggers relating to that job will return to the WAITING state."; //BLOCKED 
        //misfire instructions legend
        //value = -1
        $misfire_instruction_ignore_misfire_policy = "MISFIRE_INSTRUCTION_IGNORE_MISFIRE_POLICY: instructs the Scheduler that the Trigger will never be evaluated for a misfire situation, and that the scheduler will simply try to fire it as soon as it can, and then update the Trigger as if it had fired at the proper time. If a trigger uses this instruction, and it has missed several of its scheduled firings, then several rapid firings may occur as the trigger attempt to catch back up to where it would have been. For example, a SimpleTrigger that fires every 15 seconds which has misfired for 5 minutes will fire 20 times once it gets the chance to fire.";
        //value = 0
        $misfire_instruction_smart_policy = "MISFIRE_INSTRUCTION_SMART_POLICY: instructs the Scheduler that upon a mis-fire situation, the update after misfire method will be called on the Trigger to determine the misfire instruction, which logic will be trigger-implementation-dependent.";
        //value = 1
        $misfire_instruction_fire_now = "MISFIRE_INSTRUCTION_FIRE_NOW: instructs the Scheduler that upon a misfire situation, the SimpleTrigger wants to be fired now by Scheduler. This instruction should typically only be used for \"one-shot\" (non-repeating) Triggers. If it is used on a trigger with a repeat count &gt; 0 then it is equivalent to the instruction.";
        //value = 2
        $misfire_instruction_reschedule_now_with_existing_repeat_count = "MISFIRE_INSTRUCTION_RESCHEDULE_NOW_WITH_EXISTING_REPEAT_COUNT: instructs the Scheduler that upon a misfire situation, the SimpleTrigger wants to be re-scheduled to \"now\" (even if the associated Calendar excludes \"now\") with the repeat count left as-is. This does obey the Trigger end-time however, so if \"now\" is after the end-time the Trigger will not fire again. Use of this instruction causes the trigger to \"forget\" the start-time and repeat-count that it was originally setup with (this is only an issue if you for some reason wanted to be able to tell what the original values were at some later time).";
        //value = 3
        $misfire_instruction_reschedule_now_with_remaining_repeat_count = "MISFIRE_INSTRUCTION_RESCHEDULE_NOW_WITH_REMAINING_REPEAT_COUNT: instructs the Scheduler that upon a misfire situation, the SimpleTrigger wants to be re-scheduled to \"now\" (even if the associated Calendar excludes \"now\") with the repeat count set to what it would be, if it had not missed any firings. This does obey the Trigger end-time however, so if \"now\" is after the end-time the Trigger will not fire again. Use of this instruction causes the trigger to \"forget\" the start-time and repeat-count that it was originally setup with. Instead, the repeat count on the trigger will be changed to whatever the remaining repeat count is (this is only an issue if you for some reason wanted to be able to tell what the original values were at some later time). This instruction could cause the Trigger to go to the \"COMPLETE\" state after firing \"now\", if all the repeat-fire-times where missed.";
        //value = 4
        $misfire_instruction_reschedule_next_with_remaining_count = "MISFIRE_INSTRUCTION_RESCHEDULE_NEXT_WITH_REMAINING_COUNT: instructs the Scheduler that upon a misfire situation, the SimpleTrigger wants to be re-scheduled to the next scheduled time after \"now\" - taking into account any associated Calendar, and with the repeat count set to what it would be, if it had not missed any firings. This instruction could cause the Trigger to go directly to the \"COMPLETE\" state if all fire-times where missed.";
        //value = 5
        $misfire_instruction_reschedule_next_with_existing_count = "MISFIRE_INSTRUCTION_RESCHEDULE_NEXT_WITH_EXISTING_COUNT: instructs the Scheduler that upon a mis-fire situation, the SimpleTrigger wants to be re-scheduled to the next scheduled time after \"now\" - taking into account any associated Calendar, and with the repeat count left unchanged. This instruction could cause the Trigger to go directly to the \"COMPLETE\" state if the end-time of the trigger has arrived.";
        //CONNECT
        $link = mysqli_connect($config['host'], $config['user'], $config['pass'], $config['database']);

        /* check connection */
        if (mysqli_connect_errno()) {
            printf("Connection failed: %s\n", mysqli_connect_error());
            exit();
        }

        //get total rows, apply filter for jobName and jobGroup if this php is called by clicking on the View Triggers icon for a job in the index.php
        $filter = isset($_GET["jobName"]) && isset($_GET["jobGroup"]) ? " WHERE JOB_NAME='" . $_GET["jobName"] . "' AND JOB_GROUP='" . $_GET["jobGroup"] . "' " : "";
        $totalrows = mysqli_fetch_array(mysqli_query($link, "SELECT COUNT(*) AS total FROM " . $config['table'] . $filter));

        //IF TRIGGER TABLE IS EMPTY DISPLAY ONLY THE MENU
        if ($totalrows['total'] == 0) {
            echo "<div id='resultsTable'>";
            echo "Trigger List is empty.<br><br>";
            echo "<a class=\"pointer button\" title=\"View the job list\" href=\"jobs.php\">Jobs</a>&emsp;";
            echo "<a class=\"pointer button\" title=\"View the trigger list\" href=\"triggers.php\">Triggers</a>&emsp;";
            echo "<a class=\"pointer button\" title=\"Create a new job\" href=\"newJob.php\">New Job</a>&emsp;";
            echo "<a class=\"pointer button\" title=\"Create a new job without trigger\" href=\"newJob.php?dormantJob\">New Job (dormant)</a>&emsp;";
            echo "<a class=\"pointer button\" title=\"Create a new trigger\" href=\"newTrigger.php\">New Trigger</a>&emsp;";
            echo "<a class=\"pointer button\" title=\"Starts the Scheduler's threads that fire Triggers. When a scheduler is first created it is in 'stand-by' mode, and will not fire triggers. The scheduler can also be put into stand-by mode by clicking 'Standby Scheduler'. The misfire/recovery process will be started, if it is the initial call to this action on this scheduler instance.\" href=\"#\" onclick=\"startScheduler();return false;\">Start Scheduler</a>&emsp;";
            echo "<a class=\"pointer button\" title=\"Temporarily halts the Scheduler's firing of Triggers. When 'Start Scheduler' is called (to bring the scheduler out of stand-by mode), trigger misfire instructions will NOT be applied during the start - any misfires will be detected immediately afterward. The scheduler can be re-started at any time\" href=\"#\" onclick=\"standbyScheduler();return false;\">Standby Scheduler</a>&emsp;";
            echo "<a class=\"pointer button\" title=\"Halts the Scheduler's firing of Triggers, and cleans up all resources associated with the Scheduler, waiting jobs to complete (the scheduler cannot be re-started and requires Tomcat restart)\" href=\"#\" onclick=\"shutdownScheduler();return false;\">Shutdown Scheduler</a>&emsp;";
            echo "<a class=\"pointer button\" title=\"Halts the Scheduler's firing of Triggers, and cleans up all resources associated with the Scheduler (the scheduler cannot be re-started and requires Tomcat restart)\" href=\"#\" onclick=\"forceShutdownScheduler();return false;\">Force Shutdown Scheduler</a>&emsp;";
            echo "<a class=\"pointer button\" title=\"Pause all triggers, after using this method 'Resume Triggers' must be called to clear the scheduler's state of 'remembering' that all new triggers will be paused as they are added\" href=\"#\" onclick=\"pauseAll();return false;\">Pause Triggers</a>&emsp;";
            echo "<a class=\"pointer button\" title=\"Resume (un-pause) all triggers on every group\" href=\"#\" onclick=\"resumeAll();return false;\">Resume Triggers</a>&emsp;";
            echo "<a class=\"pointer button\" title=\"View the nodes status\" href=\"nodes-status-static.php\">Nodes Status</a>&emsp;";
            echo "<a class=\"pointer button\" title=\"View the nodes status log\" href=\"nodes-static.php\">Nodes Log</a>&emsp;";
            echo "<a class=\"pointer button\" title=\"View the log\" href=\"log-static.php\">Log</a>&emsp;";
            echo "<a class=\"pointer button\" title=\"Truncate the catalina log file of this scheduler\" href=\"#\" onclick=\"truncateCatalinaLog();return false;\">Truncate Catalina Log</a>&emsp;";
            echo "<br><br>";
            echo "<a class=\"pointer button\" title=\"Back\" href=\"#\" onclick=\"if(document.referrer) {window.open(document.referrer,'_self');} else {history.go(-1);}return false;\">Back</a>&emsp;\n";
            echo "<a class=\"pointer button\" title=\"Home\" href=\"index.php\">Home</a>&emsp;\n";
            echo "<a class=\"pointer button\" title=\"Clears (deletes) all scheduling data - all Jobs, Triggers, Calendars\" href=\"#\" onclick=\"clearScheduler();return false;\">Clear Scheduler</a>&emsp;";
            echo "<br><br>";
            echo "</div>";
            echo "</body>";
            echo "</html>";
            exit();
        }

        //limit per page, what is current page, define first record for page
        $limit = $config['perpage'];
        if (isset($_GET['page']) && is_numeric(trim($_GET['page']))) {
            //$page = mysqli_real_escape_string($_GET['page']);
            $page = $_GET['page'];
        } else {
            $page = 1;
        }
        $startrow = $Pagination->getStartRow($page, $limit);

        //create page links
        if ($config['showpagenumbers'] == true) {
            $pagination_links = $Pagination->showPageNumbers($totalrows['total'], $page, $limit, $config['pagelinks']); // add $config['pagelinks'] as a fourth parameter, to print only the first N page links (default = 50)
        } else {
            $pagination_links = null;
        }

        if ($config['showprevnext'] == true) {
            $prev_link = $Pagination->showPrev($totalrows['total'], $page, $limit);
            $prev_link_more = $Pagination->showPrevMore($totalrows['total'], $page, $limit);
            $next_link = $Pagination->showNext($totalrows['total'], $page, $limit);
            $next_link_more = $Pagination->showNextMore($totalrows['total'], $page, $limit);
        } else {
            $prev_link = null;
            $prev_link_more = null;
            $next_link = null;
            $next_link_more = null;
        }

        //IF ORDERBY NOT SET, SET DEFAULT
        if (!isset($_GET['orderby']) || trim($_GET['orderby']) == "") {
            //GET FIRST FIELD IN TABLE TO BE DEFAULT SORT
            $sql = "SELECT * FROM " . $config['table'] . " LIMIT 1";
            $result = mysqli_query($link, $sql) or die(mysqli_error());
            $array = mysqli_fetch_assoc($result);
            //first field
            $i = 0;
            foreach ($array as $key => $value) {
                if ($i > 0) {
                    break;
                } else {
                    $orderby = $key;
                }
                $i++;
            }
            //default sort
            $sort = "ASC";
        } else {

            //$orderby = mysqli_real_escape_string($_GET['orderby']);
            $orderby = $_GET['orderby'];
        }

        //IF SORT NOT SET OR VALID, SET DEFAULT
        if (!isset($_GET['sort']) || ($_GET['sort'] != "ASC" AND $_GET['sort'] != "DESC")) {
            //default sort
            $sort = "ASC";
        } else {
            //$sort = mysqli_real_escape_string($_GET['sort']);
            $sort = $_GET['sort'];
        }

        //GET DATA, eventually apply $filter
        //$sql = "SELECT * FROM " . $config['table'] . " ORDER BY $orderby $sort LIMIT $startrow,$limit";
        $sql = "SELECT QRTZ_TRIGGERS.SCHED_NAME, QRTZ_TRIGGERS.TRIGGER_NAME, QRTZ_TRIGGERS.TRIGGER_GROUP, QRTZ_TRIGGERS.JOB_NAME, QRTZ_TRIGGERS.JOB_GROUP, QRTZ_TRIGGERS.DESCRIPTION, QRTZ_TRIGGERS.NEXT_FIRE_TIME, QRTZ_TRIGGERS.PREV_FIRE_TIME, QRTZ_TRIGGERS.PRIORITY, QRTZ_TRIGGERS.TRIGGER_STATE, QRTZ_TRIGGERS.TRIGGER_TYPE, QRTZ_TRIGGERS.START_TIME, QRTZ_TRIGGERS.END_TIME, QRTZ_TRIGGERS.CALENDAR_NAME, QRTZ_TRIGGERS.MISFIRE_INSTR, QRTZ_TRIGGERS.JOB_DATA, QRTZ_SIMPLE_TRIGGERS.REPEAT_COUNT, QRTZ_SIMPLE_TRIGGERS.REPEAT_INTERVAL, QRTZ_SIMPLE_TRIGGERS.TIMES_TRIGGERED FROM quartz.QRTZ_TRIGGERS LEFT JOIN quartz.QRTZ_SIMPLE_TRIGGERS ON QRTZ_TRIGGERS.TRIGGER_NAME=QRTZ_SIMPLE_TRIGGERS.TRIGGER_NAME AND QRTZ_TRIGGERS.TRIGGER_GROUP=QRTZ_SIMPLE_TRIGGERS.TRIGGER_GROUP AND QRTZ_TRIGGERS.SCHED_NAME=QRTZ_SIMPLE_TRIGGERS.SCHED_NAME $filter ORDER BY QRTZ_TRIGGERS.SCHED_NAME, $orderby $sort LIMIT $startrow,$limit";
        $result = mysqli_query($link, $sql) or die(mysqli_error());

        //START TABLE AND TABLE HEADER
        echo "<div id='resultsTable'><table>\n<tr>";
        $array = mysqli_fetch_assoc($result);
        foreach ($array as $key => $value) {
            if ($config['nicefields']) {
                $field = ucwords(str_replace("_", " ", $key));
                //$field = ucwords($field);
            }

            $field = columnSortArrows($key, $field, $orderby, $sort);
            echo "<th>" . $field . "</th>\n";
        }
        echo "</tr>\n";

        //reset result pointer
        mysqli_data_seek($result, 0);

        //start first row style
        $tr_class = "class='odd'";

        //LOOP TABLE ROWS
        while ($row = mysqli_fetch_assoc($result)) {

            echo "<tr " . $tr_class . ">\n";

            foreach ($row as $field => $value) {
                if ($field != 'JOB_DATA') {
                    //add the edit, delete, resume, pause trigger icon links
                    if ($field == 'SCHED_NAME') {
                        echo "<td><a class=\"pointer\" title=\"Edit Trigger\" href=\"newTrigger.php?triggerName=" . $row['TRIGGER_NAME'] . "&triggerGroup=" . $row['TRIGGER_GROUP'] . "\"><img id='icon' src='images/edit.gif' alt='edit' height='14' width='14'/></a>"
                        . "<a class=\"pointer\" title=\"Delete Trigger\" onClick=\"unscheduleJob('" . $row['TRIGGER_NAME'] . "', '" . $row['TRIGGER_GROUP'] . "')\"><img id='icon' src='images/delete.gif' alt='delete' height='14' width='14'/></a>"
                        . "<a class=\"pointer\" title=\"Resume Trigger\" onClick=\"resumeTrigger('" . $row['TRIGGER_NAME'] . "', '" . $row['TRIGGER_GROUP'] . "')\"><img id='icon' src='images/play.png' alt='delete' height='14' width='14'/></a>"
                        . "<a class=\"pointer\" title=\"Pause Trigger\" onClick=\"pauseTrigger('" . $row['TRIGGER_NAME'] . "', '" . $row['TRIGGER_GROUP'] . "')\"><img id='icon' src='images/pause.png' alt='delete' height='14' width='14'/></a>" . $value . "</td>\n";
                    }
                    //format the time fields (NEXT_FIRE_TIME, PREV_FIRE_TIME, END_TIME), if timestamp=0 then print "never"
                    else if (strpos($field, '_TIME') !== false) {
                        echo "<td>" . ($value != 0 ? date('Y-m-d H:i:s', $value / 1000) : "never") . "</td>\n";
                    }
                    //add a title to <td> of TRIGGER_STATE column, to help the use understand the meaning of each trigger state (mouse overlay)
                    else if (strpos($field, 'TRIGGER_STATE') !== false) {
                        if (strpos($value, 'WAITING') !== false) {
                            echo "<td title='" . $trigger_waiting . "'>" . $value . "</td>\n";
                        } else if (strpos($value, 'PAUSED') !== false) {
                            echo "<td title='" . $trigger_paused . "'>" . $value . "</td>\n";
                        } else if (strpos($value, 'ACQUIRED') !== false) {
                            echo "<td title='" . $trigger_acquired . "'>" . $value . "</td>\n";
                        } else if (strpos($value, 'BLOCKED') !== false) {
                            echo "<td title='" . $trigger_blocked . "'>" . $value . "</td>\n";
                        }
                    }
                    //convert the repeat interval from milliseconds to seconds
                    else if (strpos($field, 'REPEAT_INTERVAL') !== false) {
                        echo "<td>" . $value / 1000 . "</td>\n";
                    }
                    //add a title to <td> of MISFIRE_INSTR column, to help the use understand the meaning of each trigger state (mouse overlay)
                    else if (strpos($field, 'MISFIRE_INSTR') !== false) {
                        if (strpos($value, '-1') !== false) {
                            echo "<td title='" . $misfire_instruction_ignore_misfire_policy . "'>" . $value . "</td>\n";
                        } else if (strpos($value, '0') !== false) {
                            echo "<td title='" . $misfire_instruction_smart_policy . "'>" . $value . "</td>\n";
                        } else if (strpos($value, '1') !== false) {
                            echo "<td title='" . $misfire_instruction_fire_now . "'>" . $value . "</td>\n";
                        } else if (strpos($value, '2') !== false) {
                            echo "<td title='" . $misfire_instruction_reschedule_now_with_existing_repeat_count . "'>" . $value . "</td>\n";
                        } else if (strpos($value, '3') !== false) {
                            echo "<td title='" . $misfire_instruction_reschedule_now_with_remaining_repeat_count . "'>" . $value . "</td>\n";
                        } else if (strpos($value, '4') !== false) {
                            echo "<td title='" . $misfire_instruction_reschedule_next_with_remaining_count . "'>" . $value . "</td>\n";
                        } else if (strpos($value, '5') !== false) {
                            echo "<td title='" . $misfire_instruction_reschedule_next_with_existing_count . "'>" . $value . "</td>\n";
                        }
                    } else if (strpos($field, 'JOB_NAME') !== false) {
                        echo "<td><a title=\"Edit Job\" href=\"newJob.php?jobName=" . $row['JOB_NAME'] . "&jobGroup=" . $row['JOB_GROUP'] . "\">" . $value . "</a>";
                    } else if (strpos($field, 'JOB_GROUP') !== false) {
                        echo "<td><a title=\"Edit Job\" href=\"newJob.php?jobName=" . $row['JOB_NAME'] . "&jobGroup=" . $row['JOB_GROUP'] . "\">" . $value . "</a>";
                    } else if (strpos($field, 'TRIGGER_NAME') !== false) {
                        echo "<td><a title=\"Edit Trigger\" href=\"newTrigger.php?triggerName=" . $row['TRIGGER_NAME'] . "&triggerGroup=" . $row['TRIGGER_GROUP'] . "\">" . $value . "</a>";
                    } else if (strpos($field, 'TRIGGER_GROUP') !== false) {
                        echo "<td><a title=\"Edit Trigger\" href=\"newTrigger.php?triggerName=" . $row['TRIGGER_NAME'] . "&triggerGroup=" . $row['TRIGGER_GROUP'] . "\">" . $value . "</a>";
                    } else {
                        echo "<td>" . $value . "</td>\n";
                    }
                } else {
                    echo "<td></td>\n";
                }
            }
            echo "</tr>\n";

            //switch row style
            if ($tr_class == "class='odd'") {
                $tr_class = "class='even'";
            } else {
                $tr_class = "class='odd'";
            }
        }

        mysqli_close($link); //close connection
        //END TABLE
        echo "</table></div>\n"; //close <div id='resultsTable'>

        if (!($prev_link == null && $next_link == null && $pagination_links == null)) {
            echo '<div class="pagination">' . "\n";
            echo $prev_link_more;
            echo $prev_link;
            echo $pagination_links;
            echo $next_link;
            echo $next_link_more;
            echo '<div style="clear:both;"></div>' . "\n";
            echo "</div>\n";

            echo "<br><a class=\"pointer button\" title=\"View the job list\" href=\"jobs.php\">Jobs</a>&emsp;";
            echo "<a class=\"pointer button\" title=\"View the trigger list\" href=\"triggers.php\">Triggers</a>&emsp;";
            echo "<a class=\"pointer button\" title=\"Create a new job\" href=\"newJob.php\">New Job</a>&emsp;";
            echo "<a class=\"pointer button\" title=\"Create a new job without trigger\" href=\"newJob.php?dormantJob\">New Job (dormant)</a>&emsp;";
            echo "<a class=\"pointer button\" title=\"Create a new trigger\" href=\"newTrigger.php\">New Trigger</a>&emsp;";
            echo "<a class=\"pointer button\" title=\"Starts the Scheduler's threads that fire Triggers. When a scheduler is first created it is in 'stand-by' mode, and will not fire triggers. The scheduler can also be put into stand-by mode by clicking 'Standby Scheduler'. The misfire/recovery process will be started, if it is the initial call to this action on this scheduler instance.\" href=\"#\" onclick=\"startScheduler();return false;\">Start Scheduler</a>&emsp;";
            echo "<a class=\"pointer button\" title=\"Temporarily halts the Scheduler's firing of Triggers. When 'Start Scheduler' is called (to bring the scheduler out of stand-by mode), trigger misfire instructions will NOT be applied during the start - any misfires will be detected immediately afterward. The scheduler can be re-started at any time\" href=\"#\" onclick=\"standbyScheduler();return false;\">Standby Scheduler</a>&emsp;";
            echo "<a class=\"pointer button\" title=\"Halts the Scheduler's firing of Triggers, and cleans up all resources associated with the Scheduler, waiting jobs to complete (the scheduler cannot be re-started and requires Tomcat restart)\" href=\"#\" onclick=\"shutdownScheduler();return false;\">Shutdown Scheduler</a>&emsp;";
            echo "<a class=\"pointer button\" title=\"Halts the Scheduler's firing of Triggers, and cleans up all resources associated with the Scheduler (the scheduler cannot be re-started and requires Tomcat restart)\" href=\"#\" onclick=\"forceShutdownScheduler();return false;\">Force Shutdown Scheduler</a>&emsp;";
            echo "<a class=\"pointer button\" title=\"Pause all triggers, after using this method 'Resume Triggers' must be called to clear the scheduler's state of 'remembering' that all new triggers will be paused as they are added\" href=\"#\" onclick=\"pauseAll();return false;\">Pause Triggers</a>&emsp;";
            echo "<a class=\"pointer button\" title=\"Resume (un-pause) all triggers on every group\" href=\"#\" onclick=\"resumeAll();return false;\">Resume Triggers</a>&emsp;";
            echo "<a class=\"pointer button\" title=\"View the nodes status\" href=\"nodes-status-static.php\">Nodes Status</a>&emsp;";
            echo "<a class=\"pointer button\" title=\"View the nodes status log\" href=\"nodes-static.php\">Nodes Log</a>&emsp;";
            echo "<a class=\"pointer button\" title=\"View the log\" href=\"log-static.php\">Log</a>&emsp;";
            echo "<a class=\"pointer button\" title=\"Truncate the catalina log file of this scheduler\" href=\"#\" onclick=\"truncateCatalinaLog();return false;\">Truncate Catalina Log</a>&emsp;";
            echo "<br><br>";
            //echo "<a class=\"pointer\" title=\"Back\" href=\"#\" onclick=\"history.back();\">Back</a>&emsp;\n";
            echo "<a class=\"pointer button\" title=\"Back\" href=\"#\" onclick=\"if(document.referrer) {window.open(document.referrer,'_self');} else {history.go(-1);}return false;\">Back</a>&emsp;\n";
            echo "<a class=\"pointer button\" title=\"Home\" href=\"index.php\">Home</a>&emsp;\n";
            echo "<a class=\"pointer button\" title=\"Clears (deletes) all scheduling data - all Jobs, Triggers, Calendars\" href=\"#\" onclick=\"clearScheduler();return false;\">Clear Scheduler</a>&emsp;";
            echo "<br><br>";
        }

        /* FUNCTIONS */

        function columnSortArrows($field, $text, $currentfield = null, $currentsort = null) {
            //defaults all field links to SORT ASC
            //if field link is current ORDERBY then make arrow and opposite current SORT

            $sortquery = "sort=ASC";
            $orderquery = "orderby=" . $field;

            if ($currentsort == "ASC") {
                $sortquery = "sort=DESC";
                $sortarrow = '<img src="images/arrow_up.png" />';
            }

            if ($currentsort == "DESC") {
                $sortquery = "sort=ASC";
                $sortarrow = '<img src="images/arrow_down.png" />';
            }

            if ($currentfield == $field) {
                $orderquery = "orderby=" . $field;
            } else {
                $sortarrow = null;
            }

            return '<a href="?' . $orderquery . '&' . $sortquery . '">' . $text . '</a> ' . $sortarrow;
        }
        ?>
    </body>
</html>