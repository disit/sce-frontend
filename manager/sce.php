<?php

include_once "../sce/settings.php";

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

global $config;
$url = "http://" . $config["tomcat"] . ":8080/SmartCloudEngine/index.jsp";

switch (filter_input(INPUT_POST, 'id')) {
    case 'isInStandbyMode':
        $postData["id"] = "isInStandbyMode";
        break;
    case 'isShutdown':
        $postData["id"] = "isShutdown";
        break;
    case 'isStarted':
        $postData["id"] = "isStarted";
        break;
    case 'deleteJob':
        $postData["id"] = "deleteJob";
        $postData["jobName"] = filter_input(INPUT_POST, 'jobName');
        $postData["jobGroup"] = filter_input(INPUT_POST, 'jobGroup');

        //delete the triggers associated to this job in the tables QRTZ_STATUS, QRTZ_REJECTED_TRIGGERS
        $link = mysqli_connect($config['host'], $config['user'], $config['pass'], $config['database']);
        if (mysqli_connect_errno()) {
            printf("Connection failed: %s\n", mysqli_connect_error());
            exit();
        }
        $sql = "DELETE FROM quartz.QRTZ_STATUS WHERE JOB_NAME = '" . $postData["jobName"] . "' AND JOB_GROUP = '" . $postData["jobGroup"] . "'";
        $result = mysqli_query($link, $sql) or die(mysqli_error());
        $sql = "DELETE FROM quartz.QRTZ_REJECTED_TRIGGERS WHERE JOB_NAME = '" . $postData["jobName"] . "' AND JOB_GROUP = '" . $postData["jobGroup"] . "'";
        $result = mysqli_query($link, $sql) or die(mysqli_error());
        mysqli_close($link);

        break;
    case 'resumeJob':
        $postData["id"] = "resumeJob";
        $postData["jobName"] = filter_input(INPUT_POST, 'jobName');
        $postData["jobGroup"] = filter_input(INPUT_POST, 'jobGroup');
        break;
    case 'pauseJob':
        $postData["id"] = "pauseJob";
        $postData["jobName"] = filter_input(INPUT_POST, 'jobName');
        $postData["jobGroup"] = filter_input(INPUT_POST, 'jobGroup');
        break;
    case 'interruptJob':
        $postData["id"] = "interruptJob";
        $postData["jobName"] = filter_input(INPUT_POST, 'jobName');
        $postData["jobGroup"] = filter_input(INPUT_POST, 'jobGroup');
        break;
    case 'unscheduleJob':
        $postData["id"] = "unscheduleJob";
        $postData["triggerName"] = filter_input(INPUT_POST, 'triggerName');
        $postData["triggerGroup"] = filter_input(INPUT_POST, 'triggerGroup');
        break;
    case 'triggerJob':
        $postData["id"] = "triggerJob";
        $postData["jobName"] = filter_input(INPUT_POST, 'jobName');
        $postData["jobGroup"] = filter_input(INPUT_POST, 'jobGroup');
        break;
    case 'resumeTrigger':
        $postData["id"] = "resumeTrigger";
        $postData["triggerName"] = filter_input(INPUT_POST, 'triggerName');
        $postData["triggerGroup"] = filter_input(INPUT_POST, 'triggerGroup');
        break;
    case 'pauseTrigger':
        $postData["id"] = "pauseTrigger";
        $postData["triggerName"] = filter_input(INPUT_POST, 'triggerName');
        $postData["triggerGroup"] = filter_input(INPUT_POST, 'triggerGroup');
        break;
    case 'startScheduler':
        $postData["id"] = "startScheduler";
        break;
    case 'standbyScheduler':
        $postData["id"] = "standbyScheduler";
        break;
    case 'shutdownScheduler':
        $postData["id"] = "shutdownScheduler";
        $postData["waitForJobsToComplete"] = "true";
        break;
    case 'forceShutdownScheduler':
        $postData["id"] = "shutdownScheduler";
        $postData["waitForJobsToComplete"] = "false";
        break;
    case 'clearScheduler':
        $postData["id"] = "clearScheduler";
        break;
    case 'pauseAll':
        $postData["id"] = "pauseAll";
        break;
    case 'resumeAll':
        $postData["id"] = "resumeAll";
        break;
    case 'truncateCatalinaLog':
        $postData["id"] = "truncateCatalinaLog";
        break;
    default:
        $result = "";
        break;
}

//CALL URL (post json data)
$jsonData["json"] = json_encode($postData);
$result = json_decode(postData($jsonData, $url), true);

//LOG
//$fp = fopen("/var/www/html/sce/log.txt", "at");
//fwrite($fp, "http://localhost:8080/SmartCloudEngine/?id=deleteJob&jobName=" . urlencode($_POST['jobName']) . "&jobGroup=" . urlencode($_POST['jobGroup']));
//fclose($fp);
echo $result[1];
?>