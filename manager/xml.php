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
        <title>Database Table View</title> 
        <link rel="stylesheet" type="text/css" href="../sce/css/reset.css"/>
        <link rel="stylesheet" type="text/css" href="../sce/css/style.css"/>
        <link rel="stylesheet" type="text/css" href="../sce/css/typography.css"/>
        <link rel="stylesheet" type="text/css" href="../sce/css/jquery-ui.css"/>
        <script type="text/javascript" src="../sce/javascript/sce.js"></script>
        <script type="text/javascript" src="../sce/javascript/jquery-2.1.0.min.js"></script>
        <script type="text/javascript" src="../sce/javascript/jquery-ui.min.js"></script>
    </head>
    <body>
        <?php
        include_once "../sce/settings.php";
        include_once "../sce/functions.php";

        function print_array($arr) {
            foreach ($arr as $key => $val) {
                if (is_array($val))
                    print_array($val);
                else
                    echo '<p>key: ' . $key . ' | value: ' . $val . '</p>';
                //echo "<tr class='odd'><td>$key</td><td>$val</td></tr>";
            }
        }

        //$query = "PREFIX%20%3A%3Chttp%3A%2F%2Fwww.w3.org%2F2002%2F07%2Fowl%23%3E%0APREFIX%20app%3A%3Chttp%3A%2F%2Fwww.cloudicaro.it%2Fcloud_ontology%2Fapplications%23%3E%0APREFIX%20rdfs%3A%3Chttp%3A%2F%2Fwww.w3.org%2F2000%2F01%2Frdf-schema%23%3E%0APREFIX%20icr%3A%3Chttp%3A%2F%2Fwww.cloudicaro.it%2Fcloud_ontology%2Fcore%23%3E%0APREFIX%20foaf%3A%3Chttp%3A%2F%2Fxmlns.com%2Ffoaf%2F0.1%2F%3E%0APREFIX%20ns0%3A%3Chttp%3A%2F%2Fwww.w3.org%2F1999%2F02%2F22-rdf-syntax-ns%23%3E%0APREFIX%20xsd%3A%3Chttp%3A%2F%2Fwww.w3.org%2F2001%2FXMLSchema%23%3E%0APREFIX%20owl%3A%3Chttp%3A%2F%2Fwww.w3.org%2F2002%2F07%2Fowl%23%3E%0APREFIX%20rdf%3A%3Chttp%3A%2F%2Fwww.w3.org%2F1999%2F02%2F22-rdf-syntax-ns%23%3E%0APREFIX%20xsi%3A%3Chttp%3A%2F%2Fwww.w3.org%2F2001%2FXMLSchema-instance%3E%0Aselect%20*%20where%20%7B%0A%20%20%20%3Fsla%20icr%3AhasSLObjective%20%5B%0A%20%20%20%20%20icr%3AhasSLAction%20%5B%20icr%3AcallUrl%20%3Fact%20%5D%3B%0A%20%20%20%20%20icr%3AhasSLMetric%20%5B%0A%20%20%20%20%20%20%20a%20icr%3AServiceLevelAndMetric%3B%0A%20%20%20%20%20%20%20icr%3AdependsOn%20%3Fx%0A%20%20%20%20%20%5D%0A%20%20%20%5D.%0A%20%20%20%3Fx%20%20icr%3AhasMetricName%20%3Fmn%3B%0A%20%20%20%20%20%20%20icr%3AdependsOn%20%3Fsm.%0A%20%20%20%7B%3Fx%20icr%3AhasMetricValueLessThan%20%3Fv.%7D%20UNION%0A%20%20%20%7B%3Fx%20icr%3AhasMetricValueGreaterThan%20%3Fv%7D%20UNION%0A%20%20%20%7B%3Fx%20icr%3AhasMetricValue%20%3Fv%7D%0A%20%20%20%3Fx%20%3Fp%20%3Fv%0A%7D";
        //var_dump($response["results"]);
        echo "<table>";
        //print_array($response["results"]);
        //echo "</table>";
        $sparql = "PREFIX%20icr%3A%3Chttp%3A%2F%2Fwww.cloudicaro.it%2Fcloud_ontology%2Fcore%23%3E%20%0APREFIX%20xsd%3A%3Chttp%3A%2F%2Fwww.w3.org%2F2001%2FXMLSchema%23%3E%20select%20DISTINCT%20%3Fsla%20where%20%7B%0A%20%20%20%20%20%20%20%20%20%20%20%20%3Fsla%20icr%3AhasSLObjective%20%5B%0A%20%20%20%20%20%20%20%20%20%20%20%20icr%3AhasSLAction%20%5B%20icr%3AcallUrl%20%3Fact%20%5D%3B%0A%20%20%20%20%20%20%20%20%20%20%20%20icr%3AhasSLMetric%20%5B%0A%20%20%20%20%20%20%20%20%20%20%20%20a%20icr%3AServiceLevelAndMetric%3B%0A%20%20%20%20%20%20%20%20%20%20%20%20icr%3AdependsOn%20%3Fx%0A%20%20%20%20%20%20%20%20%20%20%20%20%5D%0A%20%20%20%20%20%20%20%20%20%20%20%20%5D.%0A%20%20%20%20%20%20%20%20%20%20%20%20%3Fx%20%20icr%3AhasMetricName%20%3Fmn%3B%0A%20%20%20%20%20%20%20%20%20%20%20%20icr%3AdependsOn%20%3Fsm.%0A%20%20%20%20%20%20%20%20%20%20%20%20%7B%3Fx%20icr%3AhasMetricValueLessThan%20%3Fv.%7D%20UNION%0A%20%20%20%20%20%20%20%20%20%20%20%20%7B%3Fx%20icr%3AhasMetricValueGreaterThan%20%3Fv%7D%20UNION%0A%20%20%20%20%20%20%20%20%20%20%20%20%7B%3Fx%20icr%3AhasMetricValue%20%3Fv%7D%0A%20%20%20%20%20%20%20%20%20%20%20%20%3Fx%20%3Fp%20%3Fv.%0A%20%20%20%20%20%20%20%20%20%20%20%20%7D";
        $result = getSPARQLResult($sparql);
        //var_dump($result);
        //var_dump($result);
        foreach ($result as $k1 => $v1)
            foreach ($v1 as $k2 => $v2)
                foreach ($v2 as $v3 => $k3)
                    if ($v3 != 'type' && $v3 != 'datatype')
                        echo $k2 . " " . $k3 . " <br>";

        /* $result = file_get_contents('http://data.fcc.gov/api/license-view/basicSearch/getLicenses?searchValue=Verizon+Wireless');
          $result = file_get_contets('http://192.168.0.106:8080/IcaroKB/sparql?query=' . $query, false, context);
          $result = new SimpleXMLElement($xml);
          $result = simplexml_load_file($xml);
          echo '<pre>';
          print_r($movies[]);
          echo '<pre>'; */
        ?>
    </body>
</html>