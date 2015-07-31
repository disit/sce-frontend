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
//CONNECT
$link = mysqli_connect($config['host'], $config['user'], $config['pass'], $config['database']);

/* check connection */
if (mysqli_connect_errno()) {
    printf("Connection failed: %s\n", mysqli_connect_error());
    exit();
}

//GET DATA
if (isset($_GET['startAt']) && isset($_GET['endAt'])) {
    if (strtotime($_GET['startAt']) < strtotime($_GET['endAt'])) {
        $sql = "SELECT metric_timestamp, metric_unit, value, threshold, virtual_machine_name FROM quartz.QRTZ_SPARQL WHERE sla = '" . $_GET['sla'] . "' AND virtual_machine = '" . $_GET['virtual_machine'] . "' AND metric_name = '" . $_GET['metric_name'] . "' AND metric_timestamp >= '" . $_GET['startAt'] . "' AND metric_timestamp <= '" . $_GET['endAt'] . "' ORDER BY metric_timestamp ASC";
    } else {
        echo 'Start Time must be <= End Time';
        $error = "error";
    }
} else {
    $sql = "SELECT metric_timestamp, metric_unit, value, threshold, virtual_machine_name FROM quartz.QRTZ_SPARQL WHERE sla = '" . $_GET['sla'] . "' AND virtual_machine = '" . $_GET['virtual_machine'] . "' AND metric_name = '" . $_GET['metric_name'] . "' AND metric_timestamp >= NOW() - INTERVAL 1 HOUR ORDER BY metric_timestamp ASC";
}

if (!isset($error)) {
    $result = mysqli_query($link, $sql) or die(mysqli_error());
    $plotData = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $virtual_machine_name = $row['virtual_machine_name'];
        $plotData[] = array($row['metric_timestamp'], floatval($row['value']), "");
        $current_timestamp = $row['metric_timestamp'];
        $threshold = $row['threshold'];
        $metric_unit = $row['metric_unit'];
    }
    //$projectedData = array();
    //$projectedData[] = array($plotData[count($plotData) - 1][0], $plotData[count($plotData) - 1][1], "");
    //$date = strtotime($plotData[count($plotData) - 1][0]) + 7200;
    //$date = date('Y-m-d H:i:s', $date);
    //$projectedData[] = array($date, $plotData[count($plotData) - 1][1], "");
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
        <form action="" method="GET">
            <p><b title="Set the start time of the graph">Start At: </b><input type="text" name="startAt" value="<?php echo (isset($_GET['startAt']) ? $_GET['startAt'] : date("Y-m-d H:i:s", round(microtime(true)) - 3600)) ?>" id="datepicker1" />
                <b title="Set the end time of the graph">End At: </b><input type="text" name="endAt" value="<?php echo (isset($_GET['endAt']) ? $_GET['endAt'] : date("Y-m-d H:i:s", round(microtime(true)))) ?>" id="datepicker2" />
                <input type="hidden" value="<?php echo $_GET['sla']; ?>" name="sla">
                <input type="hidden" value="<?php echo $_GET['virtual_machine']; ?>" name="virtual_machine">
                <input type="hidden" value="<?php echo $_GET['metric_name']; ?>" name="metric_name">
                <input name=confirm" type="submit" value="Plot" /></p>
        </form>
        <div id="<?php echo str_replace(" ", "_", $_REQUEST['metric_name']); ?>" style="height: 600px; width: 1200px; position: relative;" class="jqplot-target"> </div>
        <?php echo '<a href="metric_alert.php?sla=' . $_REQUEST['sla'] . '&virtual_machine=' . $_REQUEST['virtual_machine'] . '&metric_name=' . $_REQUEST['metric_name'] . '&startAt=' . urlencode($_REQUEST['startAt']) . '&endAt=' . urlencode($_REQUEST['endAt']) . '">View Metric Alerts</a>'; ?>
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
                var plot1 = $.jqplot('<?php echo str_replace(" ", "_", $_GET['metric_name']); ?>', [actual_data()], {
                    title: '<?php echo $_GET['metric_name'] . ' (' . $virtual_machine_name . ')'; ?>',
                    legend: {
                        show: true,
                        labels: ["<?php echo $virtual_machine_name; ?>"],
                        renderer: $.jqplot.EnhancedLegendRenderer,
                        location: 'e',
                        showSwatch: true,
                        placement: 'outsideGrid',
                        rendererOptions: {
                            numberRows: 1
                        }
                    },
                    seriesDefaults: {
                        showMarker: true,
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
