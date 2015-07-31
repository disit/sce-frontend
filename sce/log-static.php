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
        <title>Log</title> 
        <link rel="stylesheet" type="text/css" href="css/reset.css" />
        <link rel="stylesheet" type="text/css" href="css/typography.css" />
        <link rel="stylesheet" type="text/css" href="css/style.css" />
        <script type="text/javascript" src="javascript/sce.js"></script>
        <script src="javascript/jquery-2.1.0.min.js"></script>
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
        $config['table'] = "QRTZ_LOGS";
        //$config['nicefields'] = true; //true or false | "Field Name" or "field_name"
        //$config['perpage'] = 30; // rows per page
        //$config['pagelinks'] = 50; // max number of page links, if not set 50 will be used as a default value when calling $Pagination->showPageNumbers  
        //$config['showpagenumbers'] = true; //true or false
        //$config['showprevnext'] = true; //true or false
        //old log table schema (for log4j)
        /* DROP TABLE IF EXISTS `quartz`.`QRTZ_LOGS`;
          CREATE TABLE  `quartz`.`QRTZ_LOGS` (
          `USER_ID` varchar(100) NOT NULL,
          `DATE` datetime NOT NULL,
          `LOGGER` varchar(1000) NOT NULL,
          `LEVEL` varchar(10) NOT NULL,
          `MESSAGE` text NOT NULL
          ) ENGINE=MyISAM DEFAULT CHARSET=latin1; */

        include './Pagination.php';
        $Pagination = new Pagination();

        //CONNECT
        $link = mysqli_connect($config['host'], $config['user'], $config['pass'], $config['database']);

        /* check connection */
        if (mysqli_connect_errno()) {
            printf("Connection failed: %s\n", mysqli_connect_error());
            exit();
        }

        //get total rows
        $totalrows = mysqli_fetch_array(mysqli_query($link, "SELECT COUNT(*) AS total FROM " . $config['table']));

        //IF LOG TABLE IS EMPTY DISPLAY ONLY THE MENU
        if ($totalrows['total'] == 0) {
            echo "Log List is empty.<br>";
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
            echo "<a class=\"pointer button\" title=\"Back\" href=\"#\" onclick=\"if(document.referrer) {window.open(document.referrer,'_self');} else {history.go(-1);}return false;\">Back</a>&emsp;\n";
            echo "<a class=\"pointer button\" title=\"Home\" href=\"index.php\">Home</a>&emsp;\n";
            echo "<a class=\"pointer button\" title=\"Clears (deletes) all scheduling data - all Jobs, Triggers, Calendars\" href=\"#\" onclick=\"clearScheduler();return false;\">Clear Scheduler</a>&emsp;";
            echo "<br><br><a class=\"pointer\" title=\"Push Mode\" href=\"reload-log.php\"><img id='icon' src='images/push.jpg' alt='edit' height='28' width='28'/></a>";
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
            $sql = "SELECT ID FROM " . $config['table'] . " LIMIT 1";
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
            $sort = "DESC";
        } else {

            //$orderby = mysqli_real_escape_string($_GET['orderby']);
            $orderby = $_GET['orderby'];
        }

        //IF SORT NOT SET OR VALID, SET DEFAULT
        if (!isset($_GET['sort']) || ($_GET['sort'] != "ASC" AND $_GET['sort'] != "DESC")) {
            //default sort
            $sort = "DESC";
        } else {
            //$sort = mysqli_real_escape_string($_GET['sort']);
            $sort = $_GET['sort'];
        }

        //GET DATA
        $sql = "SELECT ID, DATE, FIRE_INSTANCE_ID, JOB_NAME, JOB_GROUP, JOB_DATA, TRIGGER_NAME, TRIGGER_GROUP, STATUS, PROGRESS, PREV_FIRE_TIME, NEXT_FIRE_TIME, REFIRE_COUNT, RESULT, SCHEDULER_INSTANCE_ID, SCHEDULER_NAME, IP_ADDRESS, LOGGER, LEVEL, MESSAGE FROM " . $config['table'] . " ORDER BY $orderby $sort LIMIT $startrow,$limit";
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
                //format the time fields (NEXT_FIRE_TIME, PREV_FIRE_TIME, END_TIME), if timestamp=0 then print "never"
                //if (strpos($field, '_TIME') !== false)
                //echo "<td>" . ($value != 0 ? date('Y-m-d H:i:s', $value / 1000) : "never") . "</td>\n";
                //else
                if (strpos($field, 'JOB_NAME') !== false) {
                    echo "<td><a title=\"Edit Job\" href=\"newJob.php?jobName=" . $row['JOB_NAME'] . "&jobGroup=" . $row['JOB_GROUP'] . "\">" . $value . "</a></td>\n";
                } else if (strpos($field, 'JOB_GROUP') !== false) {
                    echo "<td><a title=\"Edit Job\" href=\"newJob.php?jobName=" . $row['JOB_NAME'] . "&jobGroup=" . $row['JOB_GROUP'] . "\">" . $value . "</a></td>\n";
                } else if (strpos($field, 'JOB_DATA') !== false) {
                    $value = stripcslashes($value);
                    //if job data field is too big, then use a resizable text area
                    if (strlen($value) > 80) {
                        echo "<td><textarea class=\"result\">" . $value . "</textarea></td>\n";
                    } else {
                        echo "<td>" . $value . "</td>\n";
                    }
                } else if (strpos($field, 'TRIGGER_NAME') !== false) {
                    echo "<td><a title=\"Edit Trigger\" href=\"newTrigger.php?triggerName=" . $row['TRIGGER_NAME'] . "&triggerGroup=" . $row['TRIGGER_GROUP'] . "\">" . $value . "</a></td>\n";
                } else if (strpos($field, 'TRIGGER_GROUP') !== false) {
                    echo "<td><a title=\"Edit Trigger\" href=\"newTrigger.php?triggerName=" . $row['TRIGGER_NAME'] . "&triggerGroup=" . $row['TRIGGER_GROUP'] . "\">" . $value . "</a></td>\n";
                } else if (strpos($field, 'IP_ADDRESS') !== false) {
                    $ipArray = explode(";", $row['IP_ADDRESS']);
                    echo "<td>";
                    foreach ($ipArray as $ip) {
                        echo "<a target=\"_blank\" href=\"http://" . $ip . "\">" . $ip . "</a>\n";
                    }
                    echo "</td>\n";
                }
                //if result or message field is too big, then use a resizable text area
                else if ((strpos($field, 'RESULT') !== false || strpos($field, 'MESSAGE') !== false) && strlen($value) > 80) {
                    echo "<td><textarea class=\"result\">" . $value . "</textarea></td>\n";
                } else {
                    echo "<td>" . $value . "</td>\n";
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
            echo "<br><br><a class=\"pointer\" title=\"Push Mode\" href=\"reload-log.php\"><img id='icon' src='images/push.jpg' alt='edit' height='28' width='28'/></a>";
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