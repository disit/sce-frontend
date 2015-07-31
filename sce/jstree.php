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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"> 
    <head> 
        <title>Job Tree</title> 
        <script src="javascript/jstree/jquery.js" type="text/javascript"></script>
        <script src="javascript/jstree/require.js" type="text/javascript"></script>
        <link rel = "stylesheet" href = "javascript/jstree/themes/default/style.min.css" />
        <script src="javascript/jstree/jstree.min.js" type="text/javascript"></script>
    </head>
    <body>
        <div id="selector">
            <ul><li id="blog"><a href="#">Blog</a>
                    <ul><li id="articulos"><a href="#">Articulos</a>
                            <ul>
                                <li id="articulo1"><a href="#">Articulo 1</a></li>
                                <li id="articulo2"><a href="#">Articulo 2</a></li>
                            </ul>
                            <li id="tutorial"><a href="#">Tutorial</a>
                                <ul>
                                    <li id="articulo3"><a href="#">Tutorial 1</a></li>
                                    <li id="articulo4"><a href="#">Tutorial 2</a></li>
                                </ul>
                            </li></ul>
                </li></ul>
        </div>
        <script>
            $(document).ready(function () {
                $("#selector").jstree();
            });
        </script>
        <?php
        ?>
    </body>
</html>