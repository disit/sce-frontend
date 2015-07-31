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
$config['table'] = "QRTZ_LOGS";
//$config['nicefields'] = true; //true or false | "Field Name" or "field_name"
//$config['perpage'] = 10; // rows per page
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

//QUERY FILTERS (sent by reload-log.php)
$query_filters = '';
$query_filters .= filter_input(INPUT_GET, 'FILTER_ID') != '' ? ($query_filters != "" ? " AND ID LIKE '%" . filter_input(INPUT_GET, 'FILTER_ID') . "%'" : " WHERE ID LIKE '%" . filter_input(INPUT_GET, 'FILTER_ID') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_DATE') != '' ? ($query_filters != "" ? " AND DATE LIKE '%" . filter_input(INPUT_GET, 'FILTER_DATE') . "%'" : " WHERE DATE LIKE '%" . filter_input(INPUT_GET, 'FILTER_DATE') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_FIRE_INSTANCE_ID') != '' ? ($query_filters != "" ? " AND FIRE_INSTANCE_ID LIKE '%" . filter_input(INPUT_GET, 'FILTER_FIRE_INSTANCE_ID') . "%'" : " WHERE FIRE_INSTANCE_ID LIKE '%" . filter_input(INPUT_GET, 'FILTER_FIRE_INSTANCE_ID') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_JOB_NAME') != '' ? ($query_filters != "" ? " AND JOB_NAME LIKE '%" . filter_input(INPUT_GET, 'FILTER_JOB_NAME') . "%'" : " WHERE JOB_NAME LIKE '%" . filter_input(INPUT_GET, 'FILTER_JOB_NAME') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_JOB_GROUP') != '' ? ($query_filters != "" ? " AND JOB_GROUP LIKE '%" . filter_input(INPUT_GET, 'FILTER_JOB_GROUP') . "%'" : " WHERE JOB_GROUP LIKE '%" . filter_input(INPUT_GET, 'FILTER_JOB_GROUP') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_JOB_DATA') != '' ? ($query_filters != "" ? " AND CONVERT(JOB_DATA using latin1) LIKE '%" . filter_input(INPUT_GET, 'FILTER_JOB_DATA') . "%'" : " WHERE CONVERT(JOB_DATA using latin1) LIKE '%" . filter_input(INPUT_GET, 'FILTER_JOB_DATA') . "%'") : ""; //CONVERT(JOB_DATA using latin1) to perform a case-insensitive search on this binary field (BLOB)
$query_filters .= filter_input(INPUT_GET, 'FILTER_TRIGGER_NAME') != '' ? ($query_filters != "" ? " AND TRIGGER_NAME LIKE '%" . filter_input(INPUT_GET, 'FILTER_TRIGGER_NAME') . "%'" : " WHERE TRIGGER_NAME LIKE '%" . filter_input(INPUT_GET, 'FILTER_TRIGGER_NAME') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_TRIGGER_GROUP') != '' ? ($query_filters != "" ? " AND TRIGGER_GROUP LIKE '%" . filter_input(INPUT_GET, 'FILTER_TRIGGER_GROUP') . "%'" : " WHERE TRIGGER_GROUP LIKE '%" . filter_input(INPUT_GET, 'FILTER_TRIGGER_GROUP') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_PREV_FIRE_TIME') != '' ? ($query_filters != "" ? " AND PREV_FIRE_TIME LIKE '%" . filter_input(INPUT_GET, 'FILTER_PREV_FIRE_TIME') . "%'" : " WHERE PREV_FIRE_TIME LIKE '%" . filter_input(INPUT_GET, 'FILTER_PREV_FIRE_TIME') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_NEXT_FIRE_TIME') != '' ? ($query_filters != "" ? " AND NEXT_FIRE_TIME LIKE '%" . filter_input(INPUT_GET, 'FILTER_NEXT_FIRE_TIME') . "%'" : " WHERE NEXT_FIRE_TIME LIKE '%" . filter_input(INPUT_GET, 'FILTER_NEXT_FIRE_TIME') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_REFIRE_COUNT') != '' ? ($query_filters != "" ? " AND REFIRE_COUNT LIKE '%" . filter_input(INPUT_GET, 'FILTER_REFIRE_COUNT') . "%'" : " WHERE REFIRE_COUNT LIKE '%" . filter_input(INPUT_GET, 'FILTER_REFIRE_COUNT') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_RESULT') != '' ? ($query_filters != "" ? " AND RESULT LIKE '%" . filter_input(INPUT_GET, 'FILTER_RESULT') . "%'" : " WHERE RESULT LIKE '%" . filter_input(INPUT_GET, 'FILTER_RESULT') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_SCHEDULER_INSTANCE_ID') != '' ? ($query_filters != "" ? " AND SCHEDULER_INSTANCE_ID LIKE '%" . filter_input(INPUT_GET, 'FILTER_SCHEDULER_INSTANCE_ID') . "%'" : " WHERE SCHEDULER_INSTANCE_ID LIKE '%" . filter_input(INPUT_GET, 'FILTER_SCHEDULER_INSTANCE_ID') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_SCHED_NAME') != '' ? ($query_filters != "" ? " AND SCHED_NAME LIKE '%" . filter_input(INPUT_GET, 'FILTER_SCHED_NAME') . "%'" : " WHERE SCHED_NAME LIKE '%" . filter_input(INPUT_GET, 'FILTER_SCHED_NAME') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_IP_ADDRESS') != '' ? ($query_filters != "" ? " AND IP_ADDRESS LIKE '%" . filter_input(INPUT_GET, 'FILTER_IP_ADDRESS') . "%'" : " WHERE IP_ADDRESS LIKE '%" . filter_input(INPUT_GET, 'FILTER_IP_ADDRESS') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_STATUS') != '' ? ($query_filters != "" ? " AND STATUS LIKE '%" . filter_input(INPUT_GET, 'FILTER_STATUS') . "%'" : " WHERE STATUS LIKE '%" . filter_input(INPUT_GET, 'FILTER_STATUS') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_PROGRESS') != '' ? ($query_filters != "" ? " AND PROGRESS LIKE '%" . filter_input(INPUT_GET, 'FILTER_PROGRESS') . "%'" : " WHERE PROGRESS LIKE '%" . filter_input(INPUT_GET, 'FILTER_PROGRESS') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_LOGGER') != '' ? ($query_filters != "" ? " AND LOGGER LIKE '%" . filter_input(INPUT_GET, 'FILTER_LOGGER') . "%'" : " WHERE LOGGER LIKE '%" . filter_input(INPUT_GET, 'FILTER_LOGGER') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_LEVEL') != '' ? ($query_filters != "" ? " AND LEVEL LIKE '%" . filter_input(INPUT_GET, 'FILTER_LEVEL') . "%'" : " WHERE LEVEL LIKE '%" . filter_input(INPUT_GET, 'FILTER_LEVEL') . "%'") : "";
$query_filters .= filter_input(INPUT_GET, 'FILTER_MESSAGE') != '' ? ($query_filters != "" ? " AND MESSAGE LIKE '%" . filter_input(INPUT_GET, 'FILTER_MESSAGE') . "%'" : " WHERE MESSAGE LIKE '%" . filter_input(INPUT_GET, 'FILTER_MESSAGE') . "%'") : "";

//$query_filters .= ($query_filters != "" ? " COLLATE utf8_general_ci" : ""); // JOB_DATA field requires case insensitive search (_ci)

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

//IF LOG TABLE IS EMPTY DISPLAY ONLY THE MENU
if ($totalrows['total'] == 0) {
    echo "Log List is empty.<br>";
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
$sql = "SELECT ID, DATE, FIRE_INSTANCE_ID, JOB_NAME, JOB_GROUP, JOB_DATA, TRIGGER_NAME, TRIGGER_GROUP, STATUS, PROGRESS, PREV_FIRE_TIME, NEXT_FIRE_TIME, REFIRE_COUNT, RESULT, SCHEDULER_INSTANCE_ID, SCHEDULER_NAME, IP_ADDRESS, LOGGER, LEVEL, MESSAGE FROM " . $config['table'] . $query_filters . " ORDER BY $orderby $sort LIMIT $startrow,$limit";
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
    //javascript function loadTable is defined in reload-log.php
    return "<a class=\"pointer\" onClick=\"loadTable('?" . $orderquery . "&" . $sortquery . "')\">" . $text . "</a> " . $sortarrow;
}
?>
</body>
</html>