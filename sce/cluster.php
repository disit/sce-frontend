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
<html xmlns="http://www.w3.org/1999/xhtml"> 
    <head> 
        <title>Smart Cloud Engine</title> 
        <link rel="stylesheet" type="text/css" href="css/typography.css" />
        <link rel="stylesheet" type="text/css" href="../index.css" />
    </head>
    <body>
        <?php

        function mdate($format, $microtime = null) {
            $microtime = explode(' ', ($microtime ? $microtime : microtime()));
            if (count($microtime) != 2) {
                return false;
            }
            $microtime[0] = $microtime[0] * 1000000;
            $format = str_replace('u', $microtime[0], $format);
            return date($format, $microtime[1]);
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

        function bytesToSize($bytes, $precision = 2) {
            $kilobyte = 1024;
            $megabyte = $kilobyte * 1024;
            $gigabyte = $megabyte * 1024;
            $terabyte = $gigabyte * 1024;

            if (($bytes >= 0) && ($bytes < $kilobyte)) {
                return $bytes . ' B';
            } elseif (($bytes >= $kilobyte) && ($bytes < $megabyte)) {
                return min_precision($bytes / $kilobyte, $precision) . ' KB';
            } elseif (($bytes >= $megabyte) && ($bytes < $gigabyte)) {
                return min_precision($bytes / $megabyte, $precision) . ' MB';
            } elseif (($bytes >= $gigabyte) && ($bytes < $terabyte)) {
                return min_precision($bytes / $gigabyte, $precision) . ' GB';
            } elseif ($bytes >= $terabyte) {
                return min_precision($bytes / $terabyte, $precision) . ' TB';
            } else {
                return $bytes . ' B';
            }
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

        // calculate the percentage of jobs failed in the last day
        function jobs_failed_24h($link) {
            $array = array();
            //$sql = "SELECT COUNT(*) AS num FROM quartz.QRTZ_LOGS WHERE DATE > '" . date('Y-m-d H:i:s', strtotime(' -1 day')) . "' AND STATUS = 'FAILED'";
            $sql = "SELECT COUNT(*) AS num FROM quartz.QRTZ_LOGS WHERE DATE > NOW() - INTERVAL 1 DAY AND STATUS = 'FAILED'";
            $result = mysqli_query($link, $sql) or die(mysqli_error());
            while ($row = mysqli_fetch_assoc($result)) {
                $array['nfailed'] = $row['num'];
            }
            //$sql = "SELECT COUNT(*) AS num FROM quartz.QRTZ_LOGS WHERE DATE > '" . date('Y-m-d H:i:s', strtotime(' -1 day')) . "' AND STATUS = 'SUCCESS'";
            $sql = "SELECT COUNT(*) AS num FROM quartz.QRTZ_LOGS WHERE DATE > NOW() - INTERVAL 1 DAY AND STATUS = 'SUCCESS'";
            $result = mysqli_query($link, $sql) or die(mysqli_error());
            while ($row = mysqli_fetch_assoc($result)) {
                $array['nsuccess'] = $row['num'];
            }
            return $array;
        }

        // calculate the percentage of jobs failed in the last 7 days
        function jobs_failed_7day($link) {
            $array = array();
            $sql = "SELECT COUNT(*) AS num FROM quartz.QRTZ_LOGS WHERE DATE > NOW() - INTERVAL 7 DAY AND STATUS = 'FAILED'";
            $result = mysqli_query($link, $sql) or die(mysqli_error());
            while ($row = mysqli_fetch_assoc($result)) {
                $array['nfailed'] = $row['num'];
            }
            $sql = "SELECT COUNT(*) AS num FROM quartz.QRTZ_LOGS WHERE DATE > NOW() - INTERVAL 7 DAY AND STATUS = 'SUCCESS'";
            $result = mysqli_query($link, $sql) or die(mysqli_error());
            while ($row = mysqli_fetch_assoc($result)) {
                $array['nsuccess'] = $row['num'];
            }
            return $array;
        }

        include_once "header.php";
        include_once "settings.php";

        $milliseconds = round(microtime(true) * 1000);

        //CONNECT
        $link = mysqli_connect($config['host'], $config['user'], $config['pass'], $config['database']);

        /* check connection */
        if (mysqli_connect_errno()) {
            printf("Connection failed: %s\n", mysqli_connect_error());
            exit();
        }

        //$sql = "SELECT a1.DATE AS LAST_CHECK, a1.IP_ADDRESS, a1.SCHEDULER_INSTANCE_ID, a1.CPU_LOAD, a1.FREE_PHYSICAL_MEMORY, a1.JOBS_EXECUTED, a1.SCHEDULER_NAME, a1.RUNNING_SINCE, a1.CLUSTERED, a1.PERSISTENCE, a1.REMOTE_SCHEDULER, a1.CURRENTLY_EXECUTING_JOBS, a1.CPU_LOAD_JVM, a1.SYSTEM_LOAD_AVERAGE, a1.OPERATING_SYSTEM_VERSION, a1.COMMITTED_VIRTUAL_MEMORY, a1.OPERATING_SYSTEM_NAME, a1.FREE_SWAP_SPACE, a1.PROCESS_CPU_TIME, a1.TOTAL_PHYSICAL_MEMORY, a1.NUMBER_OF_PROCESSORS, a1.OPERATING_SYSTEM_ARCHITECTURE, a1.TOTAL_SWAP_SPACE, a1.IS_SCHEDULER_STANDBY, a1.IS_SCHEDULER_SHUTDOWN, a1.IS_SCHEDULER_STARTED, a1.TOTAL_DISK_SPACE, a1.UNALLOCATED_DISK_SPACE, a1.USABLE_DISK_SPACE FROM quartz.QRTZ_NODES a1 INNER JOIN (SELECT max(id) maxId, IP_ADDRESS FROM quartz.QRTZ_NODES GROUP BY IP_ADDRESS) a2 ON a1.IP_ADDRESS = a2.IP_ADDRESS AND a1.id = a2.maxId";
        $sql = "SELECT a1.DATE AS LAST_CHECK, a1.IP_ADDRESS, a1.SCHEDULER_INSTANCE_ID, a1.CPU_LOAD, a1.FREE_PHYSICAL_MEMORY, a1.JOBS_EXECUTED, a1.SCHEDULER_NAME, a1.RUNNING_SINCE, a1.CLUSTERED, a1.PERSISTENCE, a1.REMOTE_SCHEDULER, a1.CURRENTLY_EXECUTING_JOBS, a1.CPU_LOAD_JVM, a1.SYSTEM_LOAD_AVERAGE, a1.OPERATING_SYSTEM_VERSION, a1.COMMITTED_VIRTUAL_MEMORY, a1.OPERATING_SYSTEM_NAME, a1.FREE_SWAP_SPACE, a1.PROCESS_CPU_TIME, a1.TOTAL_PHYSICAL_MEMORY, a1.NUMBER_OF_PROCESSORS, a1.OPERATING_SYSTEM_ARCHITECTURE, a1.TOTAL_SWAP_SPACE, a1.IS_SCHEDULER_STANDBY, a1.IS_SCHEDULER_SHUTDOWN, a1.IS_SCHEDULER_STARTED, a1.TOTAL_DISK_SPACE, a1.UNALLOCATED_DISK_SPACE, a1.USABLE_DISK_SPACE, a1.IP_ADDRESS, a3.PREV_FIRE_TIME FROM quartz.QRTZ_NODES a1 INNER JOIN (SELECT max(id) maxId, IP_ADDRESS FROM quartz.QRTZ_NODES GROUP BY IP_ADDRESS) a2 ON a1.IP_ADDRESS = a2.IP_ADDRESS AND a1.id = a2.maxId INNER JOIN (SELECT max(id) maxId, max(PREV_FIRE_TIME) PREV_FIRE_TIME, IP_ADDRESS FROM quartz.QRTZ_STATUS WHERE STATUS='SUCCESS' GROUP BY IP_ADDRESS) a3 ON a1.IP_ADDRESS = a3.IP_ADDRESS";
        $result = mysqli_query($link, $sql) or die(mysqli_error());
        $cpuTotal = 0;
        $cpuLoadTotal = 0;
        $memTotal = 0;
        $memFree = 0;
        $mipsTotal = 0;
        $numCores = 0;
        $jobsperhourTotal = 0;
        $jobsExecutedTotal = 0;
        echo "<div class='clusterContainer'/>";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<div class='cluster'>\n";
            echo "<p class='clusterTitle'><a target='_blank' style='text-decoration:none;color:black;' href='http://" . $row["IP_ADDRESS"] . "/sce'>" . $row["IP_ADDRESS"] . "</a></p>";
            foreach ($row as $field => $value) {
                if ($field != 'IP_ADDRESS') {
                    if ($field == 'RUNNING_SINCE') {
                        $date = strtotime(date("Y-m-d H:i:s")) - strtotime(date_format(date_create_from_format('Y-m-d H:i:s', $value), 'Y-m-d H:i:s'));
                        $jobsExecutedTotal += $row['JOBS_EXECUTED'];
                        $jobsperhour = $row['JOBS_EXECUTED'] / ($date / 3600);
                        $jobsperhourTotal += $jobsperhour;
                        echo "<p><a class='acluster'><b>&bull;&#160;&#160;CURRENT_TIME</b>: </a>" . file_get_contents("http://" . $row["IP_ADDRESS"] . "/sce/time.php") . "</p>\n";
                        //echo "<p><a class='acluster' title=\"View Graph\" target=\"_blank\" href=\"sysgraph.php?metric_name=JOBS_PER_HOUR&ip_address=" . $row["IP_ADDRESS"] . "\"><b>&#160;&bull;&#160;&#160;JOBS/h</b>: </a>" . min_precision($jobsperhour) . "</p>\n";
                        echo "<p><a class='acluster' title=\"View Graph\" target=\"_blank\" href=\"sysgraph.php?metric_name=JOBS_PER_HOUR\"><b>&bull;</b></a>" .
                        "<a class='acluster' title=\"View Graph\" target=\"_blank\" href=\"sysgraph.php?metric_name=JOBS_PER_HOUR&ip_address=" . $row["IP_ADDRESS"] . "\"><b>JOBS/h</b>: </a>" . min_precision($jobsperhour) . "</p>\n";
                    } else if (strpos($field, "MEMORY") !== false || strpos($field, "SPACE") !== false) {
                        if (strpos($field, "FREE_PHYSICAL_MEMORY") !== false) {
                            $memFree += $value;
                        } else if (strpos($field, "TOTAL_PHYSICAL_MEMORY") !== false) {
                            $memTotal += $value;
                        }
                        $value = bytesToSize($value);
                    } else if ($field == "CPU_LOAD") {
                        $ipAddress = split(";", $row["IP_ADDRESS"]);
                        $cpuinfo = objectToArray(json_decode(file_get_contents("http://" . $ipAddress[0] . "/sce/cpuinfo.php")));
                        $cpuTotal += $cpuinfo['cpu MHz'] * $row['NUMBER_OF_PROCESSORS'];
                        $cpuLoadTotal += ($value * $cpuinfo['cpu MHz'] * $row['NUMBER_OF_PROCESSORS']);
                        $value = min_precision($value * 100) . '%';
                    } else if ($field == "CPU_LOAD_JVM") {
                        $value = min_precision($value * 100) . '%';
                    } else if (strpos($field, "NUMBER_OF_PROCESSORS") !== false) {
                        $numCores+= $value;
                    }
                    //echo "<p><b>" . $field . "</b>: " . $value . "</p>\n";
                    //echo "<p><a class='acluster' title=\"View Graph\" target=\"_blank\" href=\"sysgraph.php?metric_name=" . $field . "&ip_address=" . $row["IP_ADDRESS"] . "\"><b>" . $field . "</b>: </a>" . $value . "</p>\n";
                    echo "<p><a class='acluster' title=\"View Graph\" target=\"_blank\" href=\"sysgraph.php?metric_name=" . $field . "\"><b>&bull;</b></a>" .
                    "<a class='acluster' title=\"View Graph\" target=\"_blank\" href=\"sysgraph.php?metric_name=" . $field . "&ip_address=" . $row["IP_ADDRESS"] . "\"><b>" . $field . "</b>: </a>" . $value . "</p>\n";
                } /* else {
                  //IP_ADDRESS COULD CONTAIN MORE THAN ONE ADDRESS (SEMICOLON SEPARATED IPs)
                  $ipAddress = split(";", $value);
                  $cpuinfo = objectToArray(json_decode(file_get_contents("http://" . $ipAddress[0] . "/sce/cpuinfo.php")));
                  $meminfo = objectToArray(json_decode(file_get_contents("http://" . $ipAddress[0] . "/sce/meminfo.php")));
                  $cpuTotal += $cpuinfo['cpu MHz']; // * ($cpuinfo['processor'] + 1));
                  $cpuLoadTotal += ($row['CPU_LOAD'] * $cpuinfo['cpu MHz']); // * ($cpuinfo['processor'] + 1)));
                  $memt = split(" ", $meminfo['MemTotal']);
                  $memf = split(" ", $meminfo['MemFree']);
                  $memTotal += $memt[0];
                  $memFree += $memf[0];
                  $numCores += ($cpuinfo['processor'] + 1);
                  } */
            }
            echo "<p><b>&#160;&bull;&#160;&#160;CPU</b>: </a>" . $cpuinfo['model name'] . "</p>\n";
            echo "</div>\n";
        }

        // calculate the number of jobs failed in the last day
        $jobs_failed_24h = jobs_failed_24h($link);
        $jobs_failed_7day = jobs_failed_7day($link);

        echo "<div class='clusterTotal' id='gradient'>";

        echo "<table>";

        echo "<thead>";
        echo "<tr>";
        echo "<th>CPU</th><th>CPU Load</th><th>Mem Total</th><th>Mem Free</th><th>Cores</th><th>Jobs/h</th><th>Jobs Executed</th><th>Jobs Failed/Success (24 h)</th><th>Jobs Failed/Success (7 days)</th>";
        echo "</tr>";
        echo "</thead>";

        echo "<tbody>";
        echo "<tr>";
        echo "<td>" . min_precision($cpuTotal / 1024) . " GHz</td>";
        echo "<td>" . min_precision($cpuLoadTotal / 1024) . " GHz (" . min_precision($cpuLoadTotal * 100 / $cpuTotal) . "%)</td>";
        echo "<td>" . bytesToSize($memTotal) . "</td>";
        echo "<td>" . bytesToSize($memFree) . "</td>";
        echo "<td>" . $numCores . "</td>";
        echo "<td>" . min_precision($jobsperhourTotal) . "</td>";
        echo "<td>" . $jobsExecutedTotal . "</td>";
        echo "<td>" . $jobs_failed_24h['nfailed'] . " (" . min_precision(($jobs_failed_24h['nfailed'] * 100) / ($jobs_failed_24h['nfailed'] + $jobs_failed_24h['nsuccess'])) . "%)<br>" .
        $jobs_failed_24h['nsuccess'] . " (" . min_precision(($jobs_failed_24h['nsuccess'] * 100) / ($jobs_failed_24h['nfailed'] + $jobs_failed_24h['nsuccess'])) . "%)</td>";
        echo "<td>" . $jobs_failed_7day['nfailed'] . " (" . min_precision(($jobs_failed_7day['nfailed'] * 100) / ($jobs_failed_7day['nfailed'] + $jobs_failed_7day['nsuccess'])) . "%)<br>" .
        $jobs_failed_7day['nsuccess'] . " (" . min_precision(($jobs_failed_7day['nsuccess'] * 100) / ($jobs_failed_7day['nfailed'] + $jobs_failed_7day['nsuccess'])) . "%)</td>";
        echo "</tr>";
        echo "</tbody>";

        echo "</table>";
        echo "</div>";

        echo "</div>";

        echo '<div class="clusterTime">Last updated on: ' . mdate('D d-m-Y H:i:s.u') . ' generated in ' . (round(microtime(true) * 1000) - $milliseconds) . ' ms (refresh time ' . $config['refreshTime'] . ' ms)';
        echo '&emsp;<a style="font-size:10px" class="pointer" title="View Cluster Status" href="cluster.php">Static</a>';
        echo '&emsp;<a style="font-size:10px" class="pointer" title="View Cluster Status" href="reload-cluster.php">Push</a>';
        echo '</div>';

        // close MySQL connection
        mysqli_close($link);
        ?>
    </body>
</html>
