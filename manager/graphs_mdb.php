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
//http://www.tutorialspoint.com/mongodb/mongodb_php.htm
//http://docs.mongodb.org/manual/reference/operator/update/setOnInsert
//http://docs.mongodb.org/manual/core/index-unique
//http://docs.mongodb.org/manual/core/index-compound/#index-type-compound
//CONNECT
$client = new MongoClient("mongodb://" . $config['mongodb_host'] . ":" . $config['mongodb_port']);
$collection = $client->sparql->collection;
$startTime = new MongoDate(time());
$endTime = new MongoDate(strtotime("+1 hour"));

// get the virtual machines of this sla
$sparql = "PREFIX%20icr%3A%3Chttp%3A%2F%2Fwww.cloudicaro.it%2Fcloud_ontology%2Fcore%23%3E%0Aselect%20%3FvmName%20where%20%7B%0A%20%20%20%3Fslm%20icr%3AdependsOn%20%3Fvm.%0A%20%20%20%3Fvm%20icr%3AhasName%20%3FvmName.%0A%20%20%20" . urlencode("<" . $_REQUEST['sla'] . ">") . "%20icr%3AhasSLObjective%20%5B%20%20%0A%20%20%20%20%20icr%3AhasSLMetric%20%5B%0A%20%20%20%20%20%20%20a%20icr%3AServiceLevelAndMetric%3B%0A%20%20%20%20%20%20%20icr%3AdependsOn%20%3Fslm%0A%20%20%20%20%20%5D%0A%20%20%20%5D.%0A%7D";
$result = getSPARQLResult($sparql); //in sce/functions.php
foreach ($result as $k1 => $v1) {
    foreach ($v1 as $key => $v2) {
        foreach ($v2 as $v3 => $value) {
            if ($v3 != 'type') {
                if (strpos($key, 'vm') !== false) {
                    $vms[$value] = "";
                }
            }
        }
    }
}

//GET DATA
if (isset($_REQUEST['startAt']) && isset($_REQUEST['endAt'])) {
    if (strtotime($_REQUEST['startAt']) < strtotime($_REQUEST['endAt'])) {
        $startTime = new MongoDate(strtotime($_REQUEST['startAt']));
        $endTime = new MongoDate(strtotime($_REQUEST['endAt']));
        $query = array('metric_timestamp' => array('$gte' => $startTime, '$lte' => $endTime));
        $cursor = $collection->find(array('metric_timestamp' => array('$gte' => $startTime, '$lte' => $endTime), 'sla' => $_REQUEST['sla']))->sort(array('metric_timestamp' => 1)); //->limit(1);
        $startAt = strtotime($_REQUEST['startAt']);
    } else {
        echo 'Start Time must be <= End Time';
        $error = "error";
    }
} else {
    $startTime = new MongoDate(strtotime("-24 hour"));
    $cursor = $collection->find(array('metric_timestamp' => array('$gte' => $startTime), 'sla' => $_REQUEST['sla']))->sort(array('metric_timestamp' => 1)); //->limit(1);
}

if (!isset($error)) {
    $data = array();
    $metric_unit_array = array();
    $threshold_array = array();
    foreach ($cursor as $doc) {
        $data[str_replace(' ', '_', $doc['metric_name'])][$doc['virtual_machine_name']][] = array(date('Y-m-d H:i:s', $doc['metric_timestamp']->sec), floatval($doc['value']), "");
        $metric_unit_array[str_replace(' ', '_', $doc['metric_name'])] = $doc['metric_unit'];
        $threshold_array[str_replace(' ', '_', $doc['metric_name'])] = $doc['threshold'];
    }
}
//echo json_encode($data['CPU_AVG_30min']);exit();
//get the javascript arrays to be used by jqplot
$i = 1;
$metric_divs = '';
if (!isset($error)) {
    foreach ($data as $key => $val) {
        $metric_name = $key;
        $metric_unit = $metric_unit_array[$metric_name];
        $threshold = $threshold_array[$metric_name];
        $metric_divs .= "<div id=" . $metric_name . " style='height: 600px; width: 900px; position: relative;' class='jqplot-target'></div>\n";
        foreach ($val as $k => $v) {
            $virtual_machine_names[$metric_name] .= "'" . $k . "', ";
            $plotData[$metric_name] .= json_encode($data[$metric_name][$k]) . ', ';
        }
        // add labels with a * prefix for vms that have not data in the selected time interval
        foreach ($vms as $vm => $val) {
            if (strpos($virtual_machine_names[$metric_name], $vm) === false) {
                $missing_vms .= "'*" . $vm . "', ";
                $plotData[$metric_name] .= "[], ";
            }
        }
        $plot .= getPlot($i, $plotData[$metric_name], $virtual_machine_names[$metric_name] . $missing_vms, $metric_name, $metric_unit, $threshold) . "\n";
        $i++;
    }
}

//get plots javascript
function getPlot($i, $plotData, $virtual_machine_names, $metric_name, $metric_unit, $threshold) {
    return "var plot" . $i . " = $.jqplot('" . $metric_name . "', [" . $plotData . "], {
                title: '$metric_name',
                legend: {
                    show: true,
                    labels: [" . substr($virtual_machine_names, 0, -2) . "],
                    renderer: $.jqplot.EnhancedLegendRenderer,
                    location: 'ne' ,
                    placement : 'outside',
                    rendererOptions: {
                        numberRows: 1
                    }
                },
                seriesDefaults: {
                    showMarker: false,
                    pointLabels: {show: true},
                    rendererOption: {smooth: true}
                },
                canvasOverlay: {
                    show: true,
                    objects: [target_temp_dashed_line($threshold)],
                },
                axesDefaults: {
                    labelRenderer: $.jqplot.CanvasAxisLabelRenderer,
                },
                axes: {
                    xaxis: {
                        //label: 'Time',
                        renderer: $.jqplot.DateAxisRenderer,
                        //tickOptions: {formatString: '%b %#d %H:%M:%S'},
                        tickRenderer: $.jqplot.CanvasAxisTickRenderer,
                        tickOptions: {
                            formatString: '%b %e %H:%M',
                            angle: -60
                        },
                    },
                    yaxis: {
                        label: '$metric_unit',
                        labelRenderer: $.jqplot.CanvasAxisLabelRenderer,
                        min: 0,
                        tickOptions: {formatString: '%.2f'},
                    },
                },
                highlighter: {
                    show: true,
                    tooltipLocation: 'ne',
                    tooltipAxes: 'xy',
                    useAxesFormatters: true,
                    formatString: '%s, %s',
                    sizeAdjust: 7.5
                },
                cursor: {
                    show: false
                }
            });";
}

// close all MongoDB connections
function closeMongoDBConnections($client) {
    $connections = $client->getConnections();
    foreach ($connections as $con) {
        // Loop over all the connections, and when the type is "SECONDARY"
        // we close the connection      
        echo "Closing '{$con['hash']}': ";
        $closed = $client->close($con['hash']);
        echo $closed ? "ok" : "failed", "\n";
    }
}

function getmicrotime() {
    list($usec, $sec) = explode(" ", microtime());
    return ((float) $usec + (float) $sec);
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <!-- imports for datepickers -->
        <script type="text/javascript" src="../sce/javascript/jquery-2.1.0.min.js"></script>
        <script type="text/javascript" src="../sce/javascript/jquery-ui.js"></script>
        <script type="text/javascript" src="../sce/javascript/jquery-ui-timepicker-addon.js"></script>
        <link rel="stylesheet" type="text/css" href="../sce/css/jquery-ui.css">
        <link rel="stylesheet" type="text/css" href="../sce/css/jquery-ui-timepicker-addon.css" />

        <script>
            $(function () {
                $("#datepicker1, #datepicker2").each(function () {
                    $(this).datetimepicker({
                        timeFormat: "HH:mm:ss",
                        dateFormat: "yy-mm-dd",
                        autoclose: true
                    });
                });
            });
        </script>

        <!-- imports for jqplot -->
        <script type="text/javascript" src="../sce/javascript/jqplot/jquery.jqplot.min.js"></script>
        <script type="text/javascript" src="../sce/javascript/jqplot/plugins/jqplot.canvasTextRenderer.min.js"></script>
        <script type="text/javascript" src="../sce/javascript/jqplot/plugins/jqplot.canvasAxisLabelRenderer.min.js"></script>
        <script type="text/javascript" src="../sce/javascript/jqplot/plugins/jqplot.canvasAxisTickRenderer.min.js"></script>
        <script type="text/javascript" src="../sce/javascript/jqplot/plugins/jqplot.dateAxisRenderer.min.js"></script>
        <script type="text/javascript" src="../sce/javascript/jqplot/plugins/jqplot.categoryAxisRenderer.min.js"></script>
        <script type="text/javascript" src="../sce/javascript/jqplot/plugins/jqplot.pointLabels.min.js"></script>
        <script type="text/javascript" src="../sce/javascript/jqplot/plugins/jqplot.canvasOverlay.min.js"></script>
        <script type="text/javascript" src="../sce/javascript/jqplot/plugins/jqplot.highlighter.min.js"></script>
        <script type="text/javascript" src="../sce/javascript/jqplot/plugins/jqplot.cursor.min.js"></script>
        <script type="text/javascript" src="../sce/javascript/jqplot/plugins/jqplot.pointLabels.min.js"></script>
        <script type="text/javascript" src="../sce/javascript/jqplot/plugins/jqplot.dateAxisRenderer.min.js"></script>
        <link rel="stylesheet" type="text/css" href="../sce/javascript/jqplot/jquery.jqplot.min.css">
    </head>

    <body>
        <!-- date pickers form-->
        <form action="" method="POST">
            <p><b title="Set the start time of the graph">Start At: </b><input type="text" name="startAt" value="<?php echo (isset($_REQUEST['startAt']) ? $_REQUEST['startAt'] : date("Y-m-d H:i:s", round(microtime(true)) - 3600)) ?>" id="datepicker1" />
                <b title="Set the end time of the graph">End At: </b><input type="text" name="endAt" value="<?php echo (isset($_REQUEST['endAt']) ? $_REQUEST['endAt'] : date("Y-m-d H:i:s", round(microtime(true)))) ?>" id="datepicker2" />
                <input type="hidden" value="<?php echo $_REQUEST['sla']; ?>" name="sla">
                <input name=confirm" type="submit" value="Plot" /></p>
        </form>
        <?php
        echo $metric_divs;
        ?>
    </body>

    <script class="code" type="text/javascript">
            var target_temp_dashed_line = function (target_temp) {
                return {dashedHorizontalLine: {
                        name: 'Boiling Pt',
                        y: target_temp,
                        lineWidth: 3,
                        color: '#EF3E42',
                        shadow: false
                    }
                };
            }
            $(document).ready(function () {
        <?php echo $plot; ?>
                //theme
                var coolTheme = {
                    legend: {
                        location: 'se',
                    },
                    title: {
                        textColor: '#002225',
                        fontSize: '25',
                    },
                    series: [
                        {color: '#00AAA1', lineWidth: 5, markerOptions: {show: true}},
                        {color: '#79ccc7', lineWidth: 2, linePattern: 'dashed'},
                        {color: '#EF3E42'},
                    ],
                    grid: {
                        backgroundColor: '#E3E7EA',
                        gridLineColor: '#002225'
                    },
                };
                //coolTheme = plot1.themeEngine.newTheme('coolTheme', coolTheme);
                //plot1.activateTheme('coolTheme');
            });
    </script>
</html>
