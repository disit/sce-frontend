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
global $config;
//DATABASE SETTINGS
//$config['host'] = "localhost";
//$config['user'] = "root";
//$config['pass'] = "centos";
//$config['database'] = "quartz";
$config['table'] = "QRTZ_NODES";
//$config['nicefields'] = true; //true or false | "Field Name" or "field_name"
//$config['perpage'] = 30; // rows per page
//$config['pagelinks'] = 50; // max number of page links, if not set 50 will be used as a default value when calling $Pagination->showPageNumbers  
//$config['showpagenumbers'] = true; //true or false
//$config['showprevnext'] = true; //true or false
//QUERY FILTERS (sent by reload-nodes.php)
$query_filters = '';
$query_filters .= filter_input(INPUT_GET, 'FILTER_ID') != '' ? ($query_filters != "" ? " AND a1.ID LIKE '%" . filter_input(INPUT_GET, 'FILTER_ID') . "%'" : " WHERE a1.ID LIKE '%" . filter_input(INPUT_GET, 'FILTER_ID') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_DATE') != '' ? ($query_filters != "" ? " AND a1.DATE LIKE '%" . filter_input(INPUT_GET, 'FILTER_DATE') . "%'" : " WHERE a1.DATE LIKE '%" . filter_input(INPUT_GET, 'FILTER_DATE') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_IP_ADDRESS') != '' ? ($query_filters != "" ? " AND a1.IP_ADDRESS LIKE '%" . filter_input(INPUT_GET, 'FILTER_IP_ADDRESS') . "%'" : " WHERE a1.IP_ADDRESS LIKE '%" . filter_input(INPUT_GET, 'FILTER_IP_ADDRESS') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_SCHEDULER_INSTANCE_ID') != '' ? ($query_filters != "" ? " AND a1.SCHEDULER_INSTANCE_ID LIKE '%" . filter_input(INPUT_GET, 'FILTER_SCHEDULER_INSTANCE_ID') . "%'" : " WHERE a1.SCHEDULER_INSTANCE_ID LIKE '%" . filter_input(INPUT_GET, 'FILTER_SCHEDULER_INSTANCE_ID') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_CPU_LOAD') != '' ? ($query_filters != "" ? " AND a1.CPU_LOAD LIKE '%" . filter_input(INPUT_GET, 'FILTER_CPU_LOAD') . "%'" : " WHERE a1.CPU_LOAD LIKE '%" . filter_input(INPUT_GET, 'FILTER_CPU_LOAD') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_FREE_PHYSICAL_MEMORY') != '' ? ($query_filters != "" ? " AND a1.FREE_PHYSICAL_MEMORY LIKE '%" . filter_input(INPUT_GET, 'FILTER_FREE_PHYSICAL_MEMORY') . "%'" : " WHERE a1.FREE_PHYSICAL_MEMORY LIKE '%" . filter_input(INPUT_GET, 'FILTER_FREE_PHYSICAL_MEMORY') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_JOBS_EXECUTED') != '' ? ($query_filters != "" ? " AND a1.JOBS_EXECUTED LIKE '%" . filter_input(INPUT_GET, 'FILTER_JOBS_EXECUTED') . "%'" : " WHERE a1.JOBS_EXECUTED LIKE '%" . filter_input(INPUT_GET, 'FILTER_JOBS_EXECUTED') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_SCHED_NAME') != '' ? ($query_filters != "" ? " AND a1.SCHED_NAME LIKE '%" . filter_input(INPUT_GET, 'FILTER_SCHED_NAME') . "%'" : " WHERE a1.SCHED_NAME LIKE '%" . filter_input(INPUT_GET, 'FILTER_SCHED_NAME') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_RUNNING_SINCE') != '' ? ($query_filters != "" ? " AND a1.RUNNING_SINCE LIKE '%" . filter_input(INPUT_GET, 'FILTER_RUNNING_SINCE') . "%'" : " WHERE a1.RUNNING_SINCE LIKE '%" . filter_input(INPUT_GET, 'FILTER_RUNNING_SINCE') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_CLUSTERED') != '' ? ($query_filters != "" ? " AND a1.CLUSTERED LIKE '%" . filter_input(INPUT_GET, 'FILTER_CLUSTERED') . "%'" : " WHERE a1.CLUSTERED LIKE '%" . filter_input(INPUT_GET, 'FILTER_CLUSTERED') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_PERSISTENCE') != '' ? ($query_filters != "" ? " AND a1.PERSISTENCE LIKE '%" . filter_input(INPUT_GET, 'FILTER_PERSISTENCE') . "%'" : " WHERE a1.PERSISTENCE LIKE '%" . filter_input(INPUT_GET, 'FILTER_PERSISTENCE') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_REMOTE_SCHEDULER') != '' ? ($query_filters != "" ? " AND a1.REMOTE_SCHEDULER LIKE '%" . filter_input(INPUT_GET, 'FILTER_REMOTE_SCHEDULER') . "%'" : " WHERE a1.REMOTE_SCHEDULER LIKE '%" . filter_input(INPUT_GET, 'FILTER_REMOTE_SCHEDULER') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_CURRENTLY_EXECUTING_JOBS') != '' ? ($query_filters != "" ? " AND a1.CURRENTLY_EXECUTING_JOBS LIKE '%" . filter_input(INPUT_GET, 'FILTER_CURRENTLY_EXECUTING_JOBS') . "%'" : " WHERE a1.CURRENTLY_EXECUTING_JOBS LIKE '%" . filter_input(INPUT_GET, 'FILTER_CURRENTLY_EXECUTING_JOBS') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_CPU_LOAD_JVM') != '' ? ($query_filters != "" ? " AND a1.CPU_LOAD_JVM LIKE '%" . filter_input(INPUT_GET, 'FILTER_CPU_LOAD_JVM') . "%'" : " WHERE a1.CPU_LOAD_JVM LIKE '%" . filter_input(INPUT_GET, 'FILTER_CPU_LOAD_JVM') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_SYSTEM_LOAD_AVERAGE') != '' ? ($query_filters != "" ? " AND a1.SYSTEM_LOAD_AVERAGE LIKE '%" . filter_input(INPUT_GET, 'FILTER_SYSTEM_LOAD_AVERAGE') . "%'" : " WHERE a1.SYSTEM_LOAD_AVERAGE LIKE '%" . filter_input(INPUT_GET, 'FILTER_SYSTEM_LOAD_AVERAGE') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_OPERATING_SYSTEM_VERSION') != '' ? ($query_filters != "" ? " AND a1.OPERATING_SYSTEM_VERSION LIKE '%" . filter_input(INPUT_GET, 'FILTER_OPERATING_SYSTEM_VERSION') . "%'" : " WHERE a1.OPERATING_SYSTEM_VERSION LIKE '%" . filter_input(INPUT_GET, 'FILTER_OPERATING_SYSTEM_VERSION') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_COMMITTED_VIRTUAL_MEMORY') != '' ? ($query_filters != "" ? " AND a1.COMMITTED_VIRTUAL_MEMORY LIKE '%" . filter_input(INPUT_GET, 'FILTER_COMMITTED_VIRTUAL_MEMORY') . "%'" : " WHERE a1.COMMITTED_VIRTUAL_MEMORY LIKE '%" . filter_input(INPUT_GET, 'FILTER_COMMITTED_VIRTUAL_MEMORY') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_OPERATING_SYSTEM_NAME') != '' ? ($query_filters != "" ? " AND a1.OPERATING_SYSTEM_NAME LIKE '%" . filter_input(INPUT_GET, 'FILTER_OPERATING_SYSTEM_NAME') . "%'" : " WHERE a1.OPERATING_SYSTEM_NAME LIKE '%" . filter_input(INPUT_GET, 'FILTER_OPERATING_SYSTEM_NAME') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_FREE_SWAP_SPACE') != '' ? ($query_filters != "" ? " AND a1.FREE_SWAP_SPACE LIKE '%" . filter_input(INPUT_GET, 'FILTER_FREE_SWAP_SPACE') . "%'" : " WHERE a1.FREE_SWAP_SPACE LIKE '%" . filter_input(INPUT_GET, 'FILTER_FREE_SWAP_SPACE') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_PROCESS_CPU_TIME') != '' ? ($query_filters != "" ? " AND a1.PROCESS_CPU_TIME LIKE '%" . filter_input(INPUT_GET, 'FILTER_PROCESS_CPU_TIME') . "%'" : " WHERE a1.PROCESS_CPU_TIME LIKE '%" . filter_input(INPUT_GET, 'FILTER_PROCESS_CPU_TIME') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_TOTAL_PHYSICAL_MEMORY') != '' ? ($query_filters != "" ? " AND a1.TOTAL_PHYSICAL_MEMORY LIKE '%" . filter_input(INPUT_GET, 'FILTER_TOTAL_PHYSICAL_MEMORY') . "%'" : " WHERE a1.TOTAL_PHYSICAL_MEMORY LIKE '%" . filter_input(INPUT_GET, 'FILTER_TOTAL_PHYSICAL_MEMORY') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_NUMBER_OF_PROCESSORS') != '' ? ($query_filters != "" ? " AND a1.NUMBER_OF_PROCESSORS LIKE '%" . filter_input(INPUT_GET, 'FILTER_NUMBER_OF_PROCESSORS') . "%'" : " WHERE a1.NUMBER_OF_PROCESSORS LIKE '%" . filter_input(INPUT_GET, 'FILTER_NUMBER_OF_PROCESSORS') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_OPERATING_SYSTEM_ARCHITECTURE') != '' ? ($query_filters != "" ? " AND a1.OPERATING_SYSTEM_ARCHITECTURE LIKE '%" . filter_input(INPUT_GET, 'FILTER_OPERATING_SYSTEM_ARCHITECTURE') . "%'" : " WHERE a1.OPERATING_SYSTEM_ARCHITECTURE LIKE '%" . filter_input(INPUT_GET, 'FILTER_OPERATING_SYSTEM_ARCHITECTURE') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_TOTAL_SWAP_SPACE') != '' ? ($query_filters != "" ? " AND a1.TOTAL_SWAP_SPACE LIKE '%" . filter_input(INPUT_GET, 'FILTER_TOTAL_SWAP_SPACE') . "%'" : " WHERE a1.TOTAL_SWAP_SPACE LIKE '%" . filter_input(INPUT_GET, 'FILTER_TOTAL_SWAP_SPACE') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_IS_SCHEDULER_STANDBY') != '' ? ($query_filters != "" ? " AND a1.IS_SCHEDULER_STANDBY LIKE '%" . filter_input(INPUT_GET, 'FILTER_IS_SCHEDULER_STANDBY') . "%'" : " WHERE a1.IS_SCHEDULER_STANDBY LIKE '%" . filter_input(INPUT_GET, 'FILTER_IS_SCHEDULER_STANDBY') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_IS_SCHEDULER_SHUTDOWN') != '' ? ($query_filters != "" ? " AND a1.IS_SCHEDULER_SHUTDOWN LIKE '%" . filter_input(INPUT_GET, 'FILTER_IS_SCHEDULER_SHUTDOWN') . "%'" : " WHERE a1.IS_SCHEDULER_SHUTDOWN LIKE '%" . filter_input(INPUT_GET, 'FILTER_IS_SCHEDULER_SHUTDOWN') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_IS_SCHEDULER_STARTED') != '' ? ($query_filters != "" ? " AND a1.IS_SCHEDULER_STARTED LIKE '%" . filter_input(INPUT_GET, 'FILTER_IS_SCHEDULER_STARTED') . "%'" : " WHERE a1.IS_SCHEDULER_STARTED LIKE '%" . filter_input(INPUT_GET, 'FILTER_IS_SCHEDULER_STARTED') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_TOTAL_DISK_SPACE') != '' ? ($query_filters != "" ? " AND a1.TOTAL_DISK_SPACE LIKE '%" . filter_input(INPUT_GET, 'FILTER_TOTAL_DISK_SPACE') . "%'" : " WHERE a1.TOTAL_DISK_SPACE LIKE '%" . filter_input(INPUT_GET, 'FILTER_TOTAL_DISK_SPACE') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_UNALLOCATED_DISK_SPACE') != '' ? ($query_filters != "" ? " AND a1.UNALLOCATED_DISK_SPACE LIKE '%" . filter_input(INPUT_GET, 'FILTER_UNALLOCATED_DISK_SPACE') . "%'" : " WHERE a1.UNALLOCATED_DISK_SPACE LIKE '%" . filter_input(INPUT_GET, 'FILTER_UNALLOCATED_DISK_SPACE') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_USABLE_DISK_SPACE') != '' ? ($query_filters != "" ? " AND a1.USABLE_DISK_SPACE LIKE '%" . filter_input(INPUT_GET, 'FILTER_USABLE_DISK_SPACE') . "%'" : " WHERE a1.USABLE_DISK_SPACE LIKE '%" . filter_input(INPUT_GET, 'FILTER_USABLE_DISK_SPACE') . "%'") : "";

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
$totalrows = mysqli_fetch_array(mysqli_query($link, "SELECT COUNT(*) AS total FROM " . $config['table'] . " a1 INNER JOIN (SELECT max(id) maxId, IP_ADDRESS FROM " . $config['table'] . " GROUP BY IP_ADDRESS) a2 ON a1.IP_ADDRESS = a2.IP_ADDRESS AND a1.id = a2.maxId " . $query_filters));

//IF NODES STATUS TABLE IS EMPTY DISPLAY ONLY THE MENU
if ($totalrows['total'] == 0) {
    echo "Nodes Status List is empty.<br>";
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
//$sql = "SELECT a1.* FROM quartz.QRTZ_NODES a1 INNER JOIN (SELECT max(id) maxId, IP_ADDRESS FROM quartz.QRTZ_NODES GROUP BY IP_ADDRESS) a2 ON a1.IP_ADDRESS = a2.IP_ADDRESS AND a1.id = a2.maxId ORDER BY a1.id DESC";
$sql = "SELECT a1.ID, a1.DATE, a1.IP_ADDRESS, a1.SCHEDULER_INSTANCE_ID, a1.CPU_LOAD, a1.FREE_PHYSICAL_MEMORY, a1.JOBS_EXECUTED, a1.SCHEDULER_NAME, a1.RUNNING_SINCE, a1.CLUSTERED, a1.PERSISTENCE, a1.REMOTE_SCHEDULER, a1.CURRENTLY_EXECUTING_JOBS, a1.CPU_LOAD_JVM, a1.SYSTEM_LOAD_AVERAGE, a1.OPERATING_SYSTEM_VERSION, a1.COMMITTED_VIRTUAL_MEMORY, a1.OPERATING_SYSTEM_NAME, a1.FREE_SWAP_SPACE, a1.PROCESS_CPU_TIME, a1.TOTAL_PHYSICAL_MEMORY, a1.NUMBER_OF_PROCESSORS, a1.OPERATING_SYSTEM_ARCHITECTURE, a1.TOTAL_SWAP_SPACE, a1.IS_SCHEDULER_STANDBY, a1.IS_SCHEDULER_SHUTDOWN, a1.IS_SCHEDULER_STARTED, a1.TOTAL_DISK_SPACE, a1.UNALLOCATED_DISK_SPACE, a1.USABLE_DISK_SPACE FROM quartz.QRTZ_NODES a1 INNER JOIN (SELECT max(id) maxId, IP_ADDRESS FROM " . $config['table'] . " GROUP BY IP_ADDRESS) a2 ON a1.IP_ADDRESS = a2.IP_ADDRESS AND a1.id = a2.maxId " . $query_filters . "ORDER BY a1.$orderby $sort LIMIT $startrow,$limit";
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

//LOOP TABLE ROWS
while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr " . $tr_class . ">\n";

    foreach ($row as $field => $value) {
        //format the time fields (NEXT_FIRE_TIME, PREV_FIRE_TIME, END_TIME), if timestamp=0 then print "never"
        //if (strpos($field, '_TIME') !== false)
        //echo "<td>" . ($value != 0 ? date('Y-m-d H:i:s', $value / 1000) : "never") . "</td>\n";
        //else
        if (strpos($field, 'IP_ADDRESS') !== false) {
            $ipArray = explode(";", $row['IP_ADDRESS']);
            echo "<td>";
            foreach ($ipArray as $ip) {
                echo "<a target=\"_blank\" href=\"http://" . $ip . "\">" . $ip . "</a>\n";
            }
            echo "</td>\n";
        } else if (strpos($field, '_MEMORY') !== false || strpos($field, '_SPACE') !== false) {
            echo "<td>" . $value . " (" . bytesToSize($value) . ")</td>\n";
        } //convert bytes to human readable format
        else if (strpos($field, 'CPU_LOAD') !== false) {
            echo "<td>" . $value . " (" . round($value * 100, 2) . "%)</td>\n";
        } //convert to %
        else if (strpos($field, 'PROCESS_CPU_TIME') !== false) {
            echo "<td>" . $value . " ns</td>\n";
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
echo "</table></div>\n"; //close <div id='resultsTableReload'>

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
    /* echo "<a class=\"pointer\" title=\"Back\" href=\"#\" onclick=\"history.back();\">Back</a>&emsp;\n";
      echo "<a class=\"pointer\" title=\"Home\" href=\"index.php\">Home</a>\n";
      echo "<br><br>"; */
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

    //return '<a href="?' . $orderquery . '&' . $sortquery . '">' . $text . '</a> ' . $sortarrow;
    //javascript function loadTable is defined in reload-nodes.php
    return "<a class=\"pointer\" onClick=\"loadTable('?" . $orderquery . "&" . $sortquery . "')\">" . $text . "</a> " . $sortarrow;
}

function bytesToSize($bytes, $precision = 2) {
    $kilobyte = 1024;
    $megabyte = $kilobyte * 1024;
    $gigabyte = $megabyte * 1024;
    $terabyte = $gigabyte * 1024;

    if (($bytes >= 0) && ($bytes < $kilobyte)) {
        return $bytes . ' B';
    } elseif (($bytes >= $kilobyte) && ($bytes < $megabyte)) {
        return round($bytes / $kilobyte, $precision) . ' KB';
    } elseif (($bytes >= $megabyte) && ($bytes < $gigabyte)) {
        return round($bytes / $megabyte, $precision) . ' MB';
    } elseif (($bytes >= $gigabyte) && ($bytes < $terabyte)) {
        return round($bytes / $gigabyte, $precision) . ' GB';
    } elseif ($bytes >= $terabyte) {
        return round($bytes / $terabyte, $precision) . ' TB';
    } else {
        return $bytes . ' B';
    }
}

?>