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
include_once "settings.php";

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

//query the db to retrieve the data (jobs and related triggers)
function getData($jobName, $jobGroup) {
    global $config;
    //CONNECT
    $link = mysqli_connect($config['host'], $config['user'], $config['pass'], $config['database']);

    /* check connection */
    if (mysqli_connect_errno()) {
        printf("Connection failed: %s\n", mysqli_connect_error());
        exit();
    }

    //rows array
    $rowsArray = array();
    //next jobs array
    $nextJobsArray = array();

    //GET DATA
    $sqlJobs = "SELECT * FROM quartz.QRTZ_JOB_DETAILS WHERE JOB_NAME='$jobName' AND JOB_GROUP='$jobGroup'";
    $resultJob = mysqli_query($link, $sqlJobs) or die(mysqli_error());

    $sqlTriggers = "SELECT * FROM quartz.QRTZ_TRIGGERS WHERE JOB_NAME='$jobName' AND JOB_GROUP='$jobGroup'";
    $resultTrigger = mysqli_query($link, $sqlTriggers) or die(mysqli_error());

    //LOOP TABLE ROWS
    while ($jobsRow = mysqli_fetch_assoc($resultJob)) {
        foreach ($jobsRow as $field => $value) {
            $jobRowsArray[$field] = $value;
        }
        $jobRowsArray['type'] = 'job';
        $rowsArray[] = $jobRowsArray;

        // store next jobs as key values to avoid duplicates
        $jobDataMapArray = parse_properties($jobsRow['JOB_DATA']);
        $nextJobs = objectToArray(json_decode($jobDataMapArray['#nextJobs']));
        for ($i = 0; $i < count($nextJobs); $i++) {
            if (!isset($nextJobsArray[$nextJobs[$i]['jobName']][$nextJobs[$i]['jobName']])) {
                $nextJobsArray[$nextJobs[$i]['jobName']][$nextJobs[$i]['jobName']] = "";
                array_merge($rowsArray, getData($nextJobs[$i]['jobName'], $nextJobs[$i]['jobName']));
            }
        }
    }

    while ($triggersRow = mysqli_fetch_assoc($resultTrigger)) {
        foreach ($triggersRow as $field => $value) {
            $triggerRowsArray[$field] = $value;
        }
        $triggerRowsArray['type'] = 'trigger';
        $rowsArray[] = $triggerRowsArray;
    }

    mysqli_close($link); //close connection

    return json_encode($rowsArray);
}

// export data to file
function exportJob($jobName, $jobGroup, $json) {
    if ($json) {
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $jobName . '_' . $jobGroup . '.json"');
        echo $json;
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

$json = getData($_GET['jobName'], $_GET['jobGroup']);
exportJob($_GET['jobName'], $_GET['jobGroup'], $json);

//LOG
//$fp = fopen("/var/www/html/sce/log.txt", "at");
//fwrite($fp, var_export($_POST['elastic'], true));
//fclose($fp);
?>