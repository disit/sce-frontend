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
        <title>Virtual Storage</title> 
        <link rel="stylesheet" type="text/css" href="../sce/css/reset.css"/>
        <link rel="stylesheet" type="text/css" href="../sce/css/style.css"/>
        <link rel="stylesheet" type="text/css" href="../sce/css/typography.css"/>
        <link rel="stylesheet" type="text/css" href="../sce/css/jquery-ui.css"/>
        <script type="text/javascript" src="../sce/javascript/manager.js"></script>
        <script type="text/javascript" src="../sce/javascript/sce.js"></script>
        <script type="text/javascript" src="../sce/javascript/jquery-2.1.0.min.js"></script>
        <script type="text/javascript" src="../sce/javascript/jquery-ui.min.js"></script>
    </head>
    <body>
        <?php
        include_once '../sce/header.php';
        include_once "../sce/settings.php";
        include_once "../sce/functions.php";
        //DATABASE SETTINGS
        $config['table'] = "QRTZ_STATUS";

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
        $totalrows = getNumberOfSLAs();

        //IF STATUS TABLE IS EMPTY DISPLAY ONLY THE MENU
        if ($totalrows == 0) {
            echo "Status List is empty.<br>";
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
              echo "<a class=\"pointer\" title=\"View the nodes status\" href=\"nodes-status-static.php\">Nodes Status</a>&emsp;";
              echo "<a class=\"pointer\" title=\"View the nodes status log\" href=\"nodes-static.php\">Nodes Log</a>&emsp;";
              echo "<a class=\"pointer\" title=\"View the log\" href=\"log-static.php\">Log</a>&emsp;";
              echo "<a class=\"pointer\" title=\"Truncate the catalina log file of this scheduler\" href=\"#\" onclick=\"truncateCatalinaLog();return false;\">Truncate Catalina Log</a>&emsp;";
              echo "<br><br>";
              echo "<a class=\"pointer\" title=\"Back\" href=\"#\" onclick=\"if(document.referrer) {window.open(document.referrer,'_self');} else {history.go(-1);}return false;\">Back</a>&emsp;\n";
              echo "<a class=\"pointer\" title=\"Home\" href=\"index.php\">Home</a>&emsp;\n";
              echo "<a title=\"Clears (deletes) all scheduling data - all Jobs, Triggers, Calendars\" href=\"#\" onclick=\"clearScheduler();return false;\">Clear Scheduler</a>&emsp;";
              echo "<br><br><a class=\"pointer\" title=\"Push Mode\" href=\"reload-status.php\"><img id='icon' src='icons/push.jpg' alt='edit' height='28' width='28'/></a>"; */
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
            $pagination_links = $Pagination->showPageNumbers($totalrows, $page, $limit, $config['pagelinks']); // add $config['pagelinks'] as a fourth parameter, to print only the first N page links (default = 50)
        } else {
            $pagination_links = null;
        }

        if ($config['showprevnext'] == true) {
            $prev_link = $Pagination->showPrev($totalrows, $page, $limit);
            $prev_link_more = $Pagination->showPrevMore($totalrows, $page, $limit);
            $next_link = $Pagination->showNext($totalrows, $page, $limit);
            $next_link_more = $Pagination->showNextMore($totalrows, $page, $limit);
        } else {
            $prev_link = null;
            $prev_link_more = null;
            $next_link = null;
            $next_link_more = null;
        }

        //IF ORDERBY NOT SET, SET DEFAULT
        if (!isset($_GET['orderby']) || trim($_GET['orderby']) == "") {
            //GET FIRST FIELD IN TABLE TO BE DEFAULT SORT
            //$sql = "SELECT ID FROM " . $config['table'] . " LIMIT 1"; //USE ID AS THE DEFAULT SORT FIELD
            //$result = mysqli_query($link, $sql) or die(mysqli_error());
            //$array = mysqli_fetch_assoc($result);
            //first field
            /* $i = 0;
              foreach ($array as $key => $value) {
              if ($i > 0) {
              break;
              } else {
              $orderby = $key;
              }
              $i++;
              } */
            $orderby = "Virtual_Storage";
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
        $sparql = "PREFIX%20icr%3A%3Chttp%3A%2F%2Fwww.cloudicaro.it%2Fcloud_ontology%2Fcore%23%3E%0ASELECT%20%3FVirtual_Storage%20%3FName%20%3FIdentifier%20%3FDisk_Size%20WHERE%20%7B%0A%20%3FVirtual_Storage%20a%20icr%3AVirtualStorage.%0A%20%3FVirtual_Storage%20icr%3AhasName%20%3FName.%0A%20%3FVirtual_Storage%20icr%3AhasIdentifier%20%3FIdentifier.%0A%20%3FVirtual_Storage%20icr%3AhasDiskSize%20%3FDisk_Size.%0A%7D";
        $result = getSPARQLResult($sparql, $sort, $orderby, $startrow, $limit); //in sce/functions.php
        //START TABLE AND TABLE HEADER
        echo "<div id='resultsTable'><table>\n<tr>";
        //$array = $result;
        //counter
        $i = 1;

        foreach ($result as $k1 => $v1) { //[0]=>array(3)
            foreach ($v1 as $key => $v2) { //["act"]=>array(2)
                foreach ($v2 as $v3 => $value) {
                    if ($v3 != 'type' && $v3 != 'datatype' && $i <= count($v1))
                    /* if ($config['nicefields']) {
                      $field = ucwords(str_replace("_", " ", $key));
                      } */ {
                        $field = columnSortArrows($key, $key, $orderby, $sort);
                        echo "<th>" . $field . "</th>\n";
                        $i++;
                    }
                }
            }
        }
        echo "</tr>\n";

        //reset result pointer
        //mysqli_data_seek($result, 0);
        //start first row style
        $tr_class = "class='odd'";

        //counter
        $i = 1;

        //LOOP TABLE ROWS

        foreach ($result as $k1 => $v1) {
            echo "<tr " . $tr_class . " >\n";
            foreach ($v1 as $key => $v2) {
                foreach ($v2 as $v3 => $value) {
                    if ($v3 != 'type' && $v3 != "datatype") {
                        if (strpos($key, 'Virtual_Storage') !== false) {
                            echo "<td><a class=\"pointer\" title=\"View Linked Open Graph\" target=\"_blank\" href=\"linkedOpenGraph.php?id=" . $v1['Virtual_Storage']['value'] . "\"><img id='icon' src='../sce/images/edit.gif' alt='edit' height='14' width='14'/></a>" . $value . "</td>\n";
                        } else
                            echo "<td>" . $value . "</td>\n";
                        if ($i % count($v1) == 0)
                            echo "</tr>\n";
                        $i++;
                    }
                }
            }
            //switch row style
            if ($tr_class == "class='odd'") {
                $tr_class = "class='even'";
            } else {
                $tr_class = "class='odd'";
            }
        }
        //}
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
              echo "<br>"; */

            //print system status
            $systemStatus = getSystemStatus();
            foreach ($systemStatus as $key => $value) {
                echo "<br><b title=\"" . $value[1] . "\">" . $key . ": </b>" . $value[0] . "&emsp;";
            }
            echo "<br>";

            echo "<a class=\"pointer\" title=\"View the SLA list\" href=\"sla.php\">SLAs</a>&emsp;\n";
            echo "<a class=\"pointer\" title=\"View the VM list\" href=\"vm.php\">VMs</a>&emsp;\n";
            echo "<a class=\"pointer\" title=\"View the Host Machine list\" href=\"host.php\">Hosts</a>&emsp;\n";
            echo "<a class=\"pointer\" title=\"View the Business Configuration list\" href=\"businessConfiguration.php\">Business Configurations</a>&emsp;\n";
            echo "<a class=\"pointer\" title=\"View the Application list\" href=\"application.php\">Applications</a>&emsp;\n";
            echo "<a class=\"pointer\" title=\"Query the Knowledge Base and then populate the scheduler with a Job for every SLA\" href=\"#\" onclick=\"buildJobs();return false;\">Build Jobs</a>&emsp;";
            echo "<br><br>";
            echo "<a class=\"pointer\" title=\"Back\" href=\"#\" onclick=\"if(document.referrer) {window.open(document.referrer,'_self');} else {history.go(-1);}return false;\">Back</a>&emsp;\n";
            echo "<a class=\"pointer\" title=\"Scheduler\" href=\"../sce/index.php\">Scheduler</a>&emsp;\n";
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
              echo "<a class=\"pointer\" title=\"View the nodes status\" href=\"nodes-status-static.php\">Nodes Status</a>&emsp;";
              echo "<a class=\"pointer\" title=\"View the nodes status log\" href=\"nodes-static.php\">Nodes Log</a>&emsp;";
              echo "<a class=\"pointer\" title=\"View the log\" href=\"log-static.php\">Log</a>&emsp;";
              echo "<a class=\"pointer\" title=\"Truncate the catalina log file of this scheduler\" href=\"#\" onclick=\"truncateCatalinaLog();return false;\">Truncate Catalina Log</a>&emsp;";
              echo "<br><br>";
              //echo "<a class=\"pointer\" title=\"Back\" href=\"#\" onclick=\"history.back();\">Back</a>&emsp;\n";
              echo "<a class=\"pointer\" title=\"Back\" href=\"#\" onclick=\"if(document.referrer) {window.open(document.referrer,'_self');} else {history.go(-1);}return false;\">Back</a>&emsp;\n";
              echo "<a class=\"pointer\" title=\"Home\" href=\"index.php\">Home</a>&emsp;\n";
              echo "<a title=\"Clears (deletes) all scheduling data - all Jobs, Triggers, Calendars\" href=\"#\" onclick=\"clearScheduler();return false;\">Clear Scheduler</a>&emsp;";
              echo "<br><br><a class=\"pointer\" title=\"Push Mode\" href=\"reload-status.php\"><img id='icon' src='icons/push.jpg' alt='edit' height='28' width='28'/></a>"; */
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
