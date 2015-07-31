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
        <title>Smart Cloud Engine Scheduler</title>
        <link rel="stylesheet" type="text/css" href="../sce/css/reset.css" />
        <link rel="stylesheet" type="text/css" href="../sce/css/style.css" />
        <link rel="stylesheet" type="text/css" href="../sce/css/typography.css" />
        <link rel="stylesheet" type="text/css" href = "../sce/css/jquery-ui.css"/>
        <script type="text/javascript" src="../sce/javascript/jquery-2.1.0.min.js"></script>
        <script type="text/javascript" src="../sce/javascript/jquery-ui.min.js"></script>
        <script type="text/javascript" src="../sce/javascript/sce.js"></script>
    </head>
    <body>
        <?php
        include_once "../sce/header.php"; //include header
        include_once "../sce/settings.php";
        //DATABASE SETTINGS
        /* $config['host'] = "localhost";
          $config['user'] = "root";
          $config['pass'] = "centos";
          $config['database'] = "quartz";
          $config['nicefields'] = true; //true or false | "Field Name" or "field_name"
          $config['perpage'] = 10;
          $config['showpagenumbers'] = true; //true or false
          $config['showprevnext'] = true; //true or false */
        $config['table'] = "QRTZ_SPARQL";

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
        //$totalrows = mysqli_fetch_array(mysqli_query($link, "SELECT COUNT(*) AS total FROM " . $config['table'] . " Q LEFT OUTER JOIN " . $config['table'] . " Q2 ON (Q.job_name = Q2.job_name AND Q.job_group = Q2.job_group and Q.ID < Q2.ID) WHERE Q2.job_name IS NULL"));
        $totalrows = mysqli_fetch_array(mysqli_query($link, "SELECT COUNT(*) AS total FROM " . $config['table']));

        //IF STATUS TABLE IS EMPTY DISPLAY ONLY THE MENU
        if ($totalrows['total'] == 0) {
            echo "<div id='resultsTable'><table>\n<tr>";
            echo "Status List is empty.<br>";
            echo "</table></div>\n"; //close <div id='resultsTable'>
            echo "</body>";
            echo "</html>";
            exit;
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
            $orderby = "timestamp";
            //default sort
            $sort = "ASC";
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
        $sql = "SELECT timestamp, sla, metric, metric_name, metric_unit, metric_timestamp, virtual_machine AS vm , virtual_machine_name AS vm_name, host_machine, value, relation, threshold, call_url, business_configuration AS configuration FROM " . $config['table'] . " WHERE alarm = 1 ORDER BY $orderby $sort LIMIT $startrow,$limit";
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
                if (strpos($field, 'sla') !== false) {
                    echo "<td><a class=\"pointer\" title=\"View Linked Open Graph\" target=\"_blank\" href=\"linkedOpenGraph.php?id=" . $value . "\"><img id='icon' src='../sce/images/edit.gif' alt='edit' height='14' width='14'/></a>" .
                    "<a class=\"pointer\" title=\"View Graph\" target=\"blank\" href=\"graph.php?sla=" . $row['sla'] . "&virtual_machine=" . $row['virtual_machine'] . "&virtual_machine_name=" . $row['virtual_machine_name'] . "&metric_name=" . $row['metric_name'] . "\"><img id='icon' src='../sce/images/graph.png' alt='edit' height='20' width='25'/></a>" .
                    "<a class=\"pointer\" title=\"View SLA Status\" target=\"blank\" href=\"sla_status.php?sla=" . $row['sla'] . "\"><img id='icon' src='../sce/images/sla_status.png' alt='edit' height='20' width='25'/></a>" . $value . "</a>\n";
                    "<a title=\"Edit Job\" href=\"../sce/newJob.php?jobName=" . $value . "&jobGroup=" . $value . "\">" . $value . "</a></td>\n";
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

            //print scheduler metadata
            /* echo "<br><b title=\"The number of currently executing jobs\">Currently executing jobs: </b>" . getCurrentlyExecutingJobs() . "&emsp;";
              $schedulerMetadata = getSchedulerMetadata();
              foreach ($schedulerMetadata as $key => $value) {
              echo "<br><b title=\"" . $value[1] . "\">" . $key . ": </b>" . $value[0] . "&emsp;";
              }
              echo "<br>";

              //print system status
              $systemStatus = getSystemStatus();
              foreach ($systemStatus as $key => $value) {
              echo "<br><b title=\"" . $value[1] . "\">" . $key . ": </b>" . $value[0] . "&emsp;";
              } */
            echo "<br>";

            echo "<a class=\"pointer button\" title=\"View the SLA list\" href=\"sla.php\">SLAs</a>&emsp;\n";
            echo "<a class=\"pointer button\" title=\"View the VM list\" href=\"vm.php\">VMs</a>&emsp;\n";
            echo "<a class=\"pointer button\" title=\"View the Host Machine list\" href=\"host.php\">Hosts</a>&emsp;\n";
            echo "<a class=\"pointer button\" title=\"View the Business Configuration list\" href=\"businessConfiguration.php\">Business Configurations</a>&emsp;\n";
            echo "<a class=\"pointer button\" title=\"View the Application list\" href=\"application.php\">Applications</a>&emsp;\n";
            echo "<a class=\"pointer button\" title=\"Query the Knowledge Base and then populate the scheduler with a Job for every SLA\" href=\"#\" onclick=\"buildJobs();return false;\">Build Jobs</a>&emsp;";
            echo "<br><br>";
            echo "<a class=\"pointer button\" title=\"Back\" href=\"#\" onclick=\"if(document.referrer) {window.open(document.referrer,'_self');} else {history.go(-1);}return false;\">Back</a>&emsp;\n";
            echo "<a class=\"pointer button\" title=\"Scheduler\" href=\"../sce/index.php\">Scheduler</a>&emsp;\n";
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
                $sortarrow = '<img src="../sce/images/arrow_up.png" />';
            }

            if ($currentsort == "DESC") {
                $sortquery = "sort=ASC";
                $sortarrow = '<img src="../sce/images/arrow_down.png" />';
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

        // get the number of running jobs
        function getCurrentlyExecutingJobs() {
            global $config;
            $postData["id"] = "getCurrentlyExecutingJobs";
            $jsonData["json"] = json_encode($postData);
            $arr = json_decode(postData($jsonData, "http://" . $config["tomcat"] . ":8080/SmartCloudEngine/index.jsp"), true);
            return count(objectToArray(json_decode($arr[1])));
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
        ?>
    </body>
</html>
