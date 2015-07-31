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
include_once "../sce/settings.php";
include_once "../sce/functions.php";

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

//getSLAs from the KB
function getSLAs() {
    $sparql = "PREFIX%20icr%3A%3Chttp%3A%2F%2Fwww.cloudicaro.it%2Fcloud_ontology%2Fcore%23%3E%20%0APREFIX%20xsd%3A%3Chttp%3A%2F%2Fwww.w3.org%2F2001%2FXMLSchema%23%3E%20select%20DISTINCT%20%3Fsla%20where%20%7B%0A%20%20%20%20%20%20%20%20%20%20%20%20%3Fsla%20icr%3AhasSLObjective%20%5B%0A%20%20%20%20%20%20%20%20%20%20%20%20icr%3AhasSLAction%20%5B%20icr%3AcallUrl%20%3Fact%20%5D%3B%0A%20%20%20%20%20%20%20%20%20%20%20%20icr%3AhasSLMetric%20%5B%0A%20%20%20%20%20%20%20%20%20%20%20%20a%20icr%3AServiceLevelAndMetric%3B%0A%20%20%20%20%20%20%20%20%20%20%20%20icr%3AdependsOn%20%3Fx%0A%20%20%20%20%20%20%20%20%20%20%20%20%5D%0A%20%20%20%20%20%20%20%20%20%20%20%20%5D.%0A%20%20%20%20%20%20%20%20%20%20%20%20%3Fx%20%20icr%3AhasMetricName%20%3Fmn%3B%0A%20%20%20%20%20%20%20%20%20%20%20%20icr%3AdependsOn%20%3Fsm.%0A%20%20%20%20%20%20%20%20%20%20%20%20%7B%3Fx%20icr%3AhasMetricValueLessThan%20%3Fv.%7D%20UNION%0A%20%20%20%20%20%20%20%20%20%20%20%20%7B%3Fx%20icr%3AhasMetricValueGreaterThan%20%3Fv%7D%20UNION%0A%20%20%20%20%20%20%20%20%20%20%20%20%7B%3Fx%20icr%3AhasMetricValue%20%3Fv%7D%0A%20%20%20%20%20%20%20%20%20%20%20%20%3Fx%20%3Fp%20%3Fv.%0A%20%20%20%20%20%20%20%20%20%20%20%20%7D";
    $result = getSPARQLResult($sparql);
    $slas = array();
    foreach ($result as $k1 => $v1) {
        foreach ($v1 as $k2 => $v2) {
            foreach ($v2 as $v3 => $k3) {
                if ($v3 != 'type' && $v3 != 'datatype') {
                    $slas[] = $k3;
                }
            }
        }
    }
    return $slas;
}

// get the jobs currently in the scheduler except the job that keep updated the jobs in the scheduler with the SLA in the KB and the job that sends the application metrics to the CM
function getSchedulerJobs() {
    global $config;
    //CONNECT
    $link = mysqli_connect($config['host'], $config['user'], $config['pass'], $config['database']);

    /* check connection */
    if (mysqli_connect_errno()) {
        printf("Connection failed: %s\n", mysqli_connect_error());
        exit();
    }

    //GET DATA
    $sql = "SELECT JOB_NAME, JOB_GROUP FROM quartz.QRTZ_JOB_DETAILS WHERE JOB_NAME != 'updateJobs'  AND JOB_NAME != 'appMetrics'";
    $result = mysqli_query($link, $sql) or die(mysqli_error());
    $jobs = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $jobs[$row['JOB_NAME'] . ";" . $row['JOB_GROUP']] = 1;
    }
    mysqli_close($link); //close connection
    return $jobs;
}

global $config;
$url = "http://" . $config["tomcat"] . ":8080/SmartCloudEngine/index.jsp";

//switch (filter_input(INPUT_POST, 'id')) {
switch ($_REQUEST['id']) {
    case 'buildJobs':
        $postData["id"] = "buildJobs";
        $slas = getSLAs();
        $time = time();
        for ($i = 0; $i < count($slas); $i++) {
            $period = 1200;
            $startTime = $time * 1000;
            //$jsonData["json"] = "{\"startAt\":\"" + $startTime + "\",\"withDescription\":\"" + $slas[$i] + "\",\"withIdentityNameGroup\":[\"" + $slas[$i] + "\",\"" + $slas[$i] + "\"],\"withPriority\":\"5\",\"repeatForever\":\"true\",\"withIntervalInSeconds\":\"" + $period + "\",\"storeDurably\":\"true\",\"requestRecovery\":\"false\",\"withJobIdentityNameGroup\":[\"" + $slas[$i] + "\",\"" + $slas[$i] + "\"],\"withJobDescription\":\"" + $slas[$i] + "\",\"id\":\"scheduleJob\",\"jobClass\":\"RESTCheckSLAJob\",\"jobDataMap\":{\"#url\":\"http:\\/\\/" + $config['sparql_username'] + ":" + $config['sparql_password'] + "@" + $config['sparql_ip'] + ":8080\\/IcaroKB\\/sparql\",\"#notificationEmail\":\"daniele.cenni@unifi.it\",\"#isNonConcurrent\":\"false\",\"#slaId\":\"" + $slas[$i] + "\",\"#slaTimestamp\":\"2014-09-11T16:30:00\"}}";
            $jsonData["json"] = "{\"startAt\":\"" . $startTime . "\",\"withDescription\":\"" . $slas[$i] . "\",\"withIdentityNameGroup\":[\"" . $slas[$i] . "\",\"" . $slas[$i] . "\"],\"withPriority\":\"5\",\"repeatForever\":\"true\",\"withIntervalInSeconds\":\"" . $period . "\",\"storeDurably\":\"true\",\"requestRecovery\":\"false\",\"withJobIdentityNameGroup\":[\"" . $slas[$i] . "\",\"" . $slas[$i] . "\"],\"withJobDescription\":\"" . $slas[$i] . "\",\"id\":\"scheduleJob\",\"jobClass\":\"RESTCheckSLAJob\",\"jobDataMap\":{\"#url\":\"http:\\/\\/" . $config['sparql_username'] . ":" . $config['sparql_password'] . "@" . $config['sparql_ip'] . ":8080\\/IcaroKB\\/sparql\",\"#isNonConcurrent\":\"false\",\"#slaId\":\"" . $slas[$i] . "\"}}";
            $result = json_decode(postData($jsonData, "http://" . $config['tomcat'] . ":8080/SmartCloudEngine/index.jsp"), true);
            $time += 300;
        }
        break;
    // load the scheduler with the jobs related to SLAs in the KB, not yet present in the scheduler itself
    // remove the jobs related to SLAs not anymore present in the KB
    case 'updateJobs':
        $jobs = getSchedulerJobs();
        $slas = getSLAs();
        $time = time();
        // load the scheduler with the job not yet present
        for ($i = 0; $i < count($slas); $i++) {
            // if the sla is yet present as a job in the scheduler, remove from array and continue
            if ($jobs[$slas[$i] . ";" . $slas[$i]]) {
                unset($jobs[$slas[$i] . ";" . $slas[$i]]);
                continue;
            }
            $period = 1200;
            $startTime = $time * 1000;
            //$jsonData["json"] = "{\"startAt\":\"" + $startTime + "\",\"withDescription\":\"" + $slas[$i] + "\",\"withIdentityNameGroup\":[\"" + $slas[$i] + "\",\"" + $slas[$i] + "\"],\"withPriority\":\"5\",\"repeatForever\":\"true\",\"withIntervalInSeconds\":\"" + $period + "\",\"storeDurably\":\"true\",\"requestRecovery\":\"false\",\"withJobIdentityNameGroup\":[\"" + $slas[$i] + "\",\"" + $slas[$i] + "\"],\"withJobDescription\":\"" + $slas[$i] + "\",\"id\":\"scheduleJob\",\"jobClass\":\"RESTCheckSLAJob\",\"jobDataMap\":{\"#url\":\"http:\\/\\/" + $config['sparql_username'] + ":" + $config['sparql_password'] + "@" + $config['sparql_ip'] + ":8080\\/IcaroKB\\/sparql\",\"#notificationEmail\":\"daniele.cenni@unifi.it\",\"#isNonConcurrent\":\"false\",\"#slaId\":\"" + $slas[$i] + "\",\"#slaTimestamp\":\"2014-09-11T16:30:00\"}}";
            $jsonData["json"] = "{\"startAt\":\"" . $startTime . "\",\"withDescription\":\"" . $slas[$i] . "\",\"withIdentityNameGroup\":[\"" . $slas[$i] . "\",\"" . $slas[$i] . "\"],\"withPriority\":\"5\",\"repeatForever\":\"true\",\"withIntervalInSeconds\":\"" . $period . "\",\"storeDurably\":\"true\",\"requestRecovery\":\"false\",\"withJobIdentityNameGroup\":[\"" . $slas[$i] . "\",\"" . $slas[$i] . "\"],\"withJobDescription\":\"" . $slas[$i] . "\",\"id\":\"scheduleJob\",\"jobClass\":\"RESTCheckSLAJob\",\"jobDataMap\":{\"#url\":\"http:\\/\\/" . $config['sparql_username'] . ":" . $config['sparql_password'] . "@" . $config['sparql_ip'] . ":8080\\/IcaroKB\\/sparql\",\"#isNonConcurrent\":\"false\",\"#slaId\":\"" . $slas[$i] . "\"}}";
            $result = json_decode(postData($jsonData, "http://" . $config['tomcat'] . ":8080/SmartCloudEngine/index.jsp"), true);
            $time += 300;
            unset($jobs[$slas[$i] . ";" . $slas[$i]]);
        }
        // delete from the scheduler the jobs not anymore present in the KB
        foreach ($jobs as $key => $val) {
            $jobNameGroup = split(";", $key);
            $jsonData["json"] = "{\"id\":\"deleteJob\", \"jobName\":\"" . $jobNameGroup[0] . "\",\"jobGroup\":\"" . $jobNameGroup[1] . "\"}";
            postData($jsonData, "http://" . $config['tomcat'] . ":8080/SmartCloudEngine/index.jsp");
        }
        break;
    default:
        echo "error";
        break;
}

//CALL URL (post json data)
//$jsonData["json"] = json_encode($postData);
//$result = json_decode(postData($jsonData, $url), true);
//LOG
//$fp = fopen("/var/www/html/sce/log.txt", "at");
//fwrite($fp, $jsonData);
//fclose($fp);
//echo $result[1];
?>