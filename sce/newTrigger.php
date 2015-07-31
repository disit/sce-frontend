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

function getTriggerData($triggerName, $triggerGroup) {
    global $config;
    //DATABASE SETTINGS
    /* $config['host'] = "localhost";
      $config['user'] = "root";
      $config['pass'] = "centos";
      $config['database'] = "quartz"; */
    //CONNECT
    $link = mysqli_connect($config['host'], $config['user'], $config['pass'], $config['database']);

    /* check connection */
    if (mysqli_connect_errno()) {
        printf("Connection failed: %s\n", mysqli_connect_error());
        exit();
    }

    //GET DATA
    $sql = "SELECT QRTZ_TRIGGERS.SCHED_NAME, QRTZ_TRIGGERS.TRIGGER_NAME, QRTZ_TRIGGERS.TRIGGER_GROUP, QRTZ_TRIGGERS.JOB_NAME, QRTZ_TRIGGERS.JOB_GROUP, QRTZ_TRIGGERS.DESCRIPTION, QRTZ_TRIGGERS.NEXT_FIRE_TIME, QRTZ_TRIGGERS.PREV_FIRE_TIME, QRTZ_TRIGGERS.PRIORITY, QRTZ_TRIGGERS.TRIGGER_STATE, QRTZ_TRIGGERS.TRIGGER_TYPE, QRTZ_TRIGGERS.START_TIME, QRTZ_TRIGGERS.END_TIME, QRTZ_TRIGGERS.CALENDAR_NAME, QRTZ_TRIGGERS.MISFIRE_INSTR, QRTZ_TRIGGERS.JOB_DATA, QRTZ_SIMPLE_TRIGGERS.REPEAT_COUNT, QRTZ_SIMPLE_TRIGGERS.REPEAT_INTERVAL, QRTZ_SIMPLE_TRIGGERS.TIMES_TRIGGERED FROM quartz.QRTZ_TRIGGERS LEFT JOIN quartz.QRTZ_SIMPLE_TRIGGERS ON QRTZ_TRIGGERS.TRIGGER_NAME=QRTZ_SIMPLE_TRIGGERS.TRIGGER_NAME AND QRTZ_TRIGGERS.TRIGGER_GROUP=QRTZ_SIMPLE_TRIGGERS.TRIGGER_GROUP WHERE QRTZ_TRIGGERS.TRIGGER_NAME='$triggerName' AND QRTZ_TRIGGERS.TRIGGER_GROUP='$triggerGroup'";
    $result = mysqli_query($link, $sql) or die(mysqli_error());

    //LOOP TABLE ROWS
    while ($row = mysqli_fetch_assoc($result)) {
        foreach ($row as $field => $value) {
            if ($field != 'JOB_DATA') {
                $arr[$field] = $value;
            }
        }
    }

    mysqli_close($link); //close connection

    return $arr;
}

//<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
echo "<html xmlns = \"http://www.w3.org/1999/xhtml\">\n";
echo "<head>\n";
echo "<title>Trigger</title>\n";
echo "<link rel = \"stylesheet\" type = \"text/css\" href = \"css/jquery-ui.css\">\n";
echo "<link rel = \"stylesheet\" type = \"text/css\" href = \"css/post.css\">\n";
echo "<link rel = \"stylesheet\" type = \"text/css\" href = \"css/jquery-ui-timepicker-addon.css\" />\n";
echo "<script type = \"text/javascript\" src = \"javascript/jquery-2.1.0.min.js\"></script>\n";
echo "<script type = \"text/javascript\" src=\"javascript/jquery-ui.js\"></script>\n";
echo "<script type=\"text/javascript\" src=\"javascript/jquery-ui-timepicker-addon.js\"></script>\n";

//datepicker js
echo "<script>\n";
echo "$(function() {\n";
echo "$(\"#datepicker1, #datepicker2\").each(function() {\n";
echo "$(this).datetimepicker({\n";
echo "timeFormat: \"HH:mm:ss\",\n";
echo "dateFormat: \"yy/mm/dd\",\n";
echo "autoclose: true\n";
echo "});\n";
echo "});\n";
echo "});\n";
echo "</script>\n";

//javascript post function
echo "<script>\n";
echo "function submit_form() {\n";
echo "var data = $(\"form\").serialize();";
echo "\n";
echo "$.ajax({\n";
echo "url: \"postTrigger.php\",\n";
echo "type: \"POST\",\n";
echo "async: true,\n";
echo "cache: false,\n";
echo "data: data,\n";
echo "success: function(data){\n";
echo "alert(data)\n";
echo "}\n";
echo "});\n";
echo "}\n";
echo "</script>\n";

echo "<script>\n";
echo "$(document).ready(function() {\n"; //********OPEN document.ready********
echo "\n";
//script to resize text fields when typing
echo "$('.resizeTextField').bind('input propertyChange', function () {\n";
echo "var oneLetterWidth = 7;\n";
echo "var minCharacters = 1;\n";
echo "var len = $(this).val().length;\n";
echo "if (len > minCharacters) {\n";
echo "$(this).width(Math.min(1000, Math.max(140, len * oneLetterWidth)));\n";
echo "} else {\n";
echo "$(this).width(140);\n";
echo "}\n";
echo "});\n";
//resize every input text
echo "$('.resizeTextField').each(function() {\n";
echo "$(this).width(Math.min(1000, Math.max(140, $(this).val().length * 7)));\n";
echo "});\n";
echo "});\n"; //********CLOSE document.ready********
echo "</script>\n";

echo "</head>\n";
echo "<body>\n";
include_once "header.php"; //include header
echo "<div id='postFields'>";
echo "<form>\n";
echo "<h1><b>Trigger Data</b></h1>\n";

if (isset($_GET['triggerName']) && isset($_GET['triggerGroup'])) {
    $jobData = getTriggerData($_GET['triggerName'], $_GET['triggerGroup']);
    //if this trigger does not exist anymore in the scheduler
    if (!isset($jobData)) {
        echo "<p>Trigger [" . $_GET['triggerName'] . "." . $_GET['triggerGroup'] . "] does not exist anymore.</p>\n";
        echo "<a class=\"pointer\" href=\"#\" onclick=\"if(document.referrer) {window.open(document.referrer,'_self');} else {history.go(-1);}return false;\">Back</a>&emsp;\n";
        echo "<a class=\"pointer\" title=\"Home\" href=\"index.php\">Home</a>\n";
        echo "</body>\n";
        echo "</html>\n";
        exit();
    }
}

//<!--startNow: set the time the Trigger should start at to the current moment - the trigger may or may not fire at this time - depending upon the schedule configured for the Trigger-->
//echo "<p><b title=\"Set the time the Trigger should start at to the current moment - the trigger may or may not fire at this time - depending upon the schedule configured for the Trigger\">Start Now: </b><input name=\"startNow\" type=\"checkbox\" value=\"1\" checked=\"checked\"/></p>\n";
//<!--startAt: set the time the Trigger should start at - the trigger may or may not fire at this time - depending upon the schedule configured for the Trigger, number of milliseconds from January 1st 1970-->
echo "<p><b title=\"Set the time the Trigger should start at - the trigger may or may not fire at this time - depending upon the schedule configured for the Trigger\">Start At: </b><input type=\"text\" name=\"startAt\" value=\"" . (isset($jobData['START_TIME']) ? date("Y/m/d H:i:s", $jobData['START_TIME'] / 1000) : "") . "\" id=\"datepicker1\" /></p>\n";

//<!--endAt: set the time at which the Trigger will no longer fire - even if it's schedule has remaining repeats, number of milliseconds from January 1st 1970-->
echo "<p><b title=\"Set the time at which the Trigger will no longer fire - even if it's schedule has remaining repeats\">End At: </b><input type=\"text\" name=\"endAt\" value=\"" . (isset($jobData['END_TIME']) && $jobData['END_TIME'] != 0 ? date("Y/m/d H:i:s", $jobData['END_TIME'] / 1000) : "") . "\" id=\"datepicker2\" /></p>\n";

//<!--forJobDetail: set the identity of the Job which should be fired by the produced Trigger, by extracting the JobKey from the given job, e.g. name.group-->
//<!--forJobKey: set the identity of the Job which should be fired by the produced Trigger, e.g. name.group-->
//<!--forJobNameGroup: set the identity of the Job which should be fired by the produced Trigger - a JobKey will be produced with the given name and group, e.g. name.group-->
echo "<p><input type=\"hidden\" name=\"jobName\" value=\"" . (isset($jobData['JOB_NAME']) ? $jobData['JOB_NAME'] : "") . "\" /></p>\n"; //if the next field is not disabled, it will override this value, otherwise this default value will be posted
echo "<p><b title=\"Set the name of the Job which should be fired by the produced Trigger\">Job Name: </b><input type=\"text\" class=\"resizeTextField\" name=\"jobName\" value=\"" . (isset($jobData['JOB_NAME']) ? $jobData['JOB_NAME'] . "\" disabled=\"true\"" : "\"") . "/></p>\n";
echo "<p><input type=\"hidden\" name=\"jobGroup\" value=\"" . (isset($jobData['JOB_GROUP']) ? $jobData['JOB_GROUP'] : "") . "\" /></p>\n"; //if the next field is not disabled, it will override this value, otherwise this default value will be posted
echo "<p><b title=\"Set the group of the Job which should be fired by the produced Trigger\">Job Group: </b><input type=\"text\" class=\"resizeTextField\" name=\"jobGroup\" value=\"" . (isset($jobData['JOB_GROUP']) ? $jobData['JOB_GROUP'] . "\" disabled=\"true\"" : "\"") . "/></p>\n";

//<!--modifiedByCalendar: set the name of the Calendar that should be applied to this Trigger's schedule-->
echo "<p><b title=\"Set the name of the Calendar that should be applied to this Trigger's schedule\">Calendar Name: </b><input type=\"text\" class=\"resizeTextField\" name=\"modifiedByCalendar\" value=\"" . (isset($jobData['CALENDAR_NAME']) ? $jobData['CALENDAR_NAME'] : "") . "\" /></p>\n";

//<!--withIdentityNameGroup: use a TriggerKey with the given name and group to identify the Trigger, e.g. name.group-->
//<!--withIdentityTriggerKey: use the given TriggerKey to identify the Trigger, e.g. name.group-->
echo "<p><input type=\"hidden\" name=\"triggerName\" value=\"" . (isset($jobData['TRIGGER_NAME']) ? $jobData['TRIGGER_NAME'] : "") . "\" /></p>\n"; //if the next field is not disabled, it will override this value, otherwise this default value will be posted
echo "<p><b title=\"Set the trigger name\">Trigger Name: </b><input type=\"text\" class=\"resizeTextField\" name=\"triggerName\" value=\"" . (isset($jobData['TRIGGER_NAME']) ? $jobData['TRIGGER_NAME'] . "\" disabled=\"true\"" : "") . "\" /></p>\n";
echo "<p><input type=\"hidden\" name=\"triggerGroup\" value=\"" . (isset($jobData['TRIGGER_GROUP']) ? $jobData['TRIGGER_GROUP'] : "") . "\" /></p>\n"; //if the next field is not disabled, it will override this value, otherwise this default value will be posted
echo "<p><b title=\"Set the trigger group\">Trigger Group: </b><input type=\"text\" class=\"resizeTextField\" name=\"triggerGroup\" value=\"" . (isset($jobData['TRIGGER_GROUP']) ? $jobData['TRIGGER_GROUP'] . "\" disabled=\"true\"" : "") . "\" /></p>\n";

//<!--withDescription: set the given (human-meaningful) description of the Trigger-->
echo "<p><b title=\"Set the given (human-meaningful) description of the Trigger\">Trigger Description: </b><input type=\"text\" class=\"resizeTextField\" name=\"triggerDescription\" value=\"" . (isset($jobData['DESCRIPTION']) ? $jobData['DESCRIPTION'] : "") . "\" /></p>\n";

//<!--withPriority: set the Trigger's priority-->
echo "<p><b title=\"Set the Trigger's priority\">Priority: </b><input type=\"text\" name=\"priority\" value=\"" . (isset($jobData['PRIORITY']) ? $jobData['PRIORITY'] : "5") . "\" /></p>\n";

//<!--repeatForever: specify that the trigger will repeat indefinitely-->
//<!--withRepeatCount: specify the number of time the trigger will repeat - total number of firings will be this number + 1-->
echo "<p><b title=\"Specify the number of time the trigger will repeat - total number of firings will be this number + 1, (0 or -1 mean indefinitely)\">Repeat Count: </b><input type=\"text\" name=\"repeatCount\" value=\"" . (isset($jobData['REPEAT_COUNT']) ? $jobData['REPEAT_COUNT'] : "0") . "\" /></p>\n";

//<!--withIntervalInSeconds: specify a repeat interval in seconds - which will then be multiplied by 1000 to produce milliseconds-->
echo "<p><b title=\"Specify a repeat interval in seconds\">Interval (s): </b><input type=\"text\" name=\"intervalInSeconds\" value=\"" . (isset($jobData['REPEAT_INTERVAL']) ? $jobData['REPEAT_INTERVAL'] / 1000 : "60") . "\" /></p>\n";

//withMisfireHandlingInstructionFireNow: if the Trigger misfires, use the SimpleTrigger.MISFIRE_INSTRUCTION_FIRE_NOW instruction
//withMisfireHandlingInstructionIgnoreMisfires: if the Trigger misfires, use the Trigger.MISFIRE_INSTRUCTION_IGNORE_MISFIRE_POLICY instruction
//withMisfireHandlingInstructionNextWithExistingCount: if the Trigger misfires, use the SimpleTrigger.MISFIRE_INSTRUCTION_RESCHEDULE_NEXT_WITH_EXISTING_COUNT instruction
//withMisfireHandlingInstructionNextWithRemainingCount: if the Trigger misfires, use the SimpleTrigger.MISFIRE_INSTRUCTION_RESCHEDULE_NEXT_WITH_REMAINING_COUNT instruction
//withMisfireHandlingInstructionNowWithExistingCount: if the Trigger misfires, use the SimpleTrigger.MISFIRE_INSTRUCTION_RESCHEDULE_NOW_WITH_EXISTING_REPEAT_COUNT instruction
//withMisfireHandlingInstructionNowWithRemainingCount: if the Trigger misfires, use the SimpleTrigger.MISFIRE_INSTRUCTION_RESCHEDULE_NOW_WITH_REMAINING_REPEAT_COUNT instruction
echo "<p><b title=\"If the Trigger misfires, use this misfire instruction\">Misfire Instruction: </b>\n";
echo "<select name=\"misfire\">\n";
echo "<option value=\"IGNORE_MISFIRE_POLICY\"" . (isset($jobData['MISFIRE_INSTR']) && $jobData['MISFIRE_INSTR'] == -1 ? "selected=\"selected\"" : "") . ">IGNORE_MISFIRE_POLICY</option>\n";
echo "<option value=\"DEFAULT\"" . (!isset($jobData['MISFIRE_INSTR']) || (isset($jobData['MISFIRE_INSTR']) && $jobData['MISFIRE_INSTR'] == 0) ? "selected=\"selected\"" : "") . ">DEFAULT</option>\n"; //SMART_POLICY
echo "<option value=\"FIRE_NOW\"" . (!isset($jobData['MISFIRE_INSTR']) && $jobData['MISFIRE_INSTR'] == 1 ? "selected=\"selected\"" : "") . ">FIRE_NOW</option>\n";
echo "<option value=\"RESCHEDULE_NOW_WITH_EXISTING_REPEAT_COUNT\"" . (isset($jobData['MISFIRE_INSTR']) && $jobData['MISFIRE_INSTR'] == 2 ? "selected=\"selected\"" : "") . ">RESCHEDULE_NOW_WITH_EXISTING_REPEAT_COUNT</option>\n";
echo "<option value=\"RESCHEDULE_NOW_WITH_REMAINING_REPEAT_COUNT\"" . (isset($jobData['MISFIRE_INSTR']) && $jobData['MISFIRE_INSTR'] == 3 ? "selected=\"selected\"" : "") . ">RESCHEDULE_NOW_WITH_REMAINING_REPEAT_COUNT</option>\n";
echo "<option value=\"RESCHEDULE_NEXT_WITH_REMAINING_COUNT\"" . (isset($jobData['MISFIRE_INSTR']) && $jobData['MISFIRE_INSTR'] == 4 ? "selected=\"selected\"" : "") . ">RESCHEDULE_NEXT_WITH_REMAINING_COUNT</option>\n";
echo "<option value=\"RESCHEDULE_NEXT_WITH_EXISTING_COUNT\"" . (isset($jobData['MISFIRE_INSTR']) && $jobData['MISFIRE_INSTR'] == 5 ? "selected=\"selected\"" : "") . ">RESCHEDULE_NEXT_WITH_EXISTING_COUNT</option>\n";
echo "</select>\n";
echo "</p>\n";

//action to perform, rescheduleJob = update an existing trigger for a job, scheduleJob = define a new trigger for a job
echo "<input type=\"hidden\" name=\"id\" value=\"" . (isset($_GET['triggerName']) && isset($_GET['triggerGroup']) ? "rescheduleJob" : "scheduleJob") . "\">\n";

//submit
//echo "<p><input type=\"submit\"></p>\n";
echo "<input name=\"confirm\" type=\"button\" value=\"confirm\" onclick=\"submit_form();\" />";
echo "</form>\n";

//echo "<a class=\"pointer\" title=\"Back\" href=\"#\" onclick=\"history.back();\">Back</a>&emsp;\n";
echo "<a class=\"pointer\" href=\"#\" onclick=\"if(document.referrer) {window.open(document.referrer,'_self');} else {history.go(-1);}return false;\">Back</a>&emsp;\n";
echo "<a class=\"pointer\" title=\"Home\" href=\"index.php\">Home</a>\n";
echo "</div>"; //close <div id='postFields'> of newJob
echo "</body>\n";
echo "</html>\n";
?>