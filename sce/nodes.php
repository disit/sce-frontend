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
$query_filters .= filter_input(INPUT_GET, 'FILTER_ID') != '' ? ($query_filters != "" ? " AND ID LIKE '%" . filter_input(INPUT_GET, 'FILTER_ID') . "%'" : " WHERE ID LIKE '%" . filter_input(INPUT_GET, 'FILTER_ID') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_DATE') != '' ? ($query_filters != "" ? " AND DATE LIKE '%" . filter_input(INPUT_GET, 'FILTER_DATE') . "%'" : " WHERE DATE LIKE '%" . filter_input(INPUT_GET, 'FILTER_DATE') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_IP_ADDRESS') != '' ? ($query_filters != "" ? " AND IP_ADDRESS LIKE '%" . filter_input(INPUT_GET, 'FILTER_IP_ADDRESS') . "%'" : " WHERE IP_ADDRESS LIKE '%" . filter_input(INPUT_GET, 'FILTER_IP_ADDRESS') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_SCHEDULER_INSTANCE_ID') != '' ? ($query_filters != "" ? " AND SCHEDULER_INSTANCE_ID LIKE '%" . filter_input(INPUT_GET, 'FILTER_SCHEDULER_INSTANCE_ID') . "%'" : " WHERE SCHEDULER_INSTANCE_ID LIKE '%" . filter_input(INPUT_GET, 'FILTER_SCHEDULER_INSTANCE_ID') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_CPU_LOAD') != '' ? ($query_filters != "" ? " AND CPU_LOAD LIKE '%" . filter_input(INPUT_GET, 'FILTER_CPU_LOAD') . "%'" : " WHERE CPU_LOAD LIKE '%" . filter_input(INPUT_GET, 'FILTER_CPU_LOAD') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_FREE_PHYSICAL_MEMORY') != '' ? ($query_filters != "" ? " AND FREE_PHYSICAL_MEMORY LIKE '%" . filter_input(INPUT_GET, 'FILTER_FREE_PHYSICAL_MEMORY') . "%'" : " WHERE FREE_PHYSICAL_MEMORY LIKE '%" . filter_input(INPUT_GET, 'FILTER_FREE_PHYSICAL_MEMORY') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_JOBS_EXECUTED') != '' ? ($query_filters != "" ? " AND JOBS_EXECUTED LIKE '%" . filter_input(INPUT_GET, 'FILTER_JOBS_EXECUTED') . "%'" : " WHERE JOBS_EXECUTED LIKE '%" . filter_input(INPUT_GET, 'FILTER_JOBS_EXECUTED') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_SCHED_NAME') != '' ? ($query_filters != "" ? " AND SCHED_NAME LIKE '%" . filter_input(INPUT_GET, 'FILTER_SCHED_NAME') . "%'" : " WHERE SCHED_NAME LIKE '%" . filter_input(INPUT_GET, 'FILTER_SCHED_NAME') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_RUNNING_SINCE') != '' ? ($query_filters != "" ? " AND RUNNING_SINCE LIKE '%" . filter_input(INPUT_GET, 'FILTER_RUNNING_SINCE') . "%'" : " WHERE RUNNING_SINCE LIKE '%" . filter_input(INPUT_GET, 'FILTER_RUNNING_SINCE') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_CLUSTERED') != '' ? ($query_filters != "" ? " AND CLUSTERED LIKE '%" . filter_input(INPUT_GET, 'FILTER_CLUSTERED') . "%'" : " WHERE CLUSTERED LIKE '%" . filter_input(INPUT_GET, 'FILTER_CLUSTERED') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_PERSISTENCE') != '' ? ($query_filters != "" ? " AND PERSISTENCE LIKE '%" . filter_input(INPUT_GET, 'FILTER_PERSISTENCE') . "%'" : " WHERE PERSISTENCE LIKE '%" . filter_input(INPUT_GET, 'FILTER_PERSISTENCE') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_REMOTE_SCHEDULER') != '' ? ($query_filters != "" ? " AND REMOTE_SCHEDULER LIKE '%" . filter_input(INPUT_GET, 'FILTER_REMOTE_SCHEDULER') . "%'" : " WHERE REMOTE_SCHEDULER LIKE '%" . filter_input(INPUT_GET, 'FILTER_REMOTE_SCHEDULER') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_CURRENTLY_EXECUTING_JOBS') != '' ? ($query_filters != "" ? " AND CURRENTLY_EXECUTING_JOBS LIKE '%" . filter_input(INPUT_GET, 'FILTER_CURRENTLY_EXECUTING_JOBS') . "%'" : " WHERE CURRENTLY_EXECUTING_JOBS LIKE '%" . filter_input(INPUT_GET, 'FILTER_CURRENTLY_EXECUTING_JOBS') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_CPU_LOAD_JVM') != '' ? ($query_filters != "" ? " AND CPU_LOAD_JVM LIKE '%" . filter_input(INPUT_GET, 'FILTER_CPU_LOAD_JVM') . "%'" : " WHERE CPU_LOAD_JVM LIKE '%" . filter_input(INPUT_GET, 'FILTER_CPU_LOAD_JVM') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_SYSTEM_LOAD_AVERAGE') != '' ? ($query_filters != "" ? " AND SYSTEM_LOAD_AVERAGE LIKE '%" . filter_input(INPUT_GET, 'FILTER_SYSTEM_LOAD_AVERAGE') . "%'" : " WHERE SYSTEM_LOAD_AVERAGE LIKE '%" . filter_input(INPUT_GET, 'FILTER_SYSTEM_LOAD_AVERAGE') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_OPERATING_SYSTEM_VERSION') != '' ? ($query_filters != "" ? " AND OPERATING_SYSTEM_VERSION LIKE '%" . filter_input(INPUT_GET, 'FILTER_OPERATING_SYSTEM_VERSION') . "%'" : " WHERE OPERATING_SYSTEM_VERSION LIKE '%" . filter_input(INPUT_GET, 'FILTER_OPERATING_SYSTEM_VERSION') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_COMMITTED_VIRTUAL_MEMORY') != '' ? ($query_filters != "" ? " AND COMMITTED_VIRTUAL_MEMORY LIKE '%" . filter_input(INPUT_GET, 'FILTER_COMMITTED_VIRTUAL_MEMORY') . "%'" : " WHERE COMMITTED_VIRTUAL_MEMORY LIKE '%" . filter_input(INPUT_GET, 'FILTER_COMMITTED_VIRTUAL_MEMORY') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_OPERATING_SYSTEM_NAME') != '' ? ($query_filters != "" ? " AND OPERATING_SYSTEM_NAME LIKE '%" . filter_input(INPUT_GET, 'FILTER_OPERATING_SYSTEM_NAME') . "%'" : " WHERE OPERATING_SYSTEM_NAME LIKE '%" . filter_input(INPUT_GET, 'FILTER_OPERATING_SYSTEM_NAME') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_FREE_SWAP_SPACE') != '' ? ($query_filters != "" ? " AND FREE_SWAP_SPACE LIKE '%" . filter_input(INPUT_GET, 'FILTER_FREE_SWAP_SPACE') . "%'" : " WHERE FREE_SWAP_SPACE LIKE '%" . filter_input(INPUT_GET, 'FILTER_FREE_SWAP_SPACE') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_PROCESS_CPU_TIME') != '' ? ($query_filters != "" ? " AND PROCESS_CPU_TIME LIKE '%" . filter_input(INPUT_GET, 'FILTER_PROCESS_CPU_TIME') . "%'" : " WHERE PROCESS_CPU_TIME LIKE '%" . filter_input(INPUT_GET, 'FILTER_PROCESS_CPU_TIME') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_TOTAL_PHYSICAL_MEMORY') != '' ? ($query_filters != "" ? " AND TOTAL_PHYSICAL_MEMORY LIKE '%" . filter_input(INPUT_GET, 'FILTER_TOTAL_PHYSICAL_MEMORY') . "%'" : " WHERE TOTAL_PHYSICAL_MEMORY LIKE '%" . filter_input(INPUT_GET, 'FILTER_TOTAL_PHYSICAL_MEMORY') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_NUMBER_OF_PROCESSORS') != '' ? ($query_filters != "" ? " AND NUMBER_OF_PROCESSORS LIKE '%" . filter_input(INPUT_GET, 'FILTER_NUMBER_OF_PROCESSORS') . "%'" : " WHERE NUMBER_OF_PROCESSORS LIKE '%" . filter_input(INPUT_GET, 'FILTER_NUMBER_OF_PROCESSORS') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_OPERATING_SYSTEM_ARCHITECTURE') != '' ? ($query_filters != "" ? " AND OPERATING_SYSTEM_ARCHITECTURE LIKE '%" . filter_input(INPUT_GET, 'FILTER_OPERATING_SYSTEM_ARCHITECTURE') . "%'" : " WHERE OPERATING_SYSTEM_ARCHITECTURE LIKE '%" . filter_input(INPUT_GET, 'FILTER_OPERATING_SYSTEM_ARCHITECTURE') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_TOTAL_SWAP_SPACE') != '' ? ($query_filters != "" ? " AND TOTAL_SWAP_SPACE LIKE '%" . filter_input(INPUT_GET, 'FILTER_TOTAL_SWAP_SPACE') . "%'" : " WHERE TOTAL_SWAP_SPACE LIKE '%" . filter_input(INPUT_GET, 'FILTER_TOTAL_SWAP_SPACE') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_IS_SCHEDULER_STANDBY') != '' ? ($query_filters != "" ? " AND IS_SCHEDULER_STANDBY LIKE '%" . filter_input(INPUT_GET, 'FILTER_IS_SCHEDULER_STANDBY') . "%'" : " WHERE IS_SCHEDULER_STANDBY LIKE '%" . filter_input(INPUT_GET, 'FILTER_IS_SCHEDULER_STANDBY') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_IS_SCHEDULER_SHUTDOWN') != '' ? ($query_filters != "" ? " AND IS_SCHEDULER_SHUTDOWN LIKE '%" . filter_input(INPUT_GET, 'FILTER_IS_SCHEDULER_SHUTDOWN') . "%'" : " WHERE IS_SCHEDULER_SHUTDOWN LIKE '%" . filter_input(INPUT_GET, 'FILTER_IS_SCHEDULER_SHUTDOWN') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_IS_SCHEDULER_STARTED') != '' ? ($query_filters != "" ? " AND IS_SCHEDULER_STARTED LIKE '%" . filter_input(INPUT_GET, 'FILTER_IS_SCHEDULER_STARTED') . "%'" : " WHERE IS_SCHEDULER_STARTED LIKE '%" . filter_input(INPUT_GET, 'FILTER_IS_SCHEDULER_STARTED') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_TOTAL_DISK_SPACE') != '' ? ($query_filters != "" ? " AND TOTAL_DISK_SPACE LIKE '%" . filter_input(INPUT_GET, 'FILTER_TOTAL_DISK_SPACE') . "%'" : " WHERE TOTAL_DISK_SPACE LIKE '%" . filter_input(INPUT_GET, 'FILTER_TOTAL_DISK_SPACE') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_UNALLOCATED_DISK_SPACE') != '' ? ($query_filters != "" ? " AND UNALLOCATED_DISK_SPACE LIKE '%" . filter_input(INPUT_GET, 'FILTER_UNALLOCATED_DISK_SPACE') . "%'" : " WHERE UNALLOCATED_DISK_SPACE LIKE '%" . filter_input(INPUT_GET, 'FILTER_UNALLOCATED_DISK_SPACE') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_USABLE_DISK_SPACE') != '' ? ($query_filters != "" ? " AND USABLE_DISK_SPACE LIKE '%" . filter_input(INPUT_GET, 'FILTER_USABLE_DISK_SPACE') . "%'" : " WHERE USABLE_DISK_SPACE LIKE '%" . filter_input(INPUT_GET, 'FILTER_USABLE_DISK_SPACE') . "%'") : "";

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
$totalrows = mysqli_fetch_array(mysqli_query($link, "SELECT COUNT(*) AS total FROM " . $config['table'] . $query_filters));

//IF NODES TABLE IS EMPTY DISPLAY ONLY THE MENU
if ($totalrows['total'] == 0) {
    echo "Nodes Log List is empty.<br>";
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
$sql = "SELECT ID, DATE, IP_ADDRESS, SCHEDULER_INSTANCE_ID, CPU_LOAD, FREE_PHYSICAL_MEMORY, JOBS_EXECUTED, SCHEDULER_NAME, RUNNING_SINCE, CLUSTERED, PERSISTENCE, REMOTE_SCHEDULER, CURRENTLY_EXECUTING_JOBS, CPU_LOAD_JVM, SYSTEM_LOAD_AVERAGE, OPERATING_SYSTEM_VERSION, COMMITTED_VIRTUAL_MEMORY, OPERATING_SYSTEM_NAME, FREE_SWAP_SPACE, PROCESS_CPU_TIME, TOTAL_PHYSICAL_MEMORY, NUMBER_OF_PROCESSORS, OPERATING_SYSTEM_ARCHITECTURE, TOTAL_SWAP_SPACE, IS_SCHEDULER_STANDBY, IS_SCHEDULER_SHUTDOWN, IS_SCHEDULER_STARTED, TOTAL_DISK_SPACE, UNALLOCATED_DISK_SPACE, USABLE_DISK_SPACE FROM " . $config['table'] . $query_filters . " ORDER BY $orderby $sort LIMIT $startrow,$limit";
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