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
include_once "header.php"; //include header
include_once "settings.php";
global $config;
?>
<html xmlns="http://www.w3.org/1999/xhtml"> 
    <head> 
        <title>Smart Cloud Engine</title> 
        <link rel="stylesheet" type="text/css" href="css/typography.css" />
        <link rel="stylesheet" type="text/css" href="../index.css" />
        <script type="text/javascript" src="javascript/jquery-2.1.0.min.js"></script>
        <script type="text/javascript">
            var global_sort_filters = ''; //used to store the global sort filters

            $(document).ready(function () {
                loadTable();
            });

            function loadTable() {
                $('#clusterHolder').load('cluster.php', function () {
                    var maxHeight = 0;
                    $(".cluster").each(function () {
                        maxHeight = Math.max(maxHeight, $(this).height());
                    });
                    $(".cluster").height(maxHeight);
                    setTimeout(loadTable, <?php echo $config['refreshTime']; ?>);
                });
            }
        </script>
    </head>
    <body>
        <div id="clusterHolder"></div>
    </body>
</html>