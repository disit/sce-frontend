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
$config['tomcat'] = "localhost"; // ip of tomcat service
$config['host'] = "localhost"; //ip of database
$config['user'] = "username"; //db username
$config['pass'] = "password"; //db password
$config['database'] = "quartz"; //db name
$config['nicefields'] = true; //true or false | "Field Name" or "field_name"
$config['perpage'] = 20;
$config['pagelinks'] = 50; // max number of page links, if not set 50 will be used as a default value when calling $Pagination->showPageNumbers  
$config['showpagenumbers'] = true; //true or false
$config['showprevnext'] = true; //true or false
$config['refreshTime'] = 3000; //refresh time in ms for push mode views
/* * ******SPARQL******* */
$config['sparql_url'] = "http://192.168.0.1";
$config['sparql_username'] = "username";
$config['sparql_password'] = "password";
$config['sparql_ip'] = "192.168.0.1";
$config['log_service'] = "http://192.168.0.1/index.php";
/* * ******MongoDB******* */
$config['mongodb_host'] = "localhost";
$config['mongodb_port'] = 27017;
//error_reporting(0); //disable error reporting
?>
