<?php
include 'boot.php';
include 'search.class.php';
	
$search = new Search();
$search->hydrate($_GET);
$results = $search->search();	

header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');

echo json_encode($results['results']);