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
        <title>Jobs</title> 
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
        //DATABASE SETTINGS
        //$config['host'] = "localhost";
        //$config['user'] = "root";
        //$config['pass'] = "centos";
        //$config['database'] = "quartz";
        //$config['nicefields'] = true; //true or false | "Field Name" or "field_name"
        //$config['perpage'] = 10;
        //$config['showpagenumbers'] = true; //true or false
        //$config['showprevnext'] = true; //true or false
        $config['table'] = /* isset(filter_input(INPUT_GET, 'table')) ? filter_input(INPUT_GET, 'table') : */"QRTZ_JOB_DETAILS";

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

        //IF JOBS TABLE IS EMPTY DISPLAY ONLY THE MENU
        if ($totalrows['total'] == 0) {
            echo "<div id='resultsTable'>";
            echo "Jobs List is empty.<br>";
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

        //GET DATA
        //$sql = "SELECT * FROM " . $config['table'] . " ORDER BY $orderby $sort LIMIT $startrow,$limit";
        //$sql = "SELECT SCHED_NAME, (SELECT COUNT(*) FROM quartz.QRTZ_FIRED_TRIGGERS T WHERE Q.JOB_NAME=T.JOB_NAME AND Q.JOB_GROUP=T.JOB_GROUP AND Q.SCHED_NAME=T.SCHED_NAME) AS RUNNING, (SELECT MIN(NEXT_FIRE_TIME) FROM quartz.QRTZ_TRIGGERS T WHERE Q.JOB_NAME=T.JOB_NAME AND Q.JOB_GROUP=T.JOB_GROUP AND Q.SCHED_NAME=T.SCHED_NAME) AS NEXT_FIRE_TIME, JOB_NAME, JOB_GROUP, DESCRIPTION, JOB_CLASS_NAME, (SELECT CASE MAX(CASE TRIGGER_STATE WHEN 'ERROR' THEN 6 WHEN 'BLOCKED' THEN 5 WHEN 'PAUSED' THEN 4 WHEN 'NORMAL' THEN 3 WHEN 'WAITING' THEN 2 WHEN 'NONE' THEN 1 ELSE 0 END) WHEN 6 THEN 'ERROR' WHEN 5 THEN 'BLOCKED' WHEN 4 THEN 'PAUSED' WHEN 3 THEN 'NORMAL' WHEN 2 THEN 'WAITING' WHEN 1 THEN 'NONE' ELSE '???' END FROM quartz.QRTZ_TRIGGERS T WHERE Q.JOB_NAME=T.JOB_NAME AND Q.JOB_GROUP=T.JOB_GROUP AND Q.SCHED_NAME=T.SCHED_NAME) AS STATE, (SELECT MAX(PREV_FIRE_TIME) FROM quartz.QRTZ_TRIGGERS T WHERE Q.JOB_NAME=T.JOB_NAME AND Q.JOB_GROUP=T.JOB_GROUP AND Q.SCHED_NAME=T.SCHED_NAME) AS PREV_FIRE_TIME, (SELECT MIN(START_TIME) FROM quartz.QRTZ_TRIGGERS T WHERE Q.JOB_NAME=T.JOB_NAME AND Q.JOB_GROUP=T.JOB_GROUP AND Q.SCHED_NAME=T.SCHED_NAME) AS START_TIME, (SELECT MIN(END_TIME) FROM quartz.QRTZ_TRIGGERS T WHERE Q.JOB_NAME=T.JOB_NAME AND Q.JOB_GROUP=T.JOB_GROUP AND Q.SCHED_NAME=T.SCHED_NAME) AS END_TIME, IS_DURABLE, IS_NONCONCURRENT, IS_UPDATE_DATA, REQUESTS_RECOVERY FROM " . $config['table'] . " Q ORDER BY $orderby $sort LIMIT $startrow,$limit";
        //$sql = "SELECT a.SCHED_NAME, a.JOB_NAME, a.JOB_GROUP, a.DESCRIPTION, a.JOB_CLASS_NAME, a.IS_DURABLE, a.IS_NONCONCURRENT, a.IS_UPDATE_DATA, a.REQUESTS_RECOVERY, a.JOB_DATA, (SELECT COUNT(*) FROM quartz.QRTZ_LOGS c WHERE DATE >= NOW() - INTERVAL 1 DAY AND a.JOB_NAME=c.JOB_NAME AND a.JOB_GROUP=c.JOB_GROUP AND status='FAILED') AS 'FAILED_1D', (SELECT COUNT(*) FROM quartz.QRTZ_LOGS c WHERE DATE >= NOW() - INTERVAL 1 DAY AND a.JOB_NAME=c.JOB_NAME AND a.JOB_GROUP=c.JOB_GROUP AND status='SUCCESS') AS 'SUCCESS_1D', 1 AS 'FAILED_1D_PERC', 1 AS 'SUCCESS_1D_PERC' FROM " . $config['table'] . " a ORDER BY $orderby $sort LIMIT $startrow,$limit";
        /* $sql = "SELECT a.SCHED_NAME, a.JOB_NAME, a.JOB_GROUP, a.DESCRIPTION, a.JOB_CLASS_NAME, a.IS_DURABLE, a.IS_NONCONCURRENT, a.IS_UPDATE_DATA, a.REQUESTS_RECOVERY, a.JOB_DATA,"
          . " (SELECT COUNT(*) FROM quartz.QRTZ_LOGS c WHERE DATE >= NOW() - INTERVAL 1 DAY AND a.JOB_NAME=c.JOB_NAME AND a.JOB_GROUP=c.JOB_GROUP AND status='FAILED') AS 'FAILED_1D',"
          . " (SELECT COUNT(*) FROM quartz.QRTZ_LOGS c WHERE DATE >= NOW() - INTERVAL 1 DAY AND a.JOB_NAME=c.JOB_NAME AND a.JOB_GROUP=c.JOB_GROUP AND status='SUCCESS') AS 'SUCCESS_1D',"
          . "  100 * (SELECT COUNT(*) FROM quartz.QRTZ_LOGS c WHERE DATE >= NOW() - INTERVAL 1 DAY AND a.JOB_NAME=c.JOB_NAME AND a.JOB_GROUP=c.JOB_GROUP AND status='FAILED')/"
          . "((SELECT COUNT(*) FROM quartz.QRTZ_LOGS c WHERE DATE >= NOW() - INTERVAL 1 DAY AND a.JOB_NAME=c.JOB_NAME AND a.JOB_GROUP=c.JOB_GROUP AND status='FAILED')+"
          . "(SELECT COUNT(*) FROM quartz.QRTZ_LOGS c WHERE DATE >= NOW() - INTERVAL 1 DAY AND a.JOB_NAME=c.JOB_NAME AND a.JOB_GROUP=c.JOB_GROUP AND status='SUCCESS')) AS FAILED_1D_PERC,"
          . " 100 * (SELECT COUNT(*) FROM quartz.QRTZ_LOGS c WHERE DATE >= NOW() - INTERVAL 1 DAY AND a.JOB_NAME=c.JOB_NAME AND a.JOB_GROUP=c.JOB_GROUP AND status='SUCCESS')/"
          . "((SELECT COUNT(*) FROM quartz.QRTZ_LOGS c WHERE DATE >= NOW() - INTERVAL 1 DAY AND a.JOB_NAME=c.JOB_NAME AND a.JOB_GROUP=c.JOB_GROUP AND status='FAILED')+"
          . "(SELECT COUNT(*) FROM quartz.QRTZ_LOGS c WHERE DATE >= NOW() - INTERVAL 1 DAY AND a.JOB_NAME=c.JOB_NAME AND a.JOB_GROUP=c.JOB_GROUP AND status='SUCCESS')) AS SUCCESS_1D_PERC FROM "
          . $config['table'] . " a ORDER BY $orderby $sort LIMIT $startrow,$limit"; */
        $sql = $sql = "SELECT a.SCHED_NAME, a.JOB_NAME, a.JOB_GROUP, a.DESCRIPTION,"
                . " (SELECT COUNT(*) FROM quartz.QRTZ_LOGS c WHERE DATE >= NOW() - INTERVAL 1 DAY AND a.JOB_NAME=c.JOB_NAME AND a.JOB_GROUP=c.JOB_GROUP AND status='FAILED') AS 'FAILED_1D',"
                . " (SELECT COUNT(*) FROM quartz.QRTZ_LOGS c WHERE DATE >= NOW() - INTERVAL 1 DAY AND a.JOB_NAME=c.JOB_NAME AND a.JOB_GROUP=c.JOB_GROUP AND status='SUCCESS') AS 'SUCCESS_1D',"
                . " 100*(SELECT COUNT(*) FROM quartz.QRTZ_LOGS c WHERE DATE >= NOW() - INTERVAL 1 DAY AND a.JOB_NAME=c.JOB_NAME AND a.JOB_GROUP=c.JOB_GROUP AND status='FAILED')/"
                . " ((SELECT COUNT(*) FROM quartz.QRTZ_LOGS c WHERE DATE >= NOW() - INTERVAL 1 DAY AND a.JOB_NAME=c.JOB_NAME AND a.JOB_GROUP=c.JOB_GROUP AND status='FAILED')+"
                . " (SELECT COUNT(*) FROM quartz.QRTZ_LOGS c WHERE DATE >= NOW() - INTERVAL 1 DAY AND a.JOB_NAME=c.JOB_NAME AND a.JOB_GROUP=c.JOB_GROUP AND status='SUCCESS')) AS FAILED_1D_PERC,"
                . " 100*(SELECT COUNT(*) FROM quartz.QRTZ_LOGS c WHERE DATE >= NOW() - INTERVAL 1 DAY AND a.JOB_NAME=c.JOB_NAME AND a.JOB_GROUP=c.JOB_GROUP AND status='SUCCESS')/"
                . " ((SELECT COUNT(*) FROM quartz.QRTZ_LOGS c WHERE DATE >= NOW() - INTERVAL 1 DAY AND a.JOB_NAME=c.JOB_NAME AND a.JOB_GROUP=c.JOB_GROUP AND status='FAILED')+"
                . " (SELECT COUNT(*) FROM quartz.QRTZ_LOGS c WHERE DATE >= NOW() - INTERVAL 1 DAY AND a.JOB_NAME=c.JOB_NAME AND a.JOB_GROUP=c.JOB_GROUP AND status='SUCCESS')) AS SUCCESS_1D_PERC,"
                . " (SELECT COUNT(*) FROM quartz.QRTZ_LOGS c WHERE DATE >= NOW() - INTERVAL 7 DAY AND a.JOB_NAME=c.JOB_NAME AND a.JOB_GROUP=c.JOB_GROUP AND status='FAILED') AS 'FAILED_7D',"
                . " (SELECT COUNT(*) FROM quartz.QRTZ_LOGS c WHERE DATE >= NOW() - INTERVAL 7 DAY AND a.JOB_NAME=c.JOB_NAME AND a.JOB_GROUP=c.JOB_GROUP AND status='SUCCESS') AS 'SUCCESS_7D',"
                . " 100*(SELECT COUNT(*) FROM quartz.QRTZ_LOGS c WHERE DATE >= NOW() - INTERVAL 7 DAY AND a.JOB_NAME=c.JOB_NAME AND a.JOB_GROUP=c.JOB_GROUP AND status='FAILED')/"
                . " ((SELECT COUNT(*) FROM quartz.QRTZ_LOGS c WHERE DATE >= NOW() - INTERVAL 7 DAY AND a.JOB_NAME=c.JOB_NAME AND a.JOB_GROUP=c.JOB_GROUP AND status='FAILED')+"
                . " (SELECT COUNT(*) FROM quartz.QRTZ_LOGS c WHERE DATE >= NOW() - INTERVAL 7 DAY AND a.JOB_NAME=c.JOB_NAME AND a.JOB_GROUP=c.JOB_GROUP AND status='SUCCESS')) AS FAILED_7D_PERC,"
                . " 100*(SELECT COUNT(*) FROM quartz.QRTZ_LOGS c WHERE DATE >= NOW() - INTERVAL 7 DAY AND a.JOB_NAME=c.JOB_NAME AND a.JOB_GROUP=c.JOB_GROUP AND status='SUCCESS')/"
                . " ((SELECT COUNT(*) FROM quartz.QRTZ_LOGS c WHERE DATE >= NOW() - INTERVAL 7 DAY AND a.JOB_NAME=c.JOB_NAME AND a.JOB_GROUP=c.JOB_GROUP AND status='FAILED')+"
                . " (SELECT COUNT(*) FROM quartz.QRTZ_LOGS c WHERE DATE >= NOW() - INTERVAL 7 DAY AND a.JOB_NAME=c.JOB_NAME AND a.JOB_GROUP=c.JOB_GROUP AND status='SUCCESS')) AS SUCCESS_7D_PERC,"
                . "a.JOB_CLASS_NAME, a.IS_DURABLE, a.IS_NONCONCURRENT, a.IS_UPDATE_DATA, a.REQUESTS_RECOVERY, a.JOB_DATA"
                . "  FROM " . $config['table'] . " a ORDER BY $orderby $sort LIMIT $startrow,$limit";

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

        //counter
        $i = 0;

        //LOOP TABLE ROWS
        while ($row = mysqli_fetch_assoc($result)) {

            echo "<tr " . $tr_class . " >\n";

            foreach ($row as $field => $value) {
                if ($field == 'SCHED_NAME') {
                    echo "<td><a class=\"pointer\" title=\"Edit Job\" href=\"newJob.php?jobName=" . $row['JOB_NAME'] . "&jobGroup=" . $row['JOB_GROUP'] . "\"><img id='icon' src='images/edit.gif' alt='edit' height='14' width='14'/></a>"
                    . "<a class=\"pointer\" title=\"Delete Job\" onClick=\"deleteJob('" . $row['JOB_NAME'] . "', '" . $row['JOB_GROUP'] . "')\"><img id='icon' src='images/delete.gif' alt='Delete Job' height='14' width='14'/></a>"
                    . "<a class=\"pointer\" title=\"Resume Job\" onClick=\"resumeJob('" . $row['JOB_NAME'] . "', '" . $row['JOB_GROUP'] . "')\"><img id='icon' src='images/play.png' alt='Resume Job' height='14' width='14'/></a>"
                    . "<a class=\"pointer\" title=\"Pause Job\" onClick=\"pauseJob('" . $row['JOB_NAME'] . "', '" . $row['JOB_GROUP'] . "')\"><img id='icon' src='images/pause.png' alt='Pause Job' height='14' width='14'/></a>"
                    . "<a class=\"pointer\" title=\"Interrupt Job\" onClick=\"interruptJob('" . $row['JOB_NAME'] . "', '" . $row['JOB_GROUP'] . "')\"><img id='icon' src='images/stop.png' alt='Stop Job' height='14' width='14'/></a>"
                    . "<a class=\"pointer\" title=\"Interrupt Job Instance\" onClick=\"interruptJobInstance('" . $row['FIRE_INSTANCE_ID'] . "')\"><img id='icon' src='images/stop-instance.png' alt='Stop Job Instance' height='14' width='14'/></a>"
                    . "<a class=\"pointer\" title=\"View Triggers\" href=\"triggers.php?jobName=" . $row['JOB_NAME'] . "&jobGroup=" . $row['JOB_GROUP'] . "\"><img id='icon' src='images/triggerlist.jpg' alt='edit' height='14' width='14'/></a>"
                    . "<a class=\"pointer\" title=\"Trigger Job\" onClick=\"triggerJob('" . $row['JOB_NAME'] . "', '" . $row['JOB_GROUP'] . "')\"><img id='icon' src='images/trigger.png' alt='Trigger Job' height='14' width='14'/></a>" . $value . "</td>\n";
                } else if (strpos($field, '_TIME') !== false) {
                    echo "<td>" . ($value != 0 ? date('Y-m-d H:i:s', $value / 1000) : "never") . "</td>\n";
                } else if (strpos($field, 'JOB_NAME') !== false) {
                    echo "<td><a title=\"Edit Job\" href=\"newJob.php?jobName=" . $row['JOB_NAME'] . "&jobGroup=" . $row['JOB_GROUP'] . "\">" . $value . "</a></td>\n";
                } else if (strpos($field, 'JOB_GROUP') !== false) {
                    echo "<td><a title=\"Edit Job\" href=\"newJob.php?jobName=" . $row['JOB_NAME'] . "&jobGroup=" . $row['JOB_GROUP'] . "\">" . $value . "</a></td>\n";
                } else if (strpos($field, 'JOB_DATA') !== false) {
                    $jobDataMap = parse_properties($value);
                    $value = stripcslashes(urldecode(http_build_query($jobDataMap, '', ';')));
                    //if job data field is too big, then use a resizable text area
                    if (strlen($value) > 80) {
                        echo "<td><textarea class=\"result\">" . $value/* urldecode(http_build_query($jobDataMap, '', ';')) */ . "</textarea></td>\n";
                    } else {
                        echo "<td>" . $value/* urldecode(http_build_query($jobDataMap, '', ';')) */ . "</td>\n";
                    }
                } /* else if (strpos($field, 'FAILED_1D_PERC') !== false) {
                  echo "<td>" . min_precision(($row['FAILED_1D'] * 100) / ($row['FAILED_1D'] + $row['SUCCESS_1D'])) . "</td>\n";
                  } else if (strpos($field, 'SUCCESS_1D_PERC') !== false) {
                  echo "<td>" . min_precision(($row['SUCCESS_1D'] * 100) / ($row['FAILED_1D'] + $row['SUCCESS_1D'])) . "</td>\n";
                  } */ else {
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

        function isSchedulerStarted() {
            global $config;
            $postData["id"] = "isStarted";
            $jsonData["json"] = json_encode($postData);
            $result = json_decode(postData($jsonData, "http://" . $config["tomcat"] . ":8080/SmartCloudEngine/index.jsp"), true);
            if ($result[1] == 'true') {
                return 'running';
            } else {
                return 'stopped';
            }
        }

        function isSchedulerStandby() {
            global $config;
            $postData["id"] = "isInStandbyMode";
            $jsonData["json"] = json_encode($postData);
            $result = json_decode(postData($jsonData, "http://" . $config["tomcat"] . ":8080/SmartCloudEngine/index.jsp"), true);
            if ($result[1] == 'true') {
                return 'yes';
            } else {
                return 'no';
            }
        }

        function isSchedulerShutdown() {
            global $config;
            $postData["id"] = "isShutdown";
            $jsonData["json"] = json_encode($postData);
            $result = json_decode(postData($jsonData, "http://" . $config["tomcat"] . ":8080/SmartCloudEngine/index.jsp"), true);
            if ($result[1] == 'true') {
                return 'yes';
            } else {
                return 'no';
            }
        }

        // get scheduler metadata
        function getSchedulerMetadata() {
            global $config;
            $postData["id"] = "getSchedulerMetadata";
            $jsonData["json"] = json_encode($postData);
            $arr = json_decode(postData($jsonData, "http://" . $config["tomcat"] . ":8080/SmartCloudEngine/index.jsp"), true);
            ksort($arr); //sort alphabetically the array
            return $arr;
        }

        // get system status
        function getSystemStatus() {
            global $config;
            $postData["id"] = "getSystemStatus";
            $jsonData["json"] = json_encode($postData);
            $arr = json_decode(postData($jsonData, "http://" . $config["tomcat"] . ":8080/SmartCloudEngine/index.jsp"), true);
            ksort($arr); //sort alphabetically the array
            return $arr;
        }

        //send data in POST to url
        function postData($data, $url) {
            //$url = 'URL';
            //$data = array('field1' => 'value', 'field2' => 'value');
            $options = array(
                'http' => array(
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method' => 'POST',
                    'content' => http_build_query($data),
                )
            );
            $context = stream_context_create($options);
            return file_get_contents($url, false, $context);
        }

        //read the JOB_DATA BLOB from the Quartz database as a .properties file
        //this method works if the org.quartz.jobStore.useProperties property in quartz.properties is set to true
        function parse_properties($txtProperties) {
            $result = array();

            $lines = split("\n", $txtProperties);
            $key = "";

            $isWaitingOtherLine = false;
            foreach ($lines as $i => $line) {

                if (empty($line) || (!$isWaitingOtherLine && strpos($line, "#") === 0)) {
                    continue;
                }

                if (!$isWaitingOtherLine) {
                    $key = substr($line, 0, strpos($line, '='));
                    //strip cslashes \\ from keys beginning with \\#, (e.g., reserved jobDataMap parameters: #isNonConcurrent, #url, #notificationEmail, #nextJobs, #processParameters, #jobConstraints)
                    $key = stripcslashes($key);

                    $value = substr($line, strpos($line, '=') + 1, strlen($line));
                    //strip cslashes \\ from keys beginning with \\#
                    $value = stripcslashes($value);
                } else {
                    $value .= $line;
                }

                /* Check if ends with single '\' */
                if (strrpos($value, "\\") === strlen($value) - strlen("\\")) {
                    $value = substr($value, 0, strlen($value) - 1) . "\n";
                    $isWaitingOtherLine = true;
                } else {
                    $isWaitingOtherLine = false;
                }

                $result[$key] = $value;
                unset($lines[$i]);
            }

            return $result;
        }

        // use this instead of php round function, find the minimum precision of a number (e.g. precision 2, 1 -> 1.00, 0.1 -> 0.10, 0.001 -> 0.001)
        function min_precision($val, $min = 2, $max = 4) {
            $result = round($val, $min);
            if ($result == 0 && $min < $max) {
                return min_precision($val, ++$min, $max);
            } else {
                return $result;
            }
        }
        ?>

    </body>
</html>
