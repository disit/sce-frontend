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

//query the db to retrieve the job data
function getJobData($jobName, $jobGroup) {
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
    $sql = "SELECT * FROM quartz.QRTZ_JOB_DETAILS WHERE JOB_NAME='$jobName' AND JOB_GROUP='$jobGroup'";
    $result = mysqli_query($link, $sql) or die(mysqli_error());

    //LOOP TABLE ROWS
    while ($row = mysqli_fetch_assoc($result)) {
        foreach ($row as $field => $value) {
            $arr[$field] = $value;
        }
    }

    mysqli_close($link); //close connection

    return $arr;
}

// get the list of triggers associated to a job
function getJobTriggers($jobName, $jobGroup) {
    //$json = file_get_contents("http://localhost:8080/SmartCloudEngine/?id=getJobTriggers&jobName=$jobName&jobGroup=$jobGroup");
    $postData["id"] = "getJobTriggers";
    $postData["jobName"] = $jobName;
    $postData["jobGroup"] = $jobGroup;
    $jsonData["json"] = json_encode($postData);
    $arr = json_decode(postData($jsonData, "http://localhost:8080/SmartCloudEngine/index.jsp"), true);
    return $arr;
}

// get the email of the eventually associated job listener of this job
/* function getNotificationEmail($jobName, $jobGroup) {
  //$json = file_get_contents("http://localhost:8080/SmartCloudEngine/?id=getNotificationEmail&jobName=$jobName&jobGroup=$jobGroup");
  $postData["id"] = "getNotificationEmail";
  $postData["jobName"] = $jobName;
  $postData["jobGroup"] = $jobGroup;
  $jsonData["json"] = json_encode($postData);
  $arr = json_decode(postData($jsonData, "http://localhost:8080/SmartCloudEngine/index.jsp"), true);
  return $arr[1];
  } */

// get the job data map, unused becase the data map is fetched directly by querying the database and reading the JOB_DATA field, with the getJobData method
/* function getJobDataMap($jobName, $jobGroup) {
  //$json = file_get_contents("http://localhost:8080/SmartCloudEngine/?id=getJobDataMap&jobName=$jobName&jobGroup=$jobGroup");
  $postData["id"] = "getJobDataMap";
  $postData["jobName"] = $jobName;
  $postData["jobGroup"] = $jobGroup;
  $jsonData["json"] = json_encode($postData);
  $arr = json_decode(postData($jsonData, "http://localhost:8080/SmartCloudEngine/index.jsp"), true);
  return $arr;
  } */

//if an existing job is being updated
if (isset($_GET['jobName']) && isset($_GET['jobGroup'])) {
    //get the job data from db
    $jobData = getJobData($_GET['jobName'], $_GET['jobGroup']);

    //if this job does not exist anymore in the scheduler (i.e., deleted or non durable job)
    if (!isset($jobData)) {
        echo "<html xmlns = \"http://www.w3.org/1999/xhtml\">\n";
        echo "<head>\n";
        echo "<title>Job</title>\n";
        echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"css/jquery-ui.css\">\n";
        echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"css/typography.css\">\n";
        echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"css/jquery-ui-timepicker-addon.css\" />\n";
        echo "<script type=\"text/javascript\" src=\"javascript/jquery-2.1.0.min.js\" type=\"text/javascript\"></script>\n";
        echo "<script type=\"text/javascript\" src=\"javascript/jquery-ui.js\"></script>\n";
        echo "<script type=\"text/javascript\" src=\"javascript/jquery-ui-timepicker-addon.js\"></script>\n";
        echo "</head>\n";
        echo "<body>\n";
        include_once "header.php"; //include header
        echo "<div id='resultsTable'><table>\n<tr>";
        echo "<h1><b>Job Data</b></h1>\n";
        echo "<p>Job [" . $_GET['jobName'] . "." . $_GET['jobGroup'] . "] does not exist anymore.</p><br><br>\n";
        echo "<a class=\"pointer\" href=\"#\" onclick=\"if(document.referrer) {window.open(document.referrer,'_self');} else {history.go(-1);}return false;\">Back</a>&emsp;\n";
        echo "<a class=\"pointer\" title=\"Home\" href=\"index.php\">Home</a>\n";
        echo "</table></div>\n"; //close <div id='resultsTable'>
        echo "</body>\n";
        echo "</html>\n";
        exit();
    }

    //get the job data map
    //$jobDataMap = getJobDataMap($_GET['jobName'], $_GET['jobGroup']);
    $jobDataMap = parse_properties($jobData['JOB_DATA']);

    //get the email eventually associated to a job listener of this job
    //$notificationEmail = getNotificationEmail($_GET['jobName'], $_GET['jobGroup']);
    if (isset($jobDataMap["#notificationEmail"])) {
        $notificationEmail = $jobDataMap["#notificationEmail"];
    }

    //get the list of triggers associated to a job
    $jobTriggers = getJobTriggers($_GET['jobName'], $_GET['jobGroup']);
}

//get the job select
function getJobSelect($selected) {
    $select = "<select name=\"nextJobs[]\">";
    $select .= "<option value=\"==\" " . (isset($selected) && $selected == "==" ? " selected=\"selected\"" : "") . ">==</option>";
    $select .= "<option value=\"!=\" " . (isset($selected) && $selected == "!=" ? " selected=\"selected\"" : "") . ">!=</option>";
    $select .= "<option value=\"<\" " . (isset($selected) && $selected == "<" ? " selected=\"selected\"" : "") . "><</option>";
    $select .= "<option value=\">\" " . (isset($selected) && $selected == ">" ? " selected=\"selected\"" : "") . ">></option>";
    $select .= "<option value=\"<=\" " . (isset($selected) && $selected == "<=" ? " selected=\"selected\"" : "") . "><=</option>";
    $select .= "<option value=\">=\" " . (isset($selected) && $selected == ">=" ? " selected=\"selected\"" : "") . ">>=</option>";
    $select .= "</select>";
    return $select;
}

//get the job constraints operator select
function getJobConstraintsOperatorSelect($selected) {
    $select = "<select name=\"jobConstraints[]\">";
    $select .= "<option value=\"==\" " . (isset($selected) && $selected == "==" ? " selected=\"selected\"" : "") . ">==</option>";
    $select .= "<option value=\"!=\" " . (isset($selected) && $selected == "!=" ? " selected=\"selected\"" : "") . ">!=</option>";
    $select .= "<option value=\"<\" " . (isset($selected) && $selected == "<" ? " selected=\"selected\"" : "") . "><</option>";
    $select .= "<option value=\">\" " . (isset($selected) && $selected == ">" ? " selected=\"selected\"" : "") . ">></option>";
    $select .= "<option value=\"<=\" " . (isset($selected) && $selected == "<=" ? " selected=\"selected\"" : "") . "><=</option>";
    $select .= "<option value=\">=\" " . (isset($selected) && $selected == ">=" ? " selected=\"selected\"" : "") . ">>=</option>";
    $select .= "</select>";
    return $select;
}

//get the job constraints parameter select
function getJobConstraintsParameterSelect($selected) {
    $select = "<select name=\"jobConstraints[]\">";
    $select .= "<option value=\"osArch\" " . (isset($selected) && $selected == "osArch" ? " selected=\"selected\"" : "") . ">OS Architecture</option>";
    $select .= "<option value=\"availableProcessors\" " . (isset($selected) && $selected == "availableProcessors" ? " selected=\"selected\"" : "") . ">Available Processors</option>";
    $select .= "<option value=\"osName\" " . (isset($selected) && $selected == "osName" ? " selected=\"selected\"" : "") . ">OS Name</option>";
    $select .= "<option value=\"systemLoadAverage\" " . (isset($selected) && $selected == "systemLoadAverage" ? " selected=\"selected\"" : "") . ">System Load Average</option>";
    $select .= "<option value=\"osVersion\" " . (isset($selected) && $selected == "osVersion" ? " selected=\"selected\"" : "") . ">OS Version</option>";
    $select .= "<option value=\"committedVirtualMemorySize\" " . (isset($selected) && $selected == "committedVirtualMemorySize" ? " selected=\"selected\"" : "") . ">Committed Virtual Memory Size</option>";
    $select .= "<option value=\"freePhysicalMemorySize\" " . (isset($selected) && $selected == "freePhysicalMemorySize" ? " selected=\"selected\"" : "") . ">Free Physical Memory Size</option>";
    $select .= "<option value=\"freeSwapSpaceSize\" " . (isset($selected) && $selected == "freeSwapSpaceSize" ? " selected=\"selected\"" : "") . ">Free Swap Space Size</option>";
    $select .= "<option value=\"processCpuLoad\" " . (isset($selected) && $selected == "processCpuLoad" ? " selected=\"selected\"" : "") . ">Process Cpu Load</option>";
    $select .= "<option value=\"processCpuTime\" " . (isset($selected) && $selected == "processCpuTime" ? " selected=\"selected\"" : "") . ">Process Cpu Time</option>";
    $select .= "<option value=\"systemCpuLoad\" " . (isset($selected) && $selected == "systemCpuLoad" ? " selected=\"selected\"" : "") . ">System Cpu Load</option>";
    $select .= "<option value=\"totalPhysicalMemorySize\" " . (isset($selected) && $selected == "totalPhysicalMemorySize" ? " selected=\"selected\"" : "") . ">Total Physical Memory Size</option>";
    $select .= "<option value=\"totalSwapSpaceSize\" " . (isset($selected) && $selected == "totalSwapSpaceSize" ? " selected=\"selected\"" : "") . ">Total Swap Space Size</option>";
    $select .= "<option value=\"ipAddress\" " . (isset($selected) && $selected == "ipAddress" ? " selected=\"selected\"" : "") . ">IP Address</option>";
    $select .= "</select>";
    return $select;
}

/* * ****** ELASTIC JOB SELECTS ******* */

//get the elastic job metric select
function getElasticMetricSelect($selected, $row, $column) {
    global $config;
    if (isset($row) && isset($column)) {
        $select = "<select name=\"elastic[" . $row . "][" . $column . "][metric]\">";
    } else {
        $select = "<select name=\"[metric]\">";
    }
    //CONNECT
    $link = mysqli_connect($config['host'], $config['user'], $config['pass'], $config['database']);

    /* check connection */
    if (mysqli_connect_errno()) {
        printf("Connection failed: %s\n", mysqli_connect_error());
        exit();
    }

    //GET DATA
    $sql = "SELECT DISTINCT(metric_name) FROM quartz.QRTZ_SPARQL";
    $result = mysqli_query($link, $sql) or die(mysqli_error());

    //LOOP TABLE ROWS
    while ($row = mysqli_fetch_assoc($result)) {
        $select .= "<option value=\"" . $row['metric_name'] . "\" " . (isset($selected) && $selected == "==" ? " selected=\"selected\"" : "") . ">" . $row['metric_name'] . "</option>";
    }

    mysqli_close($link); //close connection

    $select .= "</select>";

    return $select;
}

//get the elastic job business configuration select
function getElasticBcSelect($selected, $row, $column) {
    global $config;
    if (isset($row) && isset($column)) {
        $select = "<select name=\"elastic[" . $row . "][" . $column . "][bcconfiguration]\">";
    } else {
        $select = "<select name=\"[bcconfiguration]\">";
    }
    //CONNECT
    $link = mysqli_connect($config['host'], $config['user'], $config['pass'], $config['database']);

    /* check connection */
    if (mysqli_connect_errno()) {
        printf("Connection failed: %s\n", mysqli_connect_error());
        exit();
    }

    //GET DATA
    $sql = "SELECT DISTINCT(business_configuration) FROM quartz.QRTZ_SPARQL";
    $result = mysqli_query($link, $sql) or die(mysqli_error());

    //LOOP TABLE ROWS
    while ($row = mysqli_fetch_assoc($result)) {
        $select .= "<option value=\"" . $row['business_configuration'] . "\" " . (isset($selected) && $selected == "==" ? " selected=\"selected\"" : "") . ">" . $row['business_configuration'] . "</option>";
    }

    mysqli_close($link); //close connection

    $select .= "</select>";

    return $select;
}

//get the elastic job sla select
function getElasticSlaSelect($selected, $row, $column) {
    global $config;
    if (isset($row) && isset($column)) {
        $select = "<select name=\"elastic[" . $row . "][" . $column . "][slaconfiguration]\">";
    } else {
        $select = "<select name=\"[slaconfiguration]\">";
    }
    //CONNECT
    $link = mysqli_connect($config['host'], $config['user'], $config['pass'], $config['database']);

    /* check connection */
    if (mysqli_connect_errno()) {
        printf("Connection failed: %s\n", mysqli_connect_error());
        exit();
    }

    //GET DATA
    $sql = "SELECT DISTINCT(sla) FROM quartz.QRTZ_SPARQL";
    $result = mysqli_query($link, $sql) or die(mysqli_error());

    //LOOP TABLE ROWS
    while ($row = mysqli_fetch_assoc($result)) {
        $select .= "<option value=\"" . $row['sla'] . "\" " . (isset($selected) && $selected == "==" ? " selected=\"selected\"" : "") . ">" . $row['sla'] . "</option>";
    }

    mysqli_close($link); //close connection

    $select .= "</select>";

    return $select;
}

//get the elastic job virtual machine select
function getElasticVmSelect($selected, $row, $column) {
    global $config;
    if (isset($row) && isset($column)) {
        $select = "<select name=\"elastic[" . $row . "][" . $column . "][vmconfiguration]\">";
    } else {
        $select = "<select name=\"[vmconfiguration]\">";
    }
    //CONNECT
    $link = mysqli_connect($config['host'], $config['user'], $config['pass'], $config['database']);

    /* check connection */
    if (mysqli_connect_errno()) {
        printf("Connection failed: %s\n", mysqli_connect_error());
        exit();
    }

    //GET DATA
    $sql = "SELECT DISTINCT(virtual_machine_name) FROM quartz.QRTZ_SPARQL";
    $result = mysqli_query($link, $sql) or die(mysqli_error());

    //LOOP TABLE ROWS
    while ($row = mysqli_fetch_assoc($result)) {
        $select .= "<option value=\"" . $row['virtual_machine_name'] . "\" " . (isset($selected) && $selected == "==" ? " selected=\"selected\"" : "") . ">" . $row['virtual_machine_name'] . "</option>";
    }

    mysqli_close($link); //close connection

    $select .= "</select>";

    return $select;
}

/* * ****** END ELASTIC JOB SELECTS ******* */

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

//<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
echo "<html xmlns = \"http://www.w3.org/1999/xhtml\">\n";
echo "<head>\n";
echo "<title>Job</title>\n";
echo "<link rel = \"stylesheet\" type = \"text/css\" href = \"css/jquery-ui.css\">\n";
echo "<link rel = \"stylesheet\" type = \"text/css\" href = \"css/post.css\">\n";
echo "<link rel = \"stylesheet\" type = \"text/css\" href = \"css/jquery-ui-timepicker-addon.css\" />\n";
echo "<script src = \"javascript/jquery-2.1.0.min.js\" type = \"text/javascript\"></script>\n";
echo "<script src=\"javascript/jquery-ui.js\"></script>\n";
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

//changemenu js (dumb, REST, RESTXML, ProcessExecutor)
echo "<script>\n";
echo "function changeMenu(selection)\n";
echo "{";
echo "var f = selection.form;\n";
echo "var opt = selection.options[selection.selectedIndex].value;\n";
echo "if (opt == \"RESTJob\" || opt == \"RESTXMLJob\" || opt == \"RESTKBJob\" || opt == \"RESTCheckSLAJob\" || opt == \"RESTAppMetricJob\" || opt == \"ElasticJob\")\n";
echo "{\n";
echo "document.getElementById('resturl').disabled = false;\n";
echo "document.getElementById('processPath').disabled = true;\n";
echo "}\n";
echo "else if (opt == \"ProcessExecutorJob\")\n";
echo "{\n";
echo "document.getElementById('resturl').disabled = true;\n";
echo "document.getElementById('processPath').disabled = false;\n";
echo "}\n";
echo "else\n";
echo "{\n";
echo "document.getElementById('resturl').disabled = true;\n";
echo "document.getElementById('processPath').disabled = true;\n";
echo "document.getElementById('resturl').value = \"\";\n";
echo "document.getElementById('processPath').value = \"\";\n";
echo "}\n";
echo "return true;\n";
echo "}\n";
echo "</script>\n";

//javascript post function
echo "<script>\n";
echo "function submit_form() {\n";
echo "var data = $(\"form\").serialize();";
echo "\n";
echo "$.ajax({\n";
echo "url: \"postJob.php\",\n";
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

//javascript export job function
echo "<script>\n";
echo "function exportJob() {\n";
echo "var data = $(\"form\").serialize();";
echo "\n";
echo "window.location = 'exportJob.php?'+data;\n";
echo "}\n";
echo "</script>\n";

//javascript function to uncheck [isNonConcurrent, requestRecovery] checkboxes, depending on the value of storeDurably
echo "<script>\n";
echo "function toggleCheckbox(id) {\n";
//if storeDurably is unchecked, then uncheck [isNonConcurrent, requestRecovery] checkboxes
echo "if(id=='storeDurably' && document.getElementById(\"storeDurably\").checked==false) {\n";
echo "$(\"#isNonConcurrent\").prop(\"checked\", false);\n";
echo "$(\"#requestRecovery\").prop(\"checked\", false);\n";
echo "}\n";
//if isNonConcurrent or requestRecovery is checked, then check storeDurably checkbox
echo "else if((id=='isNonConcurrent' || id=='requestRecovery') && (document.getElementById(\"isNonConcurrent\").checked==true || document.getElementById(\"requestRecovery\").checked==true)) {\n";
echo "$(\"#storeDurably\").prop(\"checked\", true);\n";
echo "}\n";
echo "}\n";
echo "</script>\n";
?>

<!-- elastic job constraints js -->
<script type="text/javascript">
    function replace(select, id) {
        var templatediv = $("div.selectTemplate#" + id + "configuration > select").clone(true);
        var name = select.attr("name");
        var array = getIndexes(name);
        templatediv.attr("name", "elastic[" + parseInt(array[0]) + "][" + parseInt(array[1]) + "][" + id + "configuration]");
        select.next().replaceWith(templatediv);
    }
    function getIndexes(name) {
        return name.substring(name.indexOf("[") + 1, name.length - 1).split("][");
    }
    function getMaxRow() {
        return $('div.elasticjob > select[name*="cfg"]').length;
    }
    // add item to the elastic job constraints, used when building the form with #jobDataMap['elasticJobConstraints']
    function add() {
        //$(".elasticJob > p:nth-child(1)").clone(true).insertBefore(".end");
        if ($("div.elasticJob > div").length == 0) {
            var div = $("div.conditionTemplateMatch > div").clone(true);
            div.attr("id", 0);
            div.children('select').each(function () {
                this.name = "elastic[0][0]" + this.name;
            });
            div.children('input').each(function () {
                this.name = "elastic[0][0]" + this.name;
            });

            $("div.elasticJob").append(div);
        }
        return false;
    }

    //populate the elastic constraints
    function populate(name, value) {
        if (name.indexOf('threshold') != -1 || name.indexOf('time') != -1 || name.indexOf('callurl') != -1 ||
                name.indexOf('relation') != -1 || name.indexOf('timeselect') != -1 ||
                name.indexOf('configuration') != -1 || name.indexOf('metric') != -1 || name.indexOf('match') != -1) {
            $("[name='" + name + "']").val(value);
        } else if (name.indexOf("cfg") != -1) {
            $("[name='" + name + "']").val(value);//.change();
            var templatediv = $("div.selectTemplate#" + value + "configuration > select").clone(true);
            var array = getIndexes(name);
            templatediv.attr("name", "elastic[" + parseInt(array[0]) + "][" + parseInt(array[1]) + "][" + value + "configuration]");
            $("[name='" + name + "']").parent().find($('select[name*="configuration"]')).replaceWith(templatediv);
        }
    }

    function addCondition(name) {
        var indexes = getIndexes(name);
        for (column = indexes[1]; column >= 0; column--) {
            for (row = indexes[0] - 1; row >= 0; row--) {
                var obj = $('select[name*="[' + row + '][' + column + '][timeselect]"]');
                if (obj.length > 0) {
                    if (column == indexes[1]) {
                        var div = $("div.conditionTemplate > div").clone(true);
                    }
                    else if (column < indexes[1]) {
                        var div = $("div.conditionTemplateMatch > div").clone(true);
                    }
                    div.children('select').each(function () {
                        this.name = "elastic[" + parseInt(indexes[0]) + "][" + parseInt(indexes[1]) + "]" + this.name;
                    });
                    div.children('input').each(function () {
                        this.name = "elastic[" + parseInt(indexes[0]) + "][" + parseInt(indexes[1]) + "]" + this.name;
                    });

                    if (column == indexes[1]) {
                        obj.parent().parent().append(div);
                        div.insertAfter(obj.parent());
                    }
                    else if (column < indexes[1]) {
                        //obj.parent().append(div);
                        div.insertAfter(obj.next().next().next());
                    }
                    return false;
                }
            }
        }
        add();
        return false;
    }
    // update index
    function updateIndex(n) {
        var i = 0;
        $(n).each(function (index, obj) {
            var name = this.name;
            var indexes = getIndexes(name);
            var fieldName = name.substring(name.lastIndexOf("][") + 1);
            var updatedName = "elastic[" + i + "][" + parseInt(indexes[1]) + "]" + fieldName;
            i++;
            this.name = updatedName;
        });
    }
    // update index
    function updateIndexes1() {
        var i = 0;
        $('div.jobcondition > [name*="][metric]"]').each(function (index, obj) {
            var name = this.name;
            var indexes = getIndexes(name);
            //var fieldName = name.substring(name.lastIndexOf("][") + 1);
            //var updatedName = "elastic[" + i + "][" + parseInt(indexes[1]) + "]" + fieldName;
            //this.name = updatedName;
            $('[name="elastic[' + parseInt(indexes[0]) + '][' + parseInt(indexes[1]) + '][match]"]').attr("name", "elastic[" + i + "][" + parseInt(indexes[1]) + "][match]");
            $('[name="elastic[' + parseInt(indexes[0]) + '][' + parseInt(indexes[1]) + '][metric]"]').attr("name", "elastic[" + i + "][" + parseInt(indexes[1]) + "][metric]");
            $('[name="elastic[' + parseInt(indexes[0]) + '][' + parseInt(indexes[1]) + '][cfg]"]').attr("name", "elastic[" + i + "][" + parseInt(indexes[1]) + "][cfg]");
            $('[name="elastic[' + parseInt(indexes[0]) + '][' + parseInt(indexes[1]) + '][slaconfiguration]"]').attr("name", "elastic[" + i + "][" + parseInt(indexes[1]) + "][slaconfiguration]");
            $('[name="elastic[' + parseInt(indexes[0]) + '][' + parseInt(indexes[1]) + '][bcconfiguration]"]').attr("name", "elastic[" + i + "][" + parseInt(indexes[1]) + "][bcconfiguration]");
            $('[name="elastic[' + parseInt(indexes[0]) + '][' + parseInt(indexes[1]) + '][vmconfiguration]"]').attr("name", "elastic[" + i + "][" + parseInt(indexes[1]) + "][vmconfiguration]");
            $('[name="elastic[' + parseInt(indexes[0]) + '][' + parseInt(indexes[1]) + '][anyconfiguration]"]').attr("name", "elastic[" + i + "][" + parseInt(indexes[1]) + "][anyconfiguration]");
            $('[name="elastic[' + parseInt(indexes[0]) + '][' + parseInt(indexes[1]) + '][relation]"]').attr("name", "elastic[" + i + "][" + parseInt(indexes[1]) + "][relation]");
            $('[name="elastic[' + parseInt(indexes[0]) + '][' + parseInt(indexes[1]) + '][threshold]"]').attr("name", "elastic[" + i + "][" + parseInt(indexes[1]) + "][threshold]");
            $('[name="elastic[' + parseInt(indexes[0]) + '][' + parseInt(indexes[1]) + '][time]"]').attr("name", "elastic[" + i + "][" + parseInt(indexes[1]) + "][time]");
            $('[name="elastic[' + parseInt(indexes[0]) + '][' + parseInt(indexes[1]) + '][timeselect]"]').attr("name", "elastic[" + i + "][" + parseInt(indexes[1]) + "][timeselect]");
            i++;
        });
    }
    function updateIndexes() {
        var i = 0;
        $('div.jobcondition > [name*="]["]').each(function (index, obj) {
            var name = this.name;
            var indexes = getIndexes(name);
            var fieldName = name.substring(name.lastIndexOf("][") + 1);
            var updatedName = "elastic[" + i + "][" + parseInt(indexes[1]) + "]" + fieldName;
            this.name = updatedName;
            if (this.name.indexOf("[timeselect]") != -1) {
                i++;
            }
        });
    }
</script>
<?php
if (isset($jobDataMap)) {
    $jobDataMapCount = count($jobDataMap);
} else {
    $jobDataMapCount = 0;
}
if (isset($jobDataMap["#isNonConcurrent"])) {
    $jobDataMapCount--;
}
if (isset($jobDataMap["#url"])) {
    $jobDataMapCount--;
}
if (isset($jobDataMap["#notificationEmail"])) {
    $jobDataMapCount--;
}
if (isset($jobDataMap["#elasticJobConstraints"])) {
    $jobDataMapCount--;
}
//********START SCRIPT********
echo "<script>\n";

echo "$(document).ready(function() {\n"; //********OPEN document.ready********
echo "\n";

//JS FOR DYNAMICALLY ADDING JOB DATA MAP VALUES
//set x and FieldCount values with the number of job data map items
echo "var MaxInputs = 1000;\n"; //maximum input boxes allowed
echo "var InputsWrapper = $(\"#InputsWrapper\");\n"; //Input boxes wrapper ID
echo "var AddButton = $(\"#AddMoreFileBox\");\n"; //Add button ID
echo "\n";
echo "var x = " . $jobDataMapCount . "\n"; //initial text box count InputsWrapper.length;
echo "var FieldCount=" . $jobDataMapCount . "\n"; //to keep track of text box added -1
echo "\n";
echo "$(AddButton).click(function (e)\n";  //on add input button click
echo "{\n";
echo "if(x <= MaxInputs)\n"; //max input box allowed
echo "{\n";
echo "FieldCount=FieldCount+2;\n"; //text box added increment
//add input box
echo "$(InputsWrapper).append('<p><input type=\"text\" name=\"jobDataMap[]\" placeholder=\"key\" id=\"field_'+ FieldCount +'\" size=\"40\"/>&emsp;<input type=\"text\" name=\"jobDataMap[]\" placeholder=\"value\" id=\"field_'+ (FieldCount+1)+'\" size=\"40\"/>&emsp;<a href=\"#\" class=\"removeclass\">&times;</a><br></p>');\n";
echo "x++\n"; //text box increment
echo "}\n";
echo "return false;\n";
echo "});\n";
echo "\n";
echo "$(\"body\").on(\"click\",\".removeclass\", function(e){\n"; //user click on remove text
echo "if( x > 0 ) {\n";
echo "$(this).parent('p').remove();\n"; //remove text box
echo "x--;\n"; //decrement textbox
echo "}\n";
echo "return false;\n";
echo "})\n";
echo "\n";

//JS TO DYNAMICALLY ADDING NEXT JOBS CONDITIONS
echo "var MaxInputsJobs = 1000;\n"; //maximum input boxes allowed
echo "var InputsWrapperJobs = $(\"#InputsWrapperJobs\");\n"; //Input boxes wrapper ID
echo "var AddButtonJobs = $(\"#AddMoreFileBoxJobs\");\n"; //Add button ID
echo "\n";
echo "var xjobs = 0\n"; //initial text box count InputsWrapper.length;
echo "var FieldCountJobs=-1\n"; //to keep track of text box added -1
echo "\n";
echo "$(AddButtonJobs).click(function (e)\n";  //on add input button click
echo "{\n";
echo "if(xjobs <= MaxInputsJobs)\n"; //max input box allowed
echo "{\n";
echo "FieldCountJobs=FieldCountJobs+4;\n"; //text box added increment
//add input box
echo "$(InputsWrapperJobs).append('<p><b>IF RESULT</b>&emsp;" . getJobSelect(null) . "&emsp;<input type=\"text\" name=\"nextJobs[]\" placeholder=\"result (cannot be empty)\" id=\"field_'+ FieldCountJobs +'\" size=\"40\"/>&emsp;<b>THEN TRIGGER</b>&emsp;<input type=\"text\" name=\"nextJobs[]\" placeholder=\"job name (or semicolon separated emails)\" id=\"field_'+ (FieldCountJobs+1) +'\" size=\"57\"/>&emsp;<input type=\"text\" name=\"nextJobs[]\" placeholder=\"job group (or blank space if job name contains emails)\" id=\"field_'+ (FieldCountJobs+2)+'\" size=\"57\"/>&emsp;<a href=\"#\" class=\"removeclassjobs\">&times;</a><br></p>');\n";
echo "xjobs++\n"; //text box increment
echo "}\n";
echo "return false;\n";
echo "});\n";
echo "\n";
echo "$(\"body\").on(\"click\",\".removeclassjobs\", function(e){\n"; //user click on remove text
//echo "if( xjobs > 0 ) {\n";
echo "$(this).parent('p').remove();\n"; //remove text box
echo "xjobs--;\n"; //decrement textbox
//echo "}\n";
echo "return false;\n";
echo "})\n";

//JS FOR DYNAMICALLY ADDING PROCESS PARAMETERS VALUES
echo "var MaxInputsProcessParameters = 1000;\n"; //maximum input boxes allowed
echo "var InputsWrapperProcessParameters = $(\"#InputsWrapperProcessParameters\");\n"; //Input boxes wrapper ID
echo "var AddButtonProcessParameters = $(\"#AddMoreFileBoxProcessParameters\");\n"; //Add button ID
echo "\n";
echo "var xparameters = 0\n"; //initial text box count InputsWrapper.length;
echo "var FieldCountProcessParameters=0\n"; //to keep track of text box added -1
echo "\n";
echo "$(AddButtonProcessParameters).click(function (e)\n";  //on add input button click
echo "{\n";
echo "if(xparameters <= MaxInputsProcessParameters)\n"; //max input box allowed
echo "{\n";
echo "FieldCountProcessParameters=FieldCountProcessParameters+2;\n"; //text box added increment
//add input box
echo "$(InputsWrapperProcessParameters).append('<p><input type=\"text\" name=\"processParameters[]\" placeholder=\"key\" id=\"field_'+ FieldCountProcessParameters +'\" size=\"40\"/>&emsp;<input type=\"text\" name=\"processParameters[]\" placeholder=\"value\" id=\"field_'+ (FieldCountProcessParameters+1)+'\" size=\"40\"/>&emsp;<a href=\"#\" class=\"removeclassprocessparameters\">&times;</a><br></p>');\n";
echo "xparameters++\n"; //text box increment
echo "}\n";
echo "return false;\n";
echo "});\n";
echo "\n";
echo "$(\"body\").on(\"click\",\".removeclassprocessparameters\", function(e){\n"; //user click on remove text
//echo "if( xjobs > 0 ) {\n";
echo "$(this).parent('p').remove();\n"; //remove text box
echo "xparameters--;\n"; //decrement textbox
//echo "}\n";
echo "return false;\n";
echo "})\n";

//JS TO DYNAMICALLY ADDING JOB CONSTRAINTS (osArch, availableProcessors, freePhysicalMemorySize etc.)
echo "var MaxInputsJobConstraints = 1000;\n"; //maximum input boxes allowed
echo "var InputsWrapperJobConstraints = $(\"#InputsWrapperJobConstraints\");\n"; //Input boxes wrapper ID
echo "var AddButtonJobConstraints = $(\"#AddMoreFileBoxJobConstraints\");\n"; //Add button ID
echo "\n";
echo "var xjobconstraints = 0\n"; //initial text box count InputsWrapper.length;
echo "var FieldCountJobConstraints=-1\n"; //to keep track of text box added -1
echo "\n";
echo "$(AddButtonJobConstraints).click(function (e)\n";  //on add input button click
echo "{\n";
echo "if(xjobs <= MaxInputsJobConstraints)\n"; //max input box allowed
echo "{\n";
echo "FieldCountJobConstraints=FieldCountJobConstraints+4;\n"; //text box added increment
//add input box
echo "$(InputsWrapperJobConstraints).append('<p>" . getJobConstraintsParameterSelect(null) . "&emsp;" . getJobConstraintsOperatorSelect(null) . "&emsp;<input type=\"text\" name=\"jobConstraints[]\" placeholder=\"value\" id=\"field_'+ FieldCountJobConstraints +'\" size=\"40\"/>&emsp;<a href=\"#\" class=\"removeclassjobconstraints\">&times;</a><br></p>');\n";
echo "xjobconstraints++\n"; //text box increment
echo "}\n";
echo "return false;\n";
echo "});\n";
echo "\n";
echo "$(\"body\").on(\"click\",\".removeclassjobconstraints\", function(e){\n"; //user click on remove text
//echo "if( xjobs > 0 ) {\n";
echo "$(this).parent('p').remove();\n"; //remove text box
echo "xjobconstraints--;\n"; //decrement textbox
//echo "}\n";
echo "return false;\n";
echo "})\n";

//run changeMenu js to enable/disable url and process path text field, based on the selected value in the select with id "jobClass"
echo "jobClass = document.getElementById(\"jobClass\")\n";
echo "changeMenu(jobClass);\n";

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
//********END SCRIPT********

echo "</head>\n";
echo "<body>\n";
?>
<!-- js for elastic job constraints -->
<script type=text/javascript>
    $("body").on("click", ".add", function (e) {
        //$(".elasticJob > p:nth-child(1)").clone(true).insertBefore(".end");
        if ($("div.elasticJob > div").length == 0) {
            var div = $("div.conditionTemplateMatch > div").clone(true);
            div.children('select').each(function () {
                this.name = "elastic[0][0]" + this.name;
            });
            div.children('input').each(function () {
                this.name = "elastic[0][0]" + this.name;
            });
            $("div.elasticJob").append(div);
        }
        return false;
    });
    $("body").on("click", ".addSubItem", function (e) {
        var div = $("div.conditionTemplate > div").clone(true);
        var parentDiv = $(this).parent();
        var select = parentDiv.find($('select[name*="configuration"]'));
        var array = getIndexes(select.attr("name"));
        var length = $('select[name*="][cfg"]').length;
        div.children('select').each(function () {
            this.name = "elastic[" + length + "][" + parseInt(array[1]) + "]" + this.name;
        });
        div.children('input').each(function () {
            this.name = "elastic[" + length + "][" + parseInt(array[1]) + "]" + this.name;
        });
        parentDiv.parent().append(div);
        updateIndexes();
        return false;
    });
    $("body").on("click", ".remove", function (e) {
        var firstChild = $(this).parent().parent().find(".jobcondition").first();
        if ($(this).parent().parent().find($(".jobcondition")).length > 1 && $(this).parent().is(firstChild)) {
            return false;
        }
        $(this).parent().remove();
        updateIndexes();
        return false;
    });
    $("body").on("click", ".indent", function (e) {

        var parentDiv = $(this).parent();
        var select = parentDiv.find($('select[name*="cfg"]'));
        var array = getIndexes(select.attr("name"));
        var div;
        if (parentDiv.find($(".jobcondition")).length > 0) {
            div = $("div.conditionTemplate > div").clone(true);
        } else {
            div = $("div.conditionTemplateMatch > div").clone(true);
        }
        div.children('select').each(function () {
            this.name = "elastic[" + (parseInt(array[0]) + 1) + "][" + (parseInt(array[1]) + 1) + "]" + this.name;
        });
        div.children('input').each(function () {
            this.name = "elastic[" + (parseInt(array[0]) + 1) + "][" + (parseInt(array[1]) + 1) + "]" + this.name;
        });
        parentDiv.append(div);
        updateIndexes();
        return false;
    });</script>

<!-- templates for elastic job constraints -->
<div class="selectTemplate" id="slaconfiguration" style="display:none">
    <?php echo getElasticSlaSelect(null, null, null); ?>
</div>
<div class="selectTemplate" id="vmconfiguration" style="display:none">
    <?php echo getElasticVmSelect(null, null, null); ?>
</div>
<div class="selectTemplate" id="bcconfiguration" style="display:none">
    <?php echo getElasticBcSelect(null, null, null); ?>
</div>
<div class="selectTemplate" id="anyconfiguration" style="display:none">
    <select name="[anyconfiguration]" id="anyconfiguration" class="styled-select">
        <option value="any" selected="selected" class="styled-select">any</option>
    </select>
</div>

<div class="conditionTemplate" style="display:none">
    <div class="jobcondition">
        <label>IF Metric</label>
        <?php echo getElasticMetricSelect(null, null, null); ?>
        <label>of</label>
        <select name="[cfg]" class="styled-select" onChange="replace($(this), this.options[this.selectedIndex].value);">
            <option value="SLA" selected="selected" class="styled-select">SLA</option>
            <option value="VM">vm</option>
            <option value="BC">bc</option>
            <option value="ANY">any</option>
        </select>
        <?php echo getElasticSlaSelect(null, null, null); ?>
        <label>IS</label>
        <input type="text" name="[threshold]" style="width:40px"/><label>%</label>
        <select name="[relation]" class="styled-select">
            <option value="ABOVE" selected="selected">ABOVE</option>
            <option value="BELOW">BELOW</option>
        </select>
        <label> THE THRESHOLD FOR</label>
        <input type="text" name="[time]" style="width:40px"/>
        <select name="[timeselect]" class="styled-select">
            <option value="s" selected="selected">s</option>
            <option value="min">min</option>
            <option value="h">h</option>
            <option value="day">day</option>
            <option value="week">week</option>
            <option value="month">month</option>
        </select>
        <a class="remove" style="text-decoration:none;" href="#">&times</a>
        &emsp;<a class="addSubItem" style="text-decoration:none;" href="#">+</a>
        &emsp;<a class="indent" style="text-decoration:none;" href="#">&#8226;</a>
    </div>
</div>

<div class="conditionTemplateMatch" style="display:none">
    <div class="jobcondition">
        <label>Match</label>
        <select name="[match]" class="styled-select">
            <option value="ANY" selected="selected">ANY</option>
            <option value="ALL">ALL</option>
        </select>
        <br>
        <label>IF Metric</label>
        <?php echo getElasticMetricSelect(null, null, null); ?>
        <label>of</label>
        <select name="[cfg]" class="styled-select" onChange="replace($(this), this.options[this.selectedIndex].value);">
            <option value="SLA" selected="selected" class="styled-select">SLA</option>
            <option value="VM">VM</option>
            <option value="BC">BC</option>
            <option value="ANY">ANY</option>
        </select>
        <?php echo getElasticSlaSelect(null, null, null); ?>
        <label>IS</label>
        <input type="text" name="[threshold]" style="width:40px"/><label>%</label>
        <select name="[relation]" class="styled-select">
            <option value="ABOVE" selected="selected">ABOVE</option>
            <option value="BELOW">BELOW</option>
        </select>
        <label> THE THRESHOLD FOR</label>
        <input type="text" name="[time]" style="width:40px"/>
        <select name="[timeselect]" class="styled-select">
            <option value="s" selected="selected">s</option>
            <option value="min">min</option>
            <option value="h">h</option>
            <option value="day">day</option>
            <option value="week">week</option>
            <option value="month">month</option>
        </select>
        <a class="remove" style="text-decoration:none;" href="#">&times</a>
        &emsp;<a class="addSubItem" style="text-decoration:none;" href="#">+</a>
        &emsp;<a class="indent" style="text-decoration:none;" href="#">&#8226;</a>
    </div>
</div>
<?php
include_once "header.php"; //include header
echo "<div id='postFields'>";
echo "<form>\n";

//******** JOB DETAILS ********
echo "<h1><b>Job Data</b></h1>\n";
//storeDurably: set whether or not the Job should remain stored after it is orphaned
//echo "<input type=\"hidden\" value=\"0\" name=\"storeDurably\">\n"; //if the next checkbox is checked, it will override this value, otherwise the '0' value will be posted
$storeDurablyCheckbox = (!isset($jobData["IS_DURABLE"]) || (isset($jobData["IS_DURABLE"]) && $jobData["IS_DURABLE"] == '1') ? " checked=\"checked\" " : "");
echo "<p><b title=\"Set whether or not the Job should remain stored after it is orphaned. If a job is non-durable, it is automatically deleted from the scheduler once there are no longer any active triggers associated with it. In other words, non-durable jobs have a life span bounded by the existence of its triggers.\">Store Durably: </b><input name=\"storeDurably\" class=\"checkbox\" id=\"storeDurably\" type=\"checkbox\" value=\"1\"" . $storeDurablyCheckbox . "onclick=\"toggleCheckbox('storeDurably');\"/></p>\n";

//isNonConcurrent: set the job to disallow to execute concurrently (new triggers that occur before the completion of the current running job will be delayed)
//echo "<input type=\"hidden\" value=\"0\" name=\"isNonConcurrent\">\n"; //if the next checkbox is checked, it will override this value, otherwise the '0' value will be posted
echo "<p><b title=\"Set the job to disallow to execute concurrently (new triggers that occur before the completion of the current running job will be delayed)\">Non-concurrent: </b><input name=\"isNonConcurrent\" class=\"checkbox\" id=\"isNonConcurrent\" type=\"checkbox\" value=\"1\"" . (isset($jobData["IS_NONCONCURRENT"]) && $jobData["IS_NONCONCURRENT"] == '1' ? "checked=\"checked\"" : "") . " onclick=\"toggleCheckbox('isNonConcurrent');\"/></p>\n";

//requestRecovery: in clustering mode, this parameter must be set to true to ensure job fail-over
//echo "<input type=\"hidden\" value=\"0\" name=\"requestRecovery\">\n"; //if the next checkbox is checked, it will override this value, otherwise the '0' value will be posted
echo "<p><b title=\"In clustering mode, this parameter must be set to true to ensure job fail-over. If a job 'requests recovery', and it is executing during the time of a 'hard shutdown' of the scheduler (i.e. the process it is running within crashes, or the machine is shut off), then it is re-executed when the scheduler is started again.\">Request recovery: </b><input name=\"requestRecovery\" class=\"checkbox\" id=\"requestRecovery\" type=\"checkbox\" value=\"1\"" . (isset($jobData["REQUESTS_RECOVERY"]) && $jobData["REQUESTS_RECOVERY"] == '1' ? "checked=\"checked\"" : "") . " onclick=\"toggleCheckbox('requestRecovery');\"/></p>\n";

//withJobIdentityNameGroup: set the job name
//withJobIdentityNameGroup: set the job group
//echo "<p><b title=\"Set the job name\">Job Name: </b><input type=\"text\" name=\"jobName\" value=\"" . (isset($jobData['JOB_NAME']) ? $jobData['JOB_NAME'] : "") . "\" /></p>\n";
//echo "<p><b title=\"Set the job group\">Job Group: </b><input type=\"text\" name=\"jobGroup\" value=\"" . (isset($jobData['JOB_GROUP']) ? $jobData['JOB_GROUP'] : "") . "\" /></p>\n";
echo "<p><input type=\"hidden\" name=\"jobName\" value=\"" . (isset($jobData['JOB_NAME']) ? $jobData['JOB_NAME'] : "") . "\" /></p>\n"; //if the next field is not disabled, it will override this value, otherwise this default value will be posted
echo "<p><b title=\"Set the job name\">Job Name: </b><input type=\"text\" class=\"resizeTextField\" name=\"jobName\" value=\"" . (isset($jobData['JOB_NAME']) ? $jobData['JOB_NAME'] . "\" disabled=\"true\"" : "\"") . "/></p>\n";
echo "<p><input type=\"hidden\" name=\"jobGroup\" value=\"" . (isset($jobData['JOB_GROUP']) ? $jobData['JOB_GROUP'] : "") . "\" /></p>\n"; //if the next field is not disabled, it will override this value, otherwise this default value will be posted
echo "<p><b title=\"Set the job group\">Job Group: </b><input type=\"text\" class=\"resizeTextField\" name=\"jobGroup\" value=\"" . (isset($jobData['JOB_GROUP']) ? $jobData['JOB_GROUP'] . "\" disabled=\"true\"" : "\"") . "/></p>\n";

//Set the description given to the Job instance by its creator (if any)
echo "<p><b title=\"Set the description given to the Job instance by its creator (if any)\">Job Description: </b><input type=\"text\" class=\"resizeTextField\" name=\"jobDescription\" value=\"" . (isset($jobData['DESCRIPTION']) ? $jobData['DESCRIPTION'] : "") . "\" /></p>\n";
//echo "<p><input type=\"hidden\" name=\"jobDescription\" value=\"" . (isset($jobData['DESCRIPTION']) ? $jobData['DESCRIPTION'] : "") . "\" /></p>\n"; //if the next field is not disabled, it will override this value, otherwise this default value will be posted
//echo "<p><b title=\"Set the description given to the Job instance by its creator (if any)\">Job Description: </b><input type=\"text\" name=\"jobDescription\" value=\"" . (isset($jobData['DESCRIPTION']) ? $jobData['DESCRIPTION'] . "\" disabled=\"true\"" : "\"") . "/></p>\n";
//Set the job class (RESTJob, RESTXMLJob, RESTKBJob, RESTCheckSLAJob, ProcessExecutorJob, DumbJob)
echo "<p><b title=\"Set the job type\">Job Type: </b>\n";
echo "<select id=\"jobClass\" name=\"jobClass\" onchange=\"return changeMenu(this)\">\n";
echo "<option value=\"RESTJob\"" . (isset($jobData) && ($jobData["JOB_CLASS_NAME"] == "sce.RESTJob" || $jobData["JOB_CLASS_NAME"] == "sce.RESTJobStateful") ? " selected=\"selected\"" : "") . ">REST</option>\n";
echo "<option value=\"RESTXMLJob\"" . (isset($jobData) && ($jobData["JOB_CLASS_NAME"] == "sce.RESTXMLJob" || $jobData["JOB_CLASS_NAME"] == "sce.RESTXMLJobStateful") ? " selected=\"selected\"" : "") . ">RESTXML</option>\n";
echo "<option value=\"RESTKBJob\"" . (isset($jobData) && ($jobData["JOB_CLASS_NAME"] == "sce.RESTKBJob" || $jobData["JOB_CLASS_NAME"] == "sce.RESTKBJobStateful") ? " selected=\"selected\"" : "") . ">RESTKB</option>\n";
echo "<option value=\"RESTCheckSLAJob\"" . (isset($jobData) && ($jobData["JOB_CLASS_NAME"] == "sce.RESTCheckSLAJob" || $jobData["JOB_CLASS_NAME"] == "sce.RESTCheckSLAJobStateful") ? " selected=\"selected\"" : "") . ">RESTCheckSLA</option>\n";
echo "<option value=\"RESTAppMetricJob\"" . (isset($jobData) && ($jobData["JOB_CLASS_NAME"] == "sce.RESTAppMetricJob" || $jobData["JOB_CLASS_NAME"] == "sce.RESTAppMetricJobStateful") ? " selected=\"selected\"" : "") . ">RESTAppMetric</option>\n";
echo "<option value=\"ProcessExecutorJob\"" . (isset($jobData) && ($jobData["JOB_CLASS_NAME"] == "sce.ProcessExecutor" || $jobData["JOB_CLASS_NAME"] == "sce.ProcessExecutorStateful") ? " selected=\"selected\"" : "") . ">ProcessExecutor</option>\n";
echo "<option value=\"ElasticJob\"" . (isset($jobData) && ($jobData["JOB_CLASS_NAME"] == "sce.ElasticJob" || $jobData["JOB_CLASS_NAME"] == "sce.ElasticJobStateful") ? " selected=\"selected\"" : "") . ">Elastic</option>\n";
echo "<option value=\"DumbJob\"" . (isset($jobData) && ($jobData["JOB_CLASS_NAME"] == "sce.DumbJob" || $jobData["JOB_CLASS_NAME"] == "sce.DumbJobStateful") ? " selected=\"selected\"" : "") . ">dumb</option>\n";
echo "</select>\n";
echo "</p>\n";

//Set the URL (REST)
echo "<p><b title=\"Set the URL (REST)\">URL: </b><input type=\"text\" class=\"resizeTextField\" id=\"resturl\" name=\"resturl\" value=\"" . (isset($jobDataMap['#url']) ? $jobDataMap['#url'] : "") . "\" /></p>\n";

//Set the path of process to be executed
//$jobDataMap["#processParameters"] is stored as a json value in the $jobDataMap array
//thus when decoding the whole $jobDataMap json array, $jobDataMap["#processParameters"]
//must be converted from stdClass to a standard array
if (isset($jobDataMap)) {
    $processParametersArr = objectToArray(json_decode($jobDataMap["#processParameters"]));
}
echo "<p><b title=\"Set the path of process to be executed\">Process Path: </b><input type=\"text\" class=\"resizeTextField\" id=\"processPath\" name=\"processPath\" value=\"" . (isset($processParametersArr[0]['processPath']) ? $processParametersArr[0]['processPath'] : "") . "\" /></p>\n";

//******** TRIGGER DETAILS ********
//if an new job is being created, ask for trigger data
if (!isset($_GET['dormantJob']) && (!isset($_GET['jobName']) || !isset($_GET['jobGroup']))) {
    echo "<h1><b>Trigger Data</b></h1>\n";
    //startNow: set the time the Trigger should start at to the current moment - the trigger may or may not fire at this time - depending upon the schedule configured for the Trigger
    //echo "<p><b title=\"Set the time the Trigger should start at to the current moment - the trigger may or may not fire at this time - depending upon the schedule configured for the Trigger\">Start Now: </b><input name=\"startNow\" type=\"checkbox\" value=\"1\" checked=\"checked\"/></p>\n";
    //startAt: set the time the Trigger should start at - the trigger may or may not fire at this time - depending upon the schedule configured for the Trigger, number of milliseconds from January 1st 1970
    echo "<p><b title=\"Set the time the Trigger should start at - the trigger may or may not fire at this time - depending upon the schedule configured for the Trigger\">Start At: </b><input type=\"text\" name=\"startAt\" value=\"\" id=\"datepicker1\" /></p>\n";

    //endAt: set the time at which the Trigger will no longer fire - even if it's schedule has remaining repeats, number of milliseconds from January 1st 1970
    echo "<p><b title=\"Set the time at which the Trigger will no longer fire - even if it's schedule has remaining repeats\">End At: </b><input type=\"text\" name=\"endAt\" value=\"\" id=\"datepicker2\" /></p>\n";

    //modifiedByCalendar: set the name of the Calendar that should be applied to this Trigger's schedule
    echo "<p><b title=\"Set the name of the Calendar that should be applied to this Trigger's schedule\">Calendar Name: </b><input type=\"text\" class=\"resizeTextField\" name=\"modifiedByCalendar\" value=\"\" /></p>\n";

    //withIdentityNameGroup: use a TriggerKey with the given name and group to identify the Trigger, e.g. name.group
    //withIdentityTriggerKey: use the given TriggerKey to identify the Trigger, e.g. name.group
    echo "<p><b title=\"Set the trigger name\">Trigger Name: </b><input type=\"text\" class=\"resizeTextField\" name=\"triggerName\" value=\"\" /></p>\n";
    echo "<p><b title=\"Set the trigger group\">Trigger Group: </b><input type=\"text\" class=\"resizeTextField\" name=\"triggerGroup\" value=\"\" /></p>\n";

    //withDescription: set the given (human-meaningful) description of the Trigger
    echo "<p><b title=\"Set the given (human-meaningful) description of the Trigger\">Trigger Description: </b><input type=\"text\" class=\"resizeTextField\" name=\"triggerDescription\" value=\"\" /></p>\n";

    //withPriority: set the Trigger's priority
    echo "<p><b title=\"Set the Trigger's priority\">Priority: </b><input type=\"text\" name=\"priority\" value=\"5\" /></p>\n";

    //repeatForever: specify that the trigger will repeat indefinitely
    //withRepeatCount: specify the number of time the trigger will repeat - total number of firings will be this number + 1
    echo "<p><b title=\"Specify the number of time the trigger will repeat - total number of firings will be this number + 1, (0 or -1 mean indefinitely)\">Repeat Count: </b><input type=\"text\" name=\"repeatCount\" value=\"0\" /></p>\n";

    //<withIntervalInSeconds: specify a repeat interval in seconds - which will then be multiplied by 1000 to produce milliseconds
    echo "<p><b title=\"Specify a repeat interval in seconds\">Interval (s): </b><input type=\"text\" name=\"intervalInSeconds\" value=\"60\" /></p>\n";

    //withMisfireHandlingInstructionFireNow: if the Trigger misfires, use the SimpleTrigger.MISFIRE_INSTRUCTION_FIRE_NOW instruction
    //withMisfireHandlingInstructionIgnoreMisfires: if the Trigger misfires, use the Trigger.MISFIRE_INSTRUCTION_IGNORE_MISFIRE_POLICY instruction
    //withMisfireHandlingInstructionNextWithExistingCount: if the Trigger misfires, use the SimpleTrigger.MISFIRE_INSTRUCTION_RESCHEDULE_NEXT_WITH_EXISTING_COUNT instruction
    //withMisfireHandlingInstructionNextWithRemainingCount: if the Trigger misfires, use the SimpleTrigger.MISFIRE_INSTRUCTION_RESCHEDULE_NEXT_WITH_REMAINING_COUNT instruction
    //withMisfireHandlingInstructionNowWithExistingCount: if the Trigger misfires, use the SimpleTrigger.MISFIRE_INSTRUCTION_RESCHEDULE_NOW_WITH_EXISTING_REPEAT_COUNT instruction
    //withMisfireHandlingInstructionNowWithRemainingCount: if the Trigger misfires, use the SimpleTrigger.MISFIRE_INSTRUCTION_RESCHEDULE_NOW_WITH_REMAINING_REPEAT_COUNT instruction
    echo "<p><b title=\"If the Trigger misfires, use this misfire instruction\">Misfire Instruction: </b>\n";
    echo "<select name=\"misfire\">\n";
    echo "<option value=\"FIRE_NOW\">FIRE_NOW</option>\n";
    echo "<option value=\"DEFAULT\" selected=\"selected\">DEFAULT</option>\n"; //SMART_POLICY
    echo "<option value=\"IGNORE_MISFIRE_POLICY\">IGNORE_MISFIRE_POLICY</option>\n";
    echo "<option value=\"RESCHEDULE_NEXT_WITH_EXISTING_COUNT\">RESCHEDULE_NEXT_WITH_EXISTING_COUNT</option>\n";
    echo "<option value=\"RESCHEDULE_NEXT_WITH_REMAINING_COUNT\">RESCHEDULE_NEXT_WITH_REMAINING_COUNT</option>\n";
    echo "<option value=\"RESCHEDULE_NOW_WITH_EXISTING_REPEAT_COUNT\">RESCHEDULE_NOW_WITH_EXISTING_REPEAT_COUNT</option>\n";
    echo "<option value=\"RESCHEDULE_NOW_WITH_REMAINING_REPEAT_COUNT\">RESCHEDULE_NOW_WITH_REMAINING_REPEAT_COUNT</option>\n";
    echo "</select>\n";
    echo "</p>\n";
}

//email: send a notification message to this email upon job completion
echo "<p><b title=\"Send a notification message to this email upon job completion\">Email: </b><input type=\"text\" class=\"resizeTextField\" name=\"notificationEmail\" value=\"" . (isset($notificationEmail) ? $notificationEmail : "") . "\" /></p>\n";

//action to perform, 'updateJob' = update an existing job, 'scheduleJob' = define a new job with a trigger, 'addJob' = add a dormant job without trigger
if (isset($_GET['dormantJob'])) {
    $action = "addJob";
} else if (isset($_GET['jobName']) && isset($_GET['jobGroup'])) {
    $action = "updateJob";
} else {
    $action = "scheduleJob";
}
echo "<input type=\"hidden\" name=\"id\" value=\"" . $action . "\">\n";

//PRINT JOB DATA MAP DYNAMIC FIELDS
echo "<a id=\"AddMoreFileBox\" href=\"#\">Add Data Map</a><br><br>\n";
echo "<div id=\"InputsWrapper\">\n";
$counter = 1;
if (isset($jobDataMap)) {
    foreach ($jobDataMap as $key => $value) {
        //don't print reserved job data map values: #isNonConcurrent, #url, #notificationEmail, yet displayed above in the form, and #nextJobs, #processParameters, #jobConstraints displayed below
        if ($key != "#isNonConcurrent" && $key != "#url" && $key != "#notificationEmail" && $key != "#nextJobs" && $key != "#processParameters" && $key != "#jobConstraints" && $key != "#elasticJobConstraints") {
            echo "<p><input type=\"text\" name=\"jobDataMap[]\" value=\"" . $key . "\" id=\"field_" . $counter . "\" size=\"40\"/>&emsp;<input type=\"text\" name=\"jobDataMap[]\" value=\"" . $value . "\" id=\"field_" . ($counter + 1) . "\" size=\"40\"/><a href=\"#\" class=\"removeclass\">&times;</a><br></p>";
        }
        $counter++;
    }
}
//echo "<div></div>\n";
echo "</div>\n";

//PRINT NEXT JOB CONDITIONS
echo "<a id=\"AddMoreFileBoxJobs\" href=\"#\">Add Next Job</a><br><br>\n";
echo "<div id=\"InputsWrapperJobs\">\n";
$counter = 1;
//$jobDataMap["#nextJobs"] is stored as a json value in the $jobDataMap array
//thus when decoding the whole $jobDataMap json array, $jobDataMap["#nextJobs"]
//must be converted from stdClass to a standard array
if (isset($jobDataMap)) {
    $nextJobsArr = objectToArray(json_decode($jobDataMap["#nextJobs"]));
}
if (isset($nextJobsArr)) {
    for ($i = 0; $i < count($nextJobsArr); $i++) {
        echo "<p><b>IF RESULT</b>&emsp;" . getJobSelect($nextJobsArr[$i]["operator"]) . "&emsp;<input type=\"text\" name=\"nextJobs[]\" placeholder=\"result (cannot be empty)\" id=\"field_" . $counter . "\" value=\"" . $nextJobsArr[$i]["result"] . "\" size=\"49\"/>&emsp;<b>THEN TRIGGER</b>&emsp;<input type=\"text\" name=\"nextJobs[]\" placeholder=\"job name (or comma separated emails)\" id=\"field_" . ($counter + 1) . "\" value=\"" . $nextJobsArr[$i]["jobName"] . "\" size=\"57\"/>&emsp;<input type=\"text\" name=\"nextJobs[]\" placeholder=\"job group (blank space if job name contains emails)\" id=\"field_" . ($counter + 2) . "\" value=\"" . $nextJobsArr[$i]["jobGroup"] . "\" size=\"49\"/>&emsp;<a href=\"#\" class=\"removeclassjobs\">&times;</a><br></p>";
        $counter++;
    }
}
echo "</div>\n";

//PRINT PROCESS PARAMETERS
echo "<a id=\"AddMoreFileBoxProcessParameters\" href=\"#\">Add Process Parameter</a><br><br>\n";
echo "<div id=\"InputsWrapperProcessParameters\">\n";
$counter = 1;
if (isset($processParametersArr)) {
    for ($i = 0; $i < count($processParametersArr); $i++) {
        foreach ($processParametersArr[$i] as $key => $value) {
            //don't print process path value, printed above in a text field of the form
            if ($key != "processPath") {
                echo "<p><input type=\"text\" name=\"processParameters[]\" value=\"" . $key . "\" id=\"field_" . $counter . "\" size=\"40\"/>&emsp;<input type=\"text\" name=\"processParameters[]\" value=\"" . $value . "\" id=\"field_" . ($counter + 1) . "\" size=\"40\"/><a href=\"#\" class=\"removeclassprocessparameters\">&times;</a><br></p>";
                $counter++;
            }
        }
    }
}
echo "</div>\n";

//PRINT JOB CONSTRAINTS
echo "<a id=\"AddMoreFileBoxJobConstraints\" href=\"#\">Add Job Constraint</a><br><br>\n";
echo "<div id=\"InputsWrapperJobConstraints\">\n";
$counter = 1;
//$jobDataMap["#jobConstraints"] is stored as a json value in the $jobDataMap array
//thus when decoding the whole $jobDataMap json array, $jobDataMap["#jobConstraints"]
//must be converted from stdClass to a standard array
if (isset($jobDataMap)) {
    $jobConstraintsArr = objectToArray(json_decode($jobDataMap["#jobConstraints"]));
}
if (isset($jobConstraintsArr)) {
    for ($i = 0; $i < count($jobConstraintsArr); $i++) {
        echo "<p>" . getJobConstraintsParameterSelect($jobConstraintsArr[$i]["systemParameterName"]) . "&emsp;" . getJobConstraintsOperatorSelect($jobConstraintsArr[$i]["operator"]) . "&emsp;<input type=\"text\" name=\"jobConstraints[]\" placeholder=\"value\" id=\"field_" . $counter . "\" value=\"" . $jobConstraintsArr[$i]["value"] . "\" size=\"40\"/>&emsp;<a href=\"#\" class=\"removeclassjobconstraints\">&times;</a><br></p>";
        $counter++;
    }
}
echo "</div>\n";

//PRINT ELASTIC JOB CONSTRAINTS
echo "<a class=\"add\" href=\"#\">Add Elastic Job Constraints</a><br><br>";
echo "<div class=\"elasticJob\"></div>";
$counter = 1;
//$jobDataMap["#elasticJobConstraints"] is stored as a json value in the $jobDataMap array
//thus when decoding the whole $jobDataMap json array, $jobDataMap["#elasticJobConstraints"]
//must be converted from stdClass to a standard array
if (isset($jobDataMap)) {
    $elasticJobConstraintsArr = objectToArray(json_decode($jobDataMap["#elasticJobConstraints"]));
}
// build the elastic constraints
if (isset($elasticJobConstraintsArr)) {
    $i_temp = 0;
    $j_temp = 0;
    $array = array(0);
    ksort($elasticJobConstraintsArr);
    foreach ($elasticJobConstraintsArr as $i => $j1) {
        ksort($j2);
        foreach ($j1 as $j => $j2) {
            //echo "i: " . $i . " j: " . $j . "<br>";
            /* if ($i_temp == -1 && $j_temp == -1) {
              //echo "add name: " . $name . " val: " . $val . "<br>";
              echo "<script type=\"text/javascript\">add();</script>";
              } else if ($i > $i_temp && $j == $j_temp) {
              //echo "addSubItem name: " . $name . " val: " . $val . "<br>";
              $n = "elastic[" . ($i - 1) . "][" . $j . "]";
              echo "<script type=\"text/javascript\">addSubItem('" . $n . "');</script>";
              } else if ($i > $i_temp && $j > $j_temp) {
              //echo "indent name: " . $name . " val: " . $val . "<br>";
              $n = "elastic[" . ($i - 1) . "][" . ($j - 1) . "]";
              echo "<script type=\"text/javascript\">indent('" . $n . "');</script>";
              } else if ($i > $i_temp && $j < $j_temp) {
              //echo "indent name: " . $name . " val: " . $val . "<br>";
              $n = "elastic[" . ($array[$i] - 1) . "][" . ($j - 1) . "]";
              echo "<script type=\"text/javascript\">indent('" . $n . "');</script>";
              } */
            $n = "elastic[" . $i . "][" . $j . "]";
            echo "<script type=\"text/javascript\">addCondition('" . $n . "');</script>";
            //else
            //echo "<script type=\"text/javascript\">indent('" . $n . "');</script>";
            $i_temp = $i;
            $j_temp = $j;
        }
    }
}

//echo '<script type="text/javascript">add();</script>';
//SUBMIT
//echo "<p><input type=\"submit\"></p>\n";
echo "<input name=\"confirm\" type=\"button\" value=\"Confirm\" onclick=\"submit_form();\" />&emsp;";
echo "<input name=\"export\" type=\"button\" value=\"Export\" onclick=\"exportJob();\" />";
echo "</form>\n";

// populate the elastic constraints
if (isset($elasticJobConstraintsArr)) {
    foreach ($elasticJobConstraintsArr as $i => $j1) {
        foreach ($j1 as $j => $j2) {
            foreach ($j2 as $key => $value) {
                echo "<script type =\"text/javascript\">populate('elastic[" . $i . "][" . $j . "][" . $key . "]','" . $value . "');</script>";
                //echo "i: " . $i . " j: " . $j . " key: " . $key . " value: " . $value . "<br>";
            }
        }
    }
    //echo "<script type=\"text/javascript\">updateIndexes();</script>";
}

//if an existing job is being edited, print the links of the triggers of the job
if (isset($_GET['jobName']) && isset($_GET['jobGroup'])) {
    if ($jobTriggers > 0) {
        echo "<p><b>Triggers</b></p>";
    }
    foreach ($jobTriggers as $key => $value) {
        echo "<a class=\"pointer\"  href=\"newTrigger.php?triggerName=" . $jobTriggers[$key][0] . "&triggerGroup=" . $jobTriggers[$key][1] . "\">" . $jobTriggers [$key][0] . "." . $jobTriggers [$key] [1] . "</a>&emsp;\n";
    }
    echo "<br><br>\n";
}

//echo "<a class=\"pointer\" title=\"Back\" href=\"#\" onclick=\"history.back();\">Back</a>&emsp;\n";
echo "<a class=\"pointer\" href=\"#\" onclick=\"if(document.referrer) {window.open(document.referrer,'_self');} else {history.go(-1);}return false;\">Back</a>&emsp;\n";
echo "<a class=\"pointer\" title=\"Home\" href=\"index.php\">Home</a>\n";
echo "</div>"; //close <div id='postFields'> of newJob
echo "</body>\n";
echo "</html>\n";

//$fp = fopen("/var/www/html/sce/log.txt", "at");
//fwrite($fp, $jobDataMap["#elasticJobConstraints"]);
//fclose($fp);
?>
 
