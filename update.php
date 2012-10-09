<?php
include 'boot.php';
include 'update.class.php';

$update = new Update();
$update->hydrate($_REQUEST);
$results = $update->update();
$results['break'] = "Something happens";

$callback = $_REQUEST["callback"];

header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');

echo $callback. '('. json_encode($results) . ')';