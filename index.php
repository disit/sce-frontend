<?php /* Smart Cloud Engine Web Interface
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
<html xmlns="http://www.w3.org/1999/xhtml"> 
    <head> 
        <title>Smart Cloud Engine</title> 
        <link rel="stylesheet" type="text/css" href="sce/css/typography.css" />
        <link rel="stylesheet" type="text/css" href="index.css" />
    </head>
    <body>
        <?php
        //header("location: sce"); 
        include_once "sce/header.php";
        ?>

        <ul class="rig columns-3">
            <li>
                <a href="manager/host.php">
                    <h3><img src="sce/images/bladeserver.png" /><br>Hosts</h3></a>
            </li>
            <li>
                <a href="manager/vm.php">
                    <h3><img src="sce/images/vm.png" /><br>VMs</h3></a>
            </li>
            <li>
                <a href="manager/sla.php">
                    <h3><img src="sce/images/sla.png" /><br>SLA Alerts</h3></a>
            </li>
            <li>
                <a href="manager/application.php">
                    <h3><img src="sce/images/app.png" /><br>Apps</h3></a>
            </li>
            <li>
                <a href="manager/metric.php">
                    <h3><img src="sce/images/metric.png" /><br>Metrics</h3></a>
            </li>
            <li>
                <a href="manager/alerts.php">
                    <h3><img src="sce/images/graph.png" /><br>Alerts</h3></a>
            </li>
            <li>
                <a href="manager/host.php">
                    <h3><img src="sce/images/network.png" /><br>NICs</h3></a>
            </li>
            <li>
                <a href="sce/index.php">
                    <h3><img src="sce/images/scheduler.png" /><br>Scheduler</h3></a>
            </li>
            <li>
                <a href="sce/cluster.php">
                    <h3><img src="sce/images/cluster.png" /><br>Cluster</h3></a>
            </li>
        </ul>
    </body>
</html>