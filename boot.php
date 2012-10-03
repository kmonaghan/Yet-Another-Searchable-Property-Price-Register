<?php
date_default_timezone_set ('Europe/Dublin');

define('HOST', 'localhost');
define('DATABASE', 'propertyprices');
define('DB_USER', 'USERNAME');
define('DB_PASSWORD', 'PASSWORD');

$dbh = new PDO("mysql:host=" . HOST . ";dbname=" . DATABASE, DB_USER, DB_PASSWORD);
