<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"> 
    <head> 
        <title>Database Table View</title> 
        <link rel="stylesheet" type="text/css" href="reset.css" />
        <link rel="stylesheet" type="text/css" href="typography.css" />
        <link rel="stylesheet" type="text/css" href="style.css" />
        <script type="text/javascript" src="javascript/sce.js"></script>
        <script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
        <link rel = "stylesheet" href = "//code.jquery.com/ui/1.11.0/themes/cupertino/jquery-ui.css"/>
        <script src="//code.jquery.com/ui/1.11.0/jquery-ui.min.js"></script>
    </head>
    <body>
        <?php
        include_once "settings.php";
        $config['table'] = "QRTZ_STATUS";

        include './Pagination.php';
        $Pagination = new Pagination();

        //CONNECT
        //get total rows
        $xml = file_get_contents('http://data.fcc.gov/api/license-view/basicSearch/getLicenses?searchValue=Verizon+Wireless');
        $parsed = new SimpleXMLElement($xml);

        //IF STATUS TABLE IS EMPTY DISPLAY ONLY THE MENU
        if ($totalrows['total'] == 0) {
            echo "Status List is empty.<br>";
            echo "<br><a class=\"pointer\" title=\"View the job list\" href=\"jobs.php\">Jobs</a>&emsp;";
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
            echo "<br><br><a class=\"pointer\" title=\"Push Mode\" href=\"reload-status.php\"><img id='icon' src='icons/push.jpg' alt='edit' height='28' width='28'/></a>";
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
        $sql = "SELECT SCHEDULER_NAME, ID, FIRE_INSTANCE_ID, DATE, JOB_NAME, JOB_GROUP, JOB_DATA, STATUS, TRIGGER_NAME, TRIGGER_GROUP, PREV_FIRE_TIME, NEXT_FIRE_TIME, REFIRE_COUNT, RESULT, SCHEDULER_INSTANCE_ID, IP_ADDRESS FROM " . $config['table'] . " ORDER BY $orderby $sort LIMIT $startrow,$limit";
        $result = mysqli_query($link, $sql) or die(mysqli_error());

        //START TABLE AND TABLE HEADER
        echo "<table>\n<tr>";
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
                    echo "<td><a class=\"pointer\" title=\"Edit Job\" href=\"newJob.php?jobName=" . $row['JOB_NAME'] . "&jobGroup=" . $row['JOB_GROUP'] . "\"><img id='icon' src='icons/edit.gif' alt='edit' height='14' width='14'/></a>"
                    . "<a class=\"pointer\" title=\"Delete Job\" onClick=\"deleteJob('" . $row['JOB_NAME'] . "', '" . $row['JOB_GROUP'] . "')\"><img id='icon' src='icons/delete.gif' alt='Delete Job' height='14' width='14'/></a>"
                    . "<a class=\"pointer\" title=\"Resume Job\" onClick=\"resumeJob('" . $row['JOB_NAME'] . "', '" . $row['JOB_GROUP'] . "')\"><img id='icon' src='icons/play.png' alt='Resume Job' height='14' width='14'/></a>"
                    . "<a class=\"pointer\" title=\"Pause Job\" onClick=\"pauseJob('" . $row['JOB_NAME'] . "', '" . $row['JOB_GROUP'] . "')\"><img id='icon' src='icons/pause.png' alt='Pause Job' height='14' width='14'/></a>"
                    . "<a class=\"pointer\" title=\"Interrupt Job\" onClick=\"interruptJob('" . $row['JOB_NAME'] . "', '" . $row['JOB_GROUP'] . "')\"><img id='icon' src='icons/stop.png' alt='Stop Job' height='14' width='14'/></a>"
                    . "<a class=\"pointer\" title=\"View Triggers\" href=\"triggers.php?jobName=" . $row['JOB_NAME'] . "&jobGroup=" . $row['JOB_GROUP'] . "\"><img id='icon' src='icons/triggerlist.jpg' alt='edit' height='14' width='14'/></a>"
                    . "<a class=\"pointer\" title=\"Trigger Job\" onClick=\"triggerJob('" . $row['JOB_NAME'] . "', '" . $row['JOB_GROUP'] . "')\"><img id='icon' src='icons/trigger.png' alt='Trigger Job' height='14' width='14'/></a>" . $value . "</td>\n";
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

        mysqli_close($link); //close connection
        //END TABLE
        echo "</table>\n";

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
            echo "<br><b title=\"The number of currently executing jobs\">Currently executing jobs: </b>" . getCurrentlyExecutingJobs() . "&emsp;";
            $schedulerMetadata = getSchedulerMetadata();
            foreach ($schedulerMetadata as $key => $value) {
                echo "<br><b title=\"" . $value[1] . "\">" . $key . ": </b>" . $value[0] . "&emsp;";
            }
            echo "<br>";

            //print system status
            $systemStatus = getSystemStatus();
            foreach ($systemStatus as $key => $value) {
                echo "<br><b title=\"" . $value[1] . "\">" . $key . ": </b>" . $value[0] . "&emsp;";
            }
            echo "<br>";

            echo "<br><a class=\"pointer\" title=\"View the job list\" href=\"jobs.php\">Jobs</a>&emsp;";
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
            echo "<br><br><a class=\"pointer\" title=\"Push Mode\" href=\"reload-status.php\"><img id='icon' src='icons/push.jpg' alt='edit' height='28' width='28'/></a>";
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
                $sortarrow = '<img src="arrow_up.png" />';
            }

            if ($currentsort == "DESC") {
                $sortquery = "sort=ASC";
                $sortarrow = '<img src="arrow_down.png" />';
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
