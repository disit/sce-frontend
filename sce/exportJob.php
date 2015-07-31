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

// export the job to file
function exportJob($jobName, $jobGroup, $json) {
    if ($json) {
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $jobName . '_' . $jobGroup . '.json"');
        echo $json;
    }
}

//TRIGGER POST VALUES
filter_input(INPUT_GET, 'startNow') == "1" ? ($postData["startNow"] = "") : "";
filter_input(INPUT_GET, 'startAt') ? $postData["startAt"] = strtotime(filter_input(INPUT_GET, 'startAt')) . "000" : "";
filter_input(INPUT_GET, 'endAt') ? $postData["endAt"] = strtotime(filter_input(INPUT_GET, 'endAt')) . "000" : "";
filter_input(INPUT_GET, 'modifiedByCalendar') ? $postData["modifiedByCalendar"] = filter_input(INPUT_GET, 'modifiedByCalendar') : "";
filter_input(INPUT_GET, 'triggerDescription') ? $postData["withDescription"] = filter_input(INPUT_GET, 'triggerDescription') : "";
$triggerName = filter_input(INPUT_GET, 'triggerName') ? filter_input(INPUT_GET, 'triggerName') : uuid_create(); //if it's empty then use a UUID (requires PHP PECL UUID extension)
$triggerGroup = filter_input(INPUT_GET, 'triggerGroup') ? filter_input(INPUT_GET, 'triggerGroup') : uuid_create(); //if it's empty then use a UUID (requires PHP PECL UUID extension)
$triggerNameGroup[] = $triggerName;
$triggerNameGroup[] = $triggerGroup;
$triggerName != null && $triggerGroup != null ? $postData["withIdentityNameGroup"] = $triggerNameGroup : "";
filter_input(INPUT_GET, 'priority') ? $postData["withPriority"] = filter_input(INPUT_GET, 'priority') : "";
$repeatCount = filter_input(INPUT_GET, 'repeatCount');
$repeatCount != null ? ($repeatCount == "0" || $repeatCount == "-1" ? $postData["repeatForever"] = "true" : $postData["repeatCount"] = $repeatCount) : "";
filter_input(INPUT_GET, 'intervalInSeconds') ? $postData["withIntervalInSeconds"] = filter_input(INPUT_GET, 'intervalInSeconds') : "";
$misfire = getMisfireInstruction(filter_input(INPUT_GET, 'misfire'));
$misfire != "" ? $postData[$misfire] = $misfire : "";

//JOB POST VALUES
filter_input(INPUT_GET, 'storeDurably') == "1" ? $postData["storeDurably"] = "true" : $postData["storeDurably"] = "false";
filter_input(INPUT_GET, 'requestRecovery') == "1" ? $postData["requestRecovery"] = "true" : $postData["requestRecovery"] = "false";
$jobName = filter_input(INPUT_GET, 'jobName') ? filter_input(INPUT_GET, 'jobName') : uuid_create(); //if it's empty then use a UUID (requires PHP PECL UUID extension)
$jobGroup = filter_input(INPUT_GET, 'jobGroup') ? filter_input(INPUT_GET, 'jobGroup') : uuid_create(); //if it's empty then use a UUID (requires PHP PECL UUID extension)
$jobNameGroup[] = $jobName;
$jobNameGroup[] = $jobGroup;
$jobName != null && $jobGroup != null ? $postData["withJobIdentityNameGroup"] = $jobNameGroup : "";
filter_input(INPUT_GET, 'jobDescription') ? $postData["withJobDescription"] = filter_input(INPUT_GET, 'jobDescription') : "";
//action to perform, updateJob = update an existing job, scheduleJob = define a new job with a trigger
filter_input(INPUT_GET, 'id') ? $postData["id"] = filter_input(INPUT_GET, 'id') : "";
//job class (RESTJob, RESTXMLJob, RESTKBJob, RESTCheckSLAJob, RESTAppMetricJob, ProcessExecutorJob, DumbJob)
filter_input(INPUT_GET, 'jobClass') ? $postData["jobClass"] = filter_input(INPUT_GET, 'jobClass') : "";

//BUILD JOB DATA MAP
filter_input(INPUT_GET, 'resturl') ? $jobDataMap["#url"] = filter_input(INPUT_GET, 'resturl') : "";
filter_input(INPUT_GET, 'notificationEmail') ? $jobDataMap["#notificationEmail"] = filter_input(INPUT_GET, 'notificationEmail') : "";
filter_input(INPUT_GET, 'isNonConcurrent') == "1" ? $jobDataMap["#isNonConcurrent"] = "true" : $jobDataMap["#isNonConcurrent"] = "false";
//dynamic job data map values
$jobDataMapPostArray = $_GET["jobDataMap"];
if (isset($jobDataMapPostArray)) {
    foreach ($jobDataMapPostArray as $key => $value) {
        if ($key % 2 == 0) {
            $keytemp = $value;
        } else {
            if ($keytemp != "") {
                $jobDataMap[$keytemp] = $value;
            }
        }
    }
}
//next jobs data map
$nextJobsPostArray = $_GET["nextJobs"];
if (isset($nextJobsPostArray)) {
    $ao = new ArrayObject($nextJobsPostArray);
    $it = $ao->getIterator();
    while ($it->valid()) {
        $tmp["operator"] = $it->current();
        $it->next();
        $tmp["result"] = $it->current();
        $it->next();
        $tmp["jobName"] = $it->current();
        $it->next();
        $tmp["jobGroup"] = $it->current();
        $it->next();
        if ($tmp["operator"] != "" && $tmp["result"] != "" && $tmp["jobName"] != "" && $tmp["jobGroup"] != "") {
            $nextJobsArr[] = $tmp;
        }
    }
    $jobDataMap["#nextJobs"] = json_encode($nextJobsArr); //encode as a json array (decode is made in newJob.php when reading it)
}
//process parameters map [0-> (key->value), 1-> (key->value)...] key = parameter label, not passed when invoking the process in the Java scheduler, value = parameter
filter_input(INPUT_GET, 'processPath') ? $processParametersArr[] = array("processPath" => filter_input(INPUT_GET, 'processPath')) : "";
$processParametersPostArray = $_GET["processParameters"];
for ($i = 0; $i < count($processParametersPostArray); $i++) {
    if ($i % 2 == 0) {
        $processParametersArr[] = array($processParametersPostArray[$i] => $processParametersPostArray[$i + 1]);
    }
}
//if processPath is defined, then set the process parameters array
filter_input(INPUT_GET, 'processPath') ? $jobDataMap["#processParameters"] = json_encode($processParametersArr) : ""; //encode as a json associative array (decode is made in newJob.php when reading it)
//job constraints map
$jobConstraintsPostArray = $_GET["jobConstraints"];
if (isset($jobConstraintsPostArray)) {
    $ao = new ArrayObject($jobConstraintsPostArray);
    $it = $ao->getIterator();
    while ($it->valid()) {
        $tmp["systemParameterName"] = $it->current();
        $it->next();
        $tmp["operator"] = $it->current();
        $it->next();
        $tmp["value"] = $it->current();
        $it->next();
        if ($tmp["systemParameterName"] != "" && $tmp["operator"] != "" && $tmp["value"] != "") {
            $jobConstraintsArr[] = $tmp;
        }
    }
    $jobDataMap["#jobConstraints"] = json_encode($jobConstraintsArr); //encode as a json array (decode is made in newJob.php when reading it)
}

//if elastic is defined, then set the elastic constraints array
isset($_GET['elastic']) ? $jobDataMap["#elasticJobConstraints"] = json_encode(array_values($_GET['elastic']), JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK | JSON_FORCE_OBJECT) : ""; //encode as a json associative array (decode is made in newJob.php when reading it)

if (isset($jobDataMap)) {
    $postData["jobDataMap"] = $jobDataMap;
}

global $config;
//CALL URL (post json data)
//json data
$jsonData["json"] = json_encode($postData);
exportJob($jobName, $jobGroup, $jsonData["json"]);

//LOG
//$fp = fopen("/var/www/html/sce/log.txt", "at");
//fwrite($fp, $jobName. " " .  $jobGroup);
//fclose($fp);
//echo $result[1];
?>
