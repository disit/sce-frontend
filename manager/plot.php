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
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <script src="../sce/javascript/jquery-2.1.0.min.js"></script>
        <script type="text/javascript" src="../sce/javascript/jqplot/jquery.jqplot.min.js"></script>
        <script type="text/javascript" src="../sce/javascript/jqplot/plugins/jqplot.canvasTextRenderer.min.js"></script>
        <script type="text/javascript" src="../sce/javascript/jqplot/plugins/jqplot.canvasAxisLabelRenderer.min.js"></script>
        <script type="text/javascript" src="../sce/javascript/jqplot/plugins/jqplot.dateAxisRenderer.min.js"></script>
        <script type="text/javascript" src="../sce/javascript/jqplot/plugins/jqplot.pointLabels.min.js"></script>
        <script type="text/javascript" src="../sce/javascript/jqplot/plugins/jqplot.canvasOverlay.min.js"></script>
        <link rel="stylesheet" type="text/css" href="../sce/javascript/jqplot/jquery.jqplot.min.css">
    </head>

    <body>
        <div id="temperature_chart" style="height: 600px; width: 900px; position: relative;" class="jqplot-target"> </div>
    </body>

    <script class="code" type="text/javascript">
        var actual_temp_data = function () {
            return [["2014-04-05 07:00:00 -0400", 25, ""],
                ["2014-04-05 07:01:00 -0400", 26, ""],
                ["2014-04-05 07:02:00 -0400", 27, ""],
                ["2014-04-05 07:03:00 -0400", 29, ""],
                ["2014-04-05 07:04:00 -0400", 31, ""],
                ["2014-04-05 07:05:00 -0400", 33, ""],
                ["2014-04-05 07:06:00 -0400", 36, ""],
                ["2014-04-05 07:07:00 -0400", 40, ""],
                ["2014-04-05 07:08:00 -0400", 43, ""],
                ["2014-04-05 07:09:00 -0400", 47, ""],
                ["2014-04-05 07:10:00 -0400", 50, ""],
                ["2014-04-05 07:11:00 -0400", 52, "52 C"]];
        }
        var projected_temp_data = function () {
            return [["2014-04-05 07:11:00 -0400", 52, ""],
                ["2014-04-05 07:35:17 -0400", 100, "7:35:17 AM"]];
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
            var plot1 = $.jqplot('temperature_chart', [actual_temp_data(), projected_temp_data(), []], {
                title: "Process Temperature",
                legend: {
                    show: true,
                    labels: ["Actual Temperature", "Projected Temperature", "Target Temperature"]
                },
                seriesDefaults: {
                    showMarker: false,
                    pointLabels: {show: true},
                    rendererOption: {smooth: true}
                },
                canvasOverlay: {
                    show: true,
                    objects: [target_temp_dashed_line(100)],
                },
                axesDefaults: {
                    labelRenderer: $.jqplot.CanvasAxisLabelRenderer
                },
                axes: {
                    xaxis: {
                        label: "Time",
                        renderer: $.jqplot.DateAxisRenderer,
                        tickOptions: {formatString: '%I:%M:%S %p'},
                        numberTicks: 8,
                    },
                    yaxis: {
                        label: "Temperature C"
                    },
                },
            });
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
            coolTheme = plot1.themeEngine.newTheme('coolTheme', coolTheme);
            plot1.activateTheme('coolTheme');
        });
    </script>
</html> 
