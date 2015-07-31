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
include_once "settings.php";

$ipServices = array("http://ipecho.net/plain",
    "http://ident.me",
    "http://icanhazip.com");

/* function url_exists($url) {
  $headers = @get_headers($url);
  return is_array($headers) ? preg_match('/^HTTP\/\d+\.\d+\s+2\d\d\s+.*$/',$headers[0]) : 0;
  } */

function url_exists($url) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 2);
    curl_setopt($curl, CURLOPT_NOBODY, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $data = curl_exec($curl);
    curl_close($curl);
    preg_match("/HTTP\/1\.[1|0]\s(\d{3})/", $data, $matches);
    return ($matches[1] == 200);
}

function getPublicIP() {
    $ip = "";
    foreach ($ipServices as $value) {
        if (url_exists($value)) {
            $ip = file_get_contents($value);
            break;
        }
    }
    return $ip;
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

/* * ******SPARQL******* */

//get total number of SLAs (SPARQL query)
function getNumberOfSLAs() {
    global $config;
    $query = "PREFIX%20icr%3A%3Chttp%3A%2F%2Fwww.cloudicaro.it%2Fcloud_ontology%2Fcore%23%3E%0Aselect%20(COUNT(DISTINCT%20%3FSLA_Job)%20AS%20%3Fcount)%20where%20%7B%0A%20%20%20%3FSLA_Job%20a%20icr%3AServiceLevelAgreement.%0A%7D";
    $service_url = $config['sparql_url'] . "?query=" . $query;
    $curl = curl_init($service_url);
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($curl, CURLOPT_USERPWD, $config['sparql_username'] . ":" . $config['sparql_password']);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/sparql-results+json, application/rdf+json, application/json'));
    $curl_response = curl_exec($curl);
    $response = objectToArray(json_decode($curl_response));
    curl_close($curl);
    return $response["results"]["bindings"][0]["count"]["value"];
}

//get total number of Metrics (SPARQL query)
function getNumberOfMetrics() {
    global $config;
    $query = "PREFIX%20icr%3A%3Chttp%3A%2F%2Fwww.cloudicaro.it%2Fcloud_ontology%2Fcore%23%3E%0ASELECT%20(COUNT(%3Fsla)%20AS%20%3Fcount)%20where%20%7B%0A%20%20%20%3Fsla%20icr%3AhasSLObjective%20%5B%0A%20%20%20%20%20icr%3AhasSLAction%20%5B%20icr%3AcallUrl%20%3Fact%20%5D%3B%0A%20%20%20%20%20icr%3AhasSLMetric%20%5B%0A%20%20%20%20%20%20%20a%20icr%3AServiceLevelAndMetric%3B%0A%20%20%20%20%20%20%20icr%3AdependsOn%20%3Fx%0A%20%20%20%20%20%5D%0A%20%20%20%5D.%0A%7D";
    $service_url = $config['sparql_url'] . "?query=" . $query;
    $curl = curl_init($service_url);
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($curl, CURLOPT_USERPWD, $config['sparql_username'] . ":" . $config['sparql_password']);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/sparql-results+json, application/rdf+json, application/json'));
    $curl_response = curl_exec($curl);
    $response = objectToArray(json_decode($curl_response));
    curl_close($curl);
    return $response["results"]["bindings"][0]["count"]["value"];
}

//get total number of Hosts (SPARQL query)
function getNumberOfHosts() {
    global $config;
    $query = "PREFIX%20icr%3A%3Chttp%3A%2F%2Fwww.cloudicaro.it%2Fcloud_ontology%2Fcore%23%3E%0ASELECT%20(COUNT(DISTINCT%20(%3Fhm1))%20AS%20%3Fcount)%20WHERE%20%7B%0A%20%3Fhm1%20a%20icr%3AHostMachine.%0A%20%3Fhm1%20icr%3AhasNetworkAdapter%20%3Fna1.%0A%20%3Fna1%20icr%3AhasIPAddress%20%3Fip1.%0A%20%3Fna1%20icr%3AboundToNetwork%20%3Fnetwork.%0A%7D";
    $service_url = $config['sparql_url'] . "?query=" . $query;
    $curl = curl_init($service_url);
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($curl, CURLOPT_USERPWD, $config['sparql_username'] . ":" . $config['sparql_password']);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/sparql-results+json, application/rdf+json, application/json'));
    $curl_response = curl_exec($curl);
    $response = objectToArray(json_decode($curl_response));
    curl_close($curl);
    return $response["results"]["bindings"][0]["count"]["value"];
}

//get total number of VMs (SPARQL query)
function getNumberOfVMs() {
    global $config;
    $query = "PREFIX%20icr%3A%3Chttp%3A%2F%2Fwww.cloudicaro.it%2Fcloud_ontology%2Fcore%23%3E%0Aselect%20(count(%3Fvm1)%20as%20%3Fcount)%20where%20%7B%0A%20%3Fvm1%20a%20icr%3AVirtualMachine.%0A%20%3Fvm1%20icr%3AisPartOf%20%3Fhm1.%0A%20%7B%0A%20%3Fhm1%20a%20icr%3AHostMachine.%0A%20%7D%20UNION%20%7B%0A%20%3Fhm1%20a%20icr%3AHostMachineCluster.%0A%20%7D%0A%20%3Fvm1%20icr%3AhasNetworkAdapter%20%3Fna1.%0A%20%3Fna1%20icr%3AhasIPAddress%20%3Fip1.%0A%20%3Fvm1%20icr%3AhasName%20%3FvmName.%0A%7D";
    $service_url = $config['sparql_url'] . "?query=" . $query;
    $curl = curl_init($service_url);
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($curl, CURLOPT_USERPWD, $config['sparql_username'] . ":" . $config['sparql_password']);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/sparql-results+json, application/rdf+json, application/json'));
    $curl_response = curl_exec($curl);
    $response = objectToArray(json_decode($curl_response));
    curl_close($curl);
    return $response["results"]["bindings"][0]["count"]["value"];
}

//get the result (associative array) of a SPARQL query
function getSPARQLResult($query, $sort, $orderby, $startrow, $limit) {
    /* PREFIX icr:<http://www.cloudicaro.it/cloud_ontology/core#>
      select * where {
      ?sla icr:hasSLObjective [
      icr:hasSLAction [ icr:callUrl ?act ];
      icr:hasSLMetric [
      a icr:ServiceLevelAndMetric;
      icr:dependsOn ?x
      ]
      ].
      } */
    global $config;
    if (isset($orderby) && isset($sort) && isset($startrow) && isset($limit)) {
        $query = $query . "%20ORDER%20BY%20" . $sort . "(%3F" . $orderby . ")%20LIMIT%20" . $limit . "%20OFFSET%20" . $startrow;
    }
    $service_url = $config['sparql_url'] . "?query=" . $query;
    $curl = curl_init($service_url);
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($curl, CURLOPT_USERPWD, $config['sparql_username'] . ":" . $config['sparql_password']);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/sparql-results+json, application/rdf+json, application/json'));
    $curl_response = curl_exec($curl);
    $response = objectToArray(json_decode($curl_response));
    curl_close($curl);
    return $response["results"]["bindings"];
}

?>