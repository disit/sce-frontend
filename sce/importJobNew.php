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

if (isset($_REQUEST['json'])) {
    global $config;

    //GET SCHEDULER NAME
    $postData['id'] = "getSchedulerMetadata";
    $jsonData["json"] = json_encode($postData);
    $result = json_decode(postData($jsonData, "http://" . $config["tomcat"] . ":8080/SmartCloudEngine/index.jsp"), true);
    $schedulerName = $result["Scheduler name"][0];

    $data = json_decode($_REQUEST['json']);

    //CONNECT
    $link = mysqli_connect($config['host'], $config['user'], $config['pass'], $config['database']);

    /* check connection */
    if (mysqli_connect_errno()) {
        printf("Connection failed: %s\n", mysqli_connect_error());
        exit();
    }
    //INSERT JOBS
    for ($i = 0; $i < count($data); $i++) {
        if ($data[$i]['type'] == 'job') {
            foreach ($data[$i] as $field => $value) {
                $sql = "INSERT INTO quartz.QRTZ_JOB_DETAILS (SCHED_NAME, JOB_NAME, JOB_GROUP, DESCRIPTION, JOB_CLASS_NAME, IS_DURABLE, IS_NONCONCURRENT, IS_UPDATE_DATA, REQUEST_RECOVERY, JOB_DATA) VALUES('" . $schedulerName . "', '" . $data[$i]['JOB_NAME'] . "', '" . $data[$i]['JOG_GROUP'] . "'," . $data[$i]['DESCRIPTION'] . "'," . $data[$i]['JOB_CLASS_NAME'] . "'," . $data[$i]['IS_DURABLE'] . "'," . $data[$i]['IS_NONCONCURRENT'] . "'," . $data[$i]['IS_UPDATE_DATA'] . "'," . $data[$i]['IS_DURABLE'] . "'," . $data[$i]['JOB_NAME'] . "', '" . $data[$i]['IS_DURABLE'] . "'," . $data[$i]['IS_DURABLE'] . "'," . $data[$i]['IS_DURABLE'] . ")";
                //$result = mysqli_query($link, $sqlJobs) or die(mysqli_error());
                echo $field . " " . $value . "<br>";
            }
        }
    }
    //INSERT TRIGGERS
    for ($i = 0; $i < count($data); $i++) {
        if ($data[$i]['type'] == 'job') {
            foreach ($data[$i] as $field => $value) {
                //$sql = "INSERT INTO quartz.QRTZ_JOB_DETAILS (SCHED_NAME, JOB_NAME, JOB_GROUP, DESCRIPTION, JOB_CLASS_NAME, IS_DURABLE, IS_NONCONCURRENT, IS_UPDATE_DATA, REQUEST_RECOVERY, JOB_DATA) VALUES()";
                //$result = mysqli_query($link, $sqlJobs) or die(mysqli_error());
                echo $field . " " . $value . "<br>";
            }
        }
    }
}
?>