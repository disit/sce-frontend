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
<html>
    <head>
        <link rel="stylesheet" type="text/css" href="../sce/css/log.css" />
    </head>
    <body>
        <?php
        include ("../sce/header.php");
        include ("../sce/settings.php");
        //shows a LOG of a SLA, VM, HOST id 
        $id = filter_input(INPUT_GET, 'id');
        $log_url = $config['log_service'] . "?sparql=http://" . $config['sparql_username'] . ":" . $config['sparql_password'] . "@" . $config['sparql_ip'] . ":8080/IcaroKB/sparql" . "&uri=" . $id . "&embed&multiple_search=true&controls=true&description=false&info=false&translate=[0,0]&scale=(0.7)";
        $iframe = "<div id=\"container\"><iframe scrolling=\"no\" src=\"" . $log_url . "\" frameborder=\"1\"></iframe></div>";
        echo $iframe;
        ?>
    </body>
</html>