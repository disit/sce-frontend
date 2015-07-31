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

function mdate($format, $microtime = null) {
    $microtime = explode(' ', ($microtime ? $microtime : microtime()));
    if (count($microtime) != 2) {
        return false;
    }
    $microtime[0] = $microtime[0] * 1000000;
    $format = str_replace('u', $microtime[0], $format);
    return date($format, $microtime[1]);
}

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
$config['table'] = "QRTZ_STATUS";

//QUERY FILTERS (sent by reload.php)
$query_filters = '';
$query_filters .= filter_input(INPUT_GET, 'FILTER_SCHED_NAME') != '' ? ($query_filters != "" ? " AND SCHED_NAME LIKE '%" . filter_input(INPUT_GET, 'FILTER_SCHED_NAME') . "%'" : " WHERE SCHED_NAME LIKE '%" . filter_input(INPUT_GET, 'FILTER_SCHED_NAME') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_ID') != '' ? ($query_filters != "" ? " AND ID LIKE '%" . filter_input(INPUT_GET, 'FILTER_ID') . "%'" : " WHERE ID LIKE '%" . filter_input(INPUT_GET, 'FILTER_ID') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_FIRE_INSTANCE_ID') != '' ? ($query_filters != "" ? " AND FIRE_INSTANCE_ID LIKE '%" . filter_input(INPUT_GET, 'FILTER_FIRE_INSTANCE_ID') . "%'" : " WHERE FIRE_INSTANCE_ID LIKE '%" . filter_input(INPUT_GET, 'FILTER_FIRE_INSTANCE_ID') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_DATE') != '' ? ($query_filters != "" ? " AND DATE LIKE '%" . filter_input(INPUT_GET, 'FILTER_DATE') . "%'" : " WHERE DATE LIKE '%" . filter_input(INPUT_GET, 'FILTER_DATE') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_JOB_NAME') != '' ? ($query_filters != "" ? " AND JOB_NAME LIKE '%" . filter_input(INPUT_GET, 'FILTER_JOB_NAME') . "%'" : " WHERE JOB_NAME LIKE '%" . filter_input(INPUT_GET, 'FILTER_JOB_NAME') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_JOB_GROUP') != '' ? ($query_filters != "" ? " AND JOB_GROUP LIKE '%" . filter_input(INPUT_GET, 'FILTER_JOB_GROUP') . "%'" : " WHERE JOB_GROUP LIKE '%" . filter_input(INPUT_GET, 'FILTER_JOB_GROUP') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_JOB_DATA') != '' ? ($query_filters != "" ? " AND CONVERT(JOB_DATA using latin1) LIKE '%" . filter_input(INPUT_GET, 'FILTER_JOB_DATA') . "%'" : " WHERE CONVERT(JOB_DATA using latin1) LIKE '%" . filter_input(INPUT_GET, 'FILTER_JOB_DATA') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_STATUS') != '' ? ($query_filters != "" ? " AND STATUS LIKE '%" . filter_input(INPUT_GET, 'FILTER_STATUS') . "%'" : " WHERE STATUS LIKE '%" . filter_input(INPUT_GET, 'FILTER_STATUS') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_PROGRESS') != '' ? ($query_filters != "" ? " AND PROGRESS LIKE '%" . filter_input(INPUT_GET, 'FILTER_PROGRESS') . "%'" : " WHERE PROGRESS LIKE '%" . filter_input(INPUT_GET, 'FILTER_PROGRESS') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_TRIGGER_NAME') != '' ? ($query_filters != "" ? " AND TRIGGER_NAME LIKE '%" . filter_input(INPUT_GET, 'FILTER_TRIGGER_NAME') . "%'" : " WHERE TRIGGER_NAME LIKE '%" . filter_input(INPUT_GET, 'FILTER_TRIGGER_NAME') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_TRIGGER_GROUP') != '' ? ($query_filters != "" ? " AND TRIGGER_GROUP LIKE '%" . filter_input(INPUT_GET, 'FILTER_TRIGGER_GROUP') . "%'" : " WHERE TRIGGER_GROUP LIKE '%" . filter_input(INPUT_GET, 'FILTER_TRIGGER_GROUP') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_PREV_FIRE_TIME') != '' ? ($query_filters != "" ? " AND PREV_FIRE_TIME LIKE '%" . filter_input(INPUT_GET, 'FILTER_PREV_FIRE_TIME') . "%'" : " WHERE PREV_FIRE_TIME LIKE '%" . filter_input(INPUT_GET, 'FILTER_PREV_FIRE_TIME') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_NEXT_FIRE_TIME') != '' ? ($query_filters != "" ? " AND NEXT_FIRE_TIME LIKE '%" . filter_input(INPUT_GET, 'FILTER_NEXT_FIRE_TIME') . "%'" : " WHERE NEXT_FIRE_TIME LIKE '%" . filter_input(INPUT_GET, 'FILTER_NEXT_FIRE_TIME') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_REFIRE_COUNT') != '' ? ($query_filters != "" ? " AND REFIRE_COUNT LIKE '%" . filter_input(INPUT_GET, 'FILTER_REFIRE_COUNT') . "%'" : " WHERE REFIRE_COUNT LIKE '%" . filter_input(INPUT_GET, 'FILTER_REFIRE_COUNT') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_RESULT') != '' ? ($query_filters != "" ? " AND RESULT LIKE '%" . filter_input(INPUT_GET, 'FILTER_RESULT') . "%'" : " WHERE RESULT LIKE '%" . filter_input(INPUT_GET, 'FILTER_RESULT') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_SCHEDULER_INSTANCE_ID') != '' ? ($query_filters != "" ? " AND SCHEDULER_INSTANCE_ID LIKE '%" . filter_input(INPUT_GET, 'FILTER_SCHEDULER_INSTANCE_ID') . "%'" : " WHERE SCHEDULER_INSTANCE_ID LIKE '%" . filter_input(INPUT_GET, 'FILTER_SCHEDULER_INSTANCE_ID') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_IP_ADDRESS') != '' ? ($query_filters != "" ? " AND IP_ADDRESS LIKE '%" . filter_input(INPUT_GET, 'FILTER_IP_ADDRESS') . "%'" : " WHERE IP_ADDRESS LIKE '%" . filter_input(INPUT_GET, 'FILTER_IP_ADDRESS') . "%'") : "";

include_once './Pagination-reload.php';
$Pagination = new Pagination();

//CONNECT
$link = mysqli_connect($config['host'], $config['user'], $config['pass'], $config['database']);

/* check connection */
if (mysqli_connect_errno()) {
    printf("Connection failed: %s\n", mysqli_connect_error());
    exit();
}

//get total rows
//$totalrows = mysqli_fetch_array(mysqli_query($link, "SELECT COUNT(*) AS total FROM " . $config['table'] . " Q LEFT OUTER JOIN " . $config['table'] . " Q2 ON (Q.job_name = Q2.job_name AND Q.job_group = Q2.job_group and Q.ID < Q2.ID) WHERE Q2.job_name IS NULL"));
$totalrows = mysqli_fetch_array(mysqli_query($link, "SELECT COUNT(*) AS total FROM " . $config['table'] . $query_filters));

//IF JOB TABLE IS EMPTY DISPLAY ONLY THE MENU
if ($totalrows['total'] == 0) {
    echo "Status List is empty.<br>";
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
    $sql = "SELECT ID FROM " . $config['table'] . " LIMIT 1"; //USE ID AS THE DEFAULT SORT FIELD
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
//$sql = "SELECT * FROM " . $config['table'] . " ORDER BY $orderby $sort LIMIT $startrow,$limit";
//$sql = "SELECT Q.ID, Q.JOB_NAME, Q.JOB_GROUP, Q.DATE, Q.TRIGGER_NAME, Q.TRIGGER_GROUP, Q.PREV_FIRE_TIME, Q.NEXT_FIRE_TIME, Q.REFIRE_COUNT, Q.RESULT, Q.SCHEDULER_INSTANCE_ID, Q.SCHEDULER_NAME, Q.IP_ADDRESS, Q.STATUS FROM " . $config['table'] . " Q LEFT OUTER JOIN " . $config['table'] . " Q2 ON (Q.job_name = Q2.job_name AND Q.job_group = Q2.job_group and Q.ID < Q2.ID) WHERE Q2.job_name IS NULL ORDER BY $orderby $sort LIMIT $startrow,$limit";
$sql = "SELECT SCHEDULER_NAME, ID, FIRE_INSTANCE_ID, DATE, JOB_NAME, JOB_GROUP, JOB_DATA, STATUS, PROGRESS, TRIGGER_NAME, TRIGGER_GROUP, PREV_FIRE_TIME, NEXT_FIRE_TIME, REFIRE_COUNT, RESULT, SCHEDULER_INSTANCE_ID, IP_ADDRESS FROM " . $config['table'] . $query_filters . " ORDER BY $orderby $sort LIMIT $startrow,$limit";
$result = mysqli_query($link, $sql) or die(mysqli_error());

//START TABLE AND TABLE HEADER
echo "<div id='resultsTableReload'><table>\n<tr>";
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
        if (strpos($field, 'SCHEDULER_NAME') !== false) {
            echo "<td><a class=\"pointer\" title=\"Edit Job\" href=\"newJob.php?jobName=" . $row['JOB_NAME'] . "&jobGroup=" . $row['JOB_GROUP'] . "\"><img id='icon' src='images/edit.gif' alt='edit' height='14' width='14'/></a>"
            . "<a class=\"pointer\" title=\"Delete Job\" onClick=\"deleteJob('" . $row['JOB_NAME'] . "', '" . $row['JOB_GROUP'] . "')\"><img id='icon' src='images/delete.gif' alt='Delete Job' height='14' width='14'/></a>"
            . "<a class=\"pointer\" title=\"Resume Job\" onClick=\"resumeJob('" . $row['JOB_NAME'] . "', '" . $row['JOB_GROUP'] . "')\"><img id='icon' src='images/play.png' alt='Resume Job' height='14' width='14'/></a>"
            . "<a class=\"pointer\" title=\"Pause Job\" onClick=\"pauseJob('" . $row['JOB_NAME'] . "', '" . $row['JOB_GROUP'] . "')\"><img id='icon' src='images/pause.png' alt='Pause Job' height='14' width='14'/></a>"
            . "<a class=\"pointer\" title=\"Interrupt Job\" onClick=\"interruptJob('" . $row['JOB_NAME'] . "', '" . $row['JOB_GROUP'] . "')\"><img id='icon' src='images/stop.png' alt='Stop Job' height='14' width='14'/></a>"
            . "<a class=\"pointer\" title=\"Interrupt Job Instance\" onClick=\"interruptJobInstance('" . $row['FIRE_INSTANCE_ID'] . "')\"><img id='icon' src='images/stop-instance.png' alt='Stop Job Instance' height='14' width='14'/></a>"
            . "<a class=\"pointer\" title=\"View Triggers\" href=\"triggers.php?jobName=" . $row['JOB_NAME'] . "&jobGroup=" . $row['JOB_GROUP'] . "\"><img id='icon' src='images/triggerlist.jpg' alt='edit' height='14' width='14'/></a>"
            . "<a class=\"pointer\" title=\"Trigger Job\" onClick=\"triggerJob('" . $row['JOB_NAME'] . "', '" . $row['JOB_GROUP'] . "')\"><img id='icon' src='images/trigger.png' alt='Trigger Job' height='14' width='14'/></a>" . $value . "</td>\n";
        }
        //else if (strpos($field, '_TIME') !== false)
        //echo "<td>" . ($value != 0 ? date('Y-m-d H:i:s', $value / 1000) : "never") . "</td>\n";
        else if (strpos($field, 'JOB_NAME') !== false) {
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
        //if result field is too big, then use a resizable text area
        else if (strpos($field, 'RESULT') !== false && strlen($value) > 80) {
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
    echo "<br>";
    echo 'Last updated on: ' . mdate('D d-m-Y H:i:s.u') . ' (refresh time ' . $config['refreshTime'] . ' ms)';

    //print scheduler metadata
    /* $schedulerMetadata = getSchedulerMetadata();
      foreach ($schedulerMetadata as $key => $value)
      echo "<br><b title=\"" . $value[1] . "\">" . $key . ": </b>" . $value[0] . "&emsp;";
      echo "<br><b title=\"The number of currently executing jobs\">Currently executing jobs: </b>" . getCurrentlyExecutingJobs() . "&emsp;";
      echo "<br>"; */

    //print system status
    /* $systemStatus = getSystemStatus();
      foreach ($systemStatus as $key => $value)
      echo "<br><b title=\"" . $value[1] . "\">" . $key . ": </b>" . $value[0] . "&emsp;";
      echo "<br>"; */

    /* echo "<br><a class=\"pointer\" title=\"View the job list\" href=\"jobs.php\">Jobs</a>&emsp;";
      echo "<a class=\"pointer\" title=\"View the trigger list\" href=\"triggers.php\">Triggers</a>&emsp;";
      echo "<a class=\"pointer\" title=\"Create a new job\" href=\"newJob.php\">New Job</a>&emsp;";
      echo "<a class=\"pointer\" title=\"Create a new job without trigger\" href=\"newJob.php?dormantJob\">New Job (dormant)</a>&emsp;";
      echo "<a class=\"pointer\" title=\"Create a new trigger\" href=\"newTrigger.php\">New Trigger</a>&emsp;";
      echo "<a title=\"Starts the Scheduler's threads that fire Triggers. When a scheduler is first created it is in 'stand-by' mode, and will not fire triggers. The scheduler can also be put into stand-by mode by clicking 'Standby Scheduler'. The misfire/recovery process will be started, if it is the initial call to this action on this scheduler instance.\" href=\"#\" onclick=\"startScheduler();return false;\">Start Scheduler</a>&emsp;";
      echo "<a title=\"Temporarily halts the Scheduler's firing of Triggers. When 'Start Scheduler' is called (to bring the scheduler out of stand-by mode), trigger misfire instructions will NOT be applied during the start - any misfires will be detected immediately afterward. The scheduler can be re-started at any time\" href=\"#\" onclick=\"standbyScheduler();return false;\">Standby Scheduler</a>&emsp;";
      echo "<a title=\"Halts the Scheduler's firing of Triggers, and cleans up all resources associated with the Scheduler, waiting jobs to complete (the scheduler cannot be re-started and requires Tomcat restart)\" href=\"#\" onclick=\"shutdownScheduler();return false;\">Shutdown Scheduler</a>&emsp;";
      echo "<a title=\"Halts the Scheduler's firing of Triggers, and cleans up all resources associated with the Scheduler (the scheduler cannot be re-started and requires Tomcat restart)\" href=\"#\" onclick=\"forceShutdownScheduler();return false;\">Force Shutdown Scheduler</a>&emsp;";
      echo "<a title=\"Pause all triggers, after using this method 'Resume Triggers' must be called to clear the scheduler's state of 'remembering' that all new triggers will be paused as they are added\" href=\"#\" onclick=\"pauseAll();return false;\">Pause Triggers</a>&emsp;";
      echo "<a title=\"Resume (un-pause) all triggers on every group\" href=\"#\" onclick=\"resumeAll();return false;\">Resume Triggers</a>&emsp;";
      echo "<a class=\"pointer\" title=\"View the nodes status log\" href=\"nodes.php\">Nodes Log</a>&emsp;";
      echo "<a class=\"pointer\" title=\"View the log\" href=\"log.php\">Log</a>&emsp;";
      echo "<br><br><br><a title=\"Clears (deletes) all scheduling data - all Jobs, Triggers, Calendars\" href=\"#\" onclick=\"clearScheduler();return false;\">Clear Scheduler</a>&emsp;";
      echo "<br><br>"; */
    //echo "<div onclick=\"toggleText()\"><div class=\"text\" ><a title=\"Pause All\" href=\"#\" onclick=\"pauseAll();return false;\">Pause All</a></div><div class=\"text\" style=\"display:none\"><a title=\"Resume All\" href=\"#\" onclick=\"resumeAll();return false;\">Resume All</a></div></div>";
}

/* FUNCTIONS */

function columnSortArrows($field, $text, $currentfield = null, $currentsort = null) {
    //defaults all field links to SORT ASC
    //if field link is current ORDERBY then make arrow and opposite current SORT
    global $page;
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

    //return '<a href="?' . $orderquery . '&' . $sortquery . '">' . $text . '</a> ' . $sortarrow;
    //javascript function loadTable is defined in reload-status.php
    return "<a class=\"pointer\" onClick=\"loadTable('?" . $orderquery . "&" . $sortquery . "&page=" . $page . "')\">" . $text . "</a> " . $sortarrow;
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
    return $arr;
}

// get system status
function getSystemStatus() {
    global $config;
    $postData["id"] = "getSystemStatus";
    $jsonData["json"] = json_encode($postData);
    $arr = json_decode(postData($jsonData, "http://" . $config["tomcat"] . ":8080/SmartCloudEngine/index.jsp"), true);
    return $arr;
}

// get the number of running jobs
function getCurrentlyExecutingJobs() {
    global $config;
    $postData["id"] = "getCurrentlyExecutingJobs";
    $jsonData["json"] = json_encode($postData);
    $arr = json_decode(postData($jsonData, "http://" . $config["tomcat"] . ":8080/SmartCloudEngine/index.jsp"), true);
    if (isset($arr)) {
        return count(objectToArray(json_decode($arr[1])));
    } else {
        return null;
    }
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

//convert stdClass Objects to multidimensional array
function objectToArray($d) {
    if (is_object($d)) {
        // Gets the properties of the given object
        // with get_object_vars function
        $d = get_object_vars($d);
    }

    if (is_array($d)) {
        /*
         * Return array converted to object
         * Using __FUNCTION__ (Magic constant)
         * for recursive call
         */
        return array_map(__FUNCTION__, $d);
    } else {
        // Return array
        return $d;
    }
}

?>
