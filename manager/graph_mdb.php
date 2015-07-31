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
//http://www.tutorialspoint.com/mongodb/mongodb_php.htm
//http://docs.mongodb.org/manual/reference/operator/update/setOnInsert
//http://docs.mongodb.org/manual/core/index-unique
//http://docs.mongodb.org/manual/core/index-compound/#index-type-compound
//CONNECT
$client = new MongoClient("mongodb://" . $config['mongodb_host'] . ":" . $config['mongodb_port']);
$collection = $client->sparql->collection;
$startTime = new MongoDate(time());
$endTime = new MongoDate(strtotime("+1 hour"));

//GET DATA
if (isset($_REQUEST['startAt']) && isset($_REQUEST['endAt'])) {
    if (strtotime($_REQUEST['startAt']) < strtotime($_REQUEST['endAt'])) {
        $startTime = new MongoDate(strtotime($_REQUEST['startAt']));
        $endTime = new MongoDate(strtotime($_REQUEST['endAt']));
        $query = array('metric_timestamp' => array('$gte' => $startTime, '$lte' => $endTime));
        $cursor = $collection->find(array('metric_timestamp' => array('$gte' => $startTime, '$lte' => $endTime), 'sla' => $_REQUEST['sla'], 'virtual_machine' => $_REQUEST['virtual_machine'], 'metric_name' => $_REQUEST['metric_name']))->sort(array('metric_timestamp' => 1)); //->limit(1);
        $startAt = strtotime($_REQUEST['startAt']);
    } else {
        echo 'Start Time must be <= End Time';
        $error = "error";
    }
} else {
    $startTime = new MongoDate(strtotime("-24 hour"));
    $cursor = $collection->find(array('metric_timestamp' => array('$gte' => $startTime), 'sla' => $_REQUEST['sla'], 'virtual_machine' => $_REQUEST['virtual_machine'], 'metric_name' => $_REQUEST['metric_name']))->sort(array('metric_timestamp' => 1)); //->limit(1);
}

if (!isset($error)) {
    $plotData = array();
    foreach ($cursor as $doc) {
        $virtual_machine_name = $doc['virtual_machine_name'];
        $plotData[] = array(date('Y-m-d H:i:s', $doc['metric_timestamp']->sec), floatval($doc['value']), "");
    }
    $threshold = $doc['threshold'];
    $metric_unit = $doc['metric_unit'];
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
                <input type="hidden" value="<?php echo $_REQUEST['virtual_machine']; ?>" name="virtual_machine">
                <input type="hidden" value="<?php echo $_REQUEST['metric_name']; ?>" name="metric_name">
                <input name=confirm" type="submit" value="Plot" /></p>
        </form>
        <div id="<?php echo str_replace(" ", "_", $_REQUEST['metric_name']); ?>" style="height: 600px; width: 900px; position: relative;" class="jqplot-target"> </div>
    </body>

    <script class="code" type="text/javascript">
            var actual_data = function () {
                return <?php echo json_encode($plotData); ?>;
            }
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
                //var plot1 = $.jqplot('temperature_chart', [actual_temp_data(), projected_temp_data(), []], {
                var plot1 = $.jqplot('<?php echo str_replace(" ", "_", $_REQUEST['metric_name']); ?>', [actual_data()], {
                    title: '<?php echo $_REQUEST['metric_name'] . ' (' . $virtual_machine_name . ')'; ?>',
                    legend: {
                        show: true,
                        labels: ["<?php echo $virtual_machine_name; ?>"],
                        renderer: $.jqplot.EnhancedLegendRenderer,
                        location: 'ne',
                        placement: 'outside',
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
                        objects: [target_temp_dashed_line(<?php echo floatval($threshold) ?>)],
                    },
                    axesDefaults: {
                        labelRenderer: $.jqplot.CanvasAxisLabelRenderer,
                        /*tickRenderer: $.jqplot.CanvasAxisTickRenderer,
                         tickOptions: {
                         angle: -45
                         }*/
                    },
                    axes: {
                        xaxis: {
                            label: "Time",
                            renderer: $.jqplot.DateAxisRenderer,
                            //tickOptions: {formatString: '%b %#d %H:%M:%S'},
                            tickRenderer: $.jqplot.CanvasAxisTickRenderer,
                            tickOptions: {
                                formatString: '%b %e %H:%M',
                                angle: -60
                            },
                            //numberTicks: 8,
                        },
                        yaxis: {
                            label: '<?php echo $metric_unit; ?>',
                            labelRenderer: $.jqplot.CanvasAxisLabelRenderer,
                            min: 0,
                            tickOptions: {formatString: "%.2f"},
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
                });
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
