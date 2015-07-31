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

// get the virtual machines of this sla
$sql = "SELECT DISTINCT(virtual_machine_name) FROM quartz.QRTZ_SPARQL WHERE sla='" . $_REQUEST['sla'] . "'";
$result = mysqli_query($link, $sql) or die(mysqli_error());
while ($row = mysqli_fetch_assoc($result)) {
    $vms[$row['virtual_machine_name']] = "";
}

//GET DATA
if (isset($_REQUEST['startAt']) && isset($_REQUEST['endAt'])) {
    if (strtotime($_REQUEST['startAt']) < strtotime($_REQUEST['endAt'])) {
        $sql = "SELECT metric_timestamp, virtual_machine_name, metric_name, metric_unit, value, threshold FROM quartz.QRTZ_SPARQL WHERE sla = '" . $_REQUEST['sla'] . "' AND metric_timestamp >= '" . $_REQUEST['startAt'] . "' AND metric_timestamp <= '" . $_REQUEST['endAt'] . "' ORDER BY metric_timestamp ASC";
        $startAt = strtotime($_REQUEST['startAt']);
    } else {
        echo 'Start Time must be <= End Time';
        $error = "error";
    }
} else {
    $sql = "SELECT metric_timestamp, virtual_machine_name, metric_name, metric_unit, value, threshold FROM quartz.QRTZ_SPARQL WHERE sla = '" . $_REQUEST['sla'] . "' AND metric_timestamp >= NOW() - INTERVAL 1 HOUR ORDER BY metric_timestamp ASC";
    $startAt = round(microtime(true)) - 3600;
}

if (!isset($error)) {
    $result = mysqli_query($link, $sql) or die(mysqli_error());
    $data = array();
    $metric_unit_array = array();
    $threshold_array = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $data[str_replace(' ', '_', $row['metric_name'])][$row['virtual_machine_name']][] = array($row['metric_timestamp'], floatval($row['value']), "");
        $metric_unit_array[str_replace(' ', '_', $row['metric_name'])] = $row['metric_unit'];
        $threshold_array[str_replace(' ', '_', $row['metric_name'])] = $row['threshold'];
    }
}

//get the javascript arrays to be used by jqplot
$i = 1;
$metric_divs = '';
if (!isset($error)) {
    foreach ($data as $key => $val) {
        $metric_name = $key;
        $metric_unit = $metric_unit_array[$metric_name];
        $threshold = $threshold_array[$metric_name];
        $metric_divs .= "<div id=" . $metric_name . " style='height: 600px; width: 1200px; position: relative;' class='jqplot-target'></div>\n";
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
                    location: 'e' ,
                    showSwatch: true,
                    placement: 'outsideGrid',
                    rendererOptions: {
                        numberRows: 1
                    }
                },
                seriesDefaults: {
                    showMarker: true,
                    pointLabels: {show: true},
                    rendererOptions: {smooth: false}
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
        <!--<style media="screen" type="text/css">
            table.jqplot-table-legend{width:100% !important;}
            td.jqplot-table-legend jqplot-table-legend-label{width:10px !important;}
            div.jqplot-table-legend-swatch {width:100% !important;}
            .jqplot-table-legend-label {padding-left:10px !important;}
        </style>-->

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
