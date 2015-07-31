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

function getMisfireInstruction($instruction) {
    $result = "";
    switch ($instruction) {
        case "FIRE_NOW":
            $result = "withMisfireHandlingInstructionFireNow";
            break;
        case "IGNORE_MISFIRE_POLICY":
            $result = "withMisfireHandlingInstructionIgnoreMisfires";
            break;
        case "RESCHEDULE_NEXT_WITH_EXISTING_COUNT":
            $result = "withMisfireHandlingInstructionNextWithExistingCount";
            break;
        case "RESCHEDULE_NEXT_WITH_REMAINING_COUNT":
            $result = "withMisfireHandlingInstructionNextWithRemainingCount";
            break;
        case "RESCHEDULE_NOW_WITH_EXISTING_REPEAT_COUNT":
            $result = "withMisfireHandlingInstructionNowWithExistingCount";
            break;
        case "RESCHEDULE_NOW_WITH_REMAINING_REPEAT_COUNT":
            $result = "withMisfireHandlingInstructionNowWithRemainingCount";
            break;
        default: //SMART_POLICY
            $result = "";
            break;
    }
    return $result;
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

//get the job class from database
function getJobClass($jobName, $jobGroup) {
    global $config;
    //CONNECT
    $link = mysqli_connect($config['host'], $config['user'], $config['pass'], $config['database']);

    /* check connection */
    if (mysqli_connect_errno()) {
        printf("Connection failed: %s\n", mysqli_connect_error());
        exit();
    }

    //GET DATA
    $sql = "SELECT JOB_CLASS_NAME FROM quartz.QRTZ_JOB_DETAILS WHERE JOB_NAME='$jobName' AND JOB_GROUP='$jobGroup' LIMIT 1";
    $result = mysqli_query($link, $sql) or die(mysqli_error());

    //LOOP TABLE ROWS
    while ($row = mysqli_fetch_assoc($result)) {
        foreach ($row as $field => $value) {
            $result = $value;
        }
    }

    mysqli_close($result); //close connection

    $result = explode(".", $result); //split the scheduler name from the retrieved field (e.g., sce.RESTJob)
    return $result[1];
}

//POST TRIGGER DATA
/* $startNow = filter_input(INPUT_POST, 'startNow') == "1" ? "&startNow=" : "";
  $startAt = filter_input(INPUT_POST, 'startAt') ? "&startAt=" . strtotime(filter_input(INPUT_POST, 'startAt')) . "000" : "";
  $endAt = filter_input(INPUT_POST, 'endAt') ? "&endAt=" . strtotime(filter_input(INPUT_POST, 'endAt')) . "000" : "";
  $modifiedByCalendar = filter_input(INPUT_POST, 'modifiedByCalendar') ? "&modifiedByCalendar=" . filter_input(INPUT_POST, 'modifiedByCalendar') : "";
  $jobName = filter_input(INPUT_POST, 'jobName');
  $jobGroup = filter_input(INPUT_POST, 'jobGroup');
  $triggerDescription = filter_input(INPUT_POST, 'triggerDescription');
  $triggerName = filter_input(INPUT_POST, 'triggerName');
  $triggerGroup = filter_input(INPUT_POST, 'triggerGroup');
  $priority = filter_input(INPUT_POST, 'priority') ? "&withPriority=" . filter_input(INPUT_POST, 'priority') : "";
  $repeatCount = filter_input(INPUT_POST, 'repeatCount') == "0" || filter_input(INPUT_POST, 'repeatCount') == "-1" ? "&repeatForever=true" : "&repeatCount=" . filter_input(INPUT_POST, 'repeatCount');
  $intervalInSeconds = filter_input(INPUT_POST, 'intervalInSeconds');
  $misfire = filter_input(INPUT_POST, 'misfire') ? getMisfireInstruction(filter_input(INPUT_POST, 'misfire')) : "";
  //action to perform, rescheduleJob = update an existing trigger for a job, scheduleJob = define a new trigger for a job
  $action = filter_input(INPUT_POST, 'action'); */

filter_input(INPUT_POST, 'startNow') == "1" ? $postData["startNow"] = "" : "";
filter_input(INPUT_POST, 'startAt') ? $postData["startAt"] = strtotime(filter_input(INPUT_POST, 'startAt')) . "000" : "";
filter_input(INPUT_POST, 'endAt') ? $postData["endAt"] = strtotime(filter_input(INPUT_POST, 'endAt')) . "000" : "";
filter_input(INPUT_POST, 'modifiedByCalendar') ? $postData["modifiedByCalendar"] = filter_input(INPUT_POST, 'modifiedByCalendar') : "";
$jobName = filter_input(INPUT_POST, 'jobName') ? filter_input(INPUT_POST, 'jobName') : uuid_create(); //if it's empty then use a UUID (requires PHP PECL UUID extension)
$jobGroup = filter_input(INPUT_POST, 'jobGroup') ? filter_input(INPUT_POST, 'jobGroup') : uuid_create(); //if it's empty then use a UUID (requires PHP PECL UUID extension)
$jobNameGroup[] = $jobName;
$jobNameGroup[] = $jobGroup;
$jobName != null && $jobGroup != null ? $postData["withJobIdentityNameGroup"] = $jobNameGroup : "";
$jobName != null && $jobGroup != null ? $postData["jobClass"] = getJobClass($jobName, $jobGroup) : ""; //set job class (e.g., RESTJob, RESTXMLJob, RESTKBJob, RESTCheckSLAJob, RESTAppMetricJob, ProcessExecutorJob)
filter_input(INPUT_POST, 'triggerDescription') ? $postData["withDescription"] = filter_input(INPUT_POST, 'triggerDescription') : "";
$triggerName = filter_input(INPUT_POST, 'triggerName') ? filter_input(INPUT_POST, 'triggerName') : uuid_create(); //if it's empty then use a UUID (requires PHP PECL UUID extension)
$triggerGroup = filter_input(INPUT_POST, 'triggerGroup') ? filter_input(INPUT_POST, 'triggerGroup') : uuid_create(); //if it's empty then use a UUID (requires PHP PECL UUID extension)
$triggerNameGroup[] = $triggerName;
$triggerNameGroup[] = $triggerGroup;
$triggerName != null && $triggerGroup != null ? $postData["withIdentityNameGroup"] = $triggerNameGroup : "";
filter_input(INPUT_POST, 'priority') ? $postData["withPriority"] = filter_input(INPUT_POST, 'priority') : "";
$repeatCount = filter_input(INPUT_POST, 'repeatCount');
$repeatCount != null ? ($repeatCount == "0" || $repeatCount == "-1" ? $postData["repeatForever"] = "true" : $postData["repeatCount"] = $repeatCount) : "";
filter_input(INPUT_POST, 'intervalInSeconds') ? $postData["withIntervalInSeconds"] = filter_input(INPUT_POST, 'intervalInSeconds') : "";
$misfire = getMisfireInstruction(filter_input(INPUT_POST, 'misfire'));
$misfire != "" ? $postData[$misfire] = "" : "";
//action to perform, rescheduleJob = update an existing trigger for a job, scheduleJob = define a new trigger for a job
filter_input(INPUT_POST, 'id') ? $postData["id"] = filter_input(INPUT_POST, 'id') : "";

global $config;
//CALL URL (post json data)
//$result = file_get_contents("http://" . $config["tomcat"] . ":8080/SmartCloudEngine/?id=" . $action . $startNow . $startAt . $endAt . $modifiedByCalendar . $priority . $misfire . "&withDescription=" . $triggerDescription . "&withIdentityNameGroup=" . $triggerName . "." . $triggerGroup . $repeatCount . "&withIntervalInSeconds=" . $intervalInSeconds . "&withJobIdentityNameGroup=" . $jobName . "." . $jobGroup);
//$result = json_decode($result, true);
//json data
$jsonData["json"] = json_encode($postData);
$result = json_decode(postData($jsonData, "http://" . $config["tomcat"] . ":8080/SmartCloudEngine/index.jsp"), true);

//LOG
//$fp = fopen("/var/www/html/sce/log.txt", "at");
//fwrite($fp, var_export($jsonData, true));
//fwrite($fp, var_export($_POST, true));
//fwrite($fp, "http://" . $config["tomcat"] . ":8080/SmartCloudEngine/?id=" . $action . $startNow . $startAt . $endAt . $modifiedByCalendar . $priority . $misfire . "&withDescription=" . $triggerDescription . "&withIdentityNameGroup=" . $triggerName . "." . $triggerGroup . $repeatCount . "&withIntervalInSeconds=" . $intervalInSeconds . "&withJobIdentityNameGroup=" . $jobName . "." . $jobGroup);
//fclose($fp);

echo $result[1];
?>