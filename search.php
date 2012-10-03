<?php
include 'boot.php';

$limit = (is_numeric($_GET['limit']) && $_GET['limit'] > 0) ? $_GET['limit'] : 10;
$offset = (is_numeric($_GET['page']) && $_GET['page'] > 0) ? $limit * ($_GET['page'] - 1) : 0;

$params = array();

$whereArray = array();

$urlArray = array();

$having = '';
$havingSelect = '';
if (isset($_GET['lat']))
{
	$havingSelect = ', ( 6371 * acos( cos( radians(?) ) * cos( radians( lat ) ) * cos( radians( lng ) - radians(?) ) + sin( radians(?) ) * sin( radians( lat ) ) ) ) as distance ';
	$params[] = $_GET['lat'];
	$params[] = $_GET['lng'];
	$params[] = $_GET['lat'];
	
	$having = ' having distance < 10';
}

if (isset($_GET['address']) && trim($_GET['address']))
{
	$params[] = '%' . trim($_GET['address']) . '%';
	$whereArray[] = ' address LIKE ?';
	
	$urlArray[] = 'address=' . trim($_GET['address']);
}

if (isset($_GET['from_price']) && (is_numeric($_GET['from_price'])))
{
	$params[] = $_GET['from_price'];
	$whereArray[] = ' price >= ?';
	
	$urlArray[] = 'from_price=' . trim($_GET['from_price']);
}

if (isset($_GET['to_price']) && (is_numeric($_GET['to_price'])))
{
	$params[] = $_GET['to_price'];
	$whereArray[] = ' price < ?';
	
	$urlArray[] = 'to_price=' . trim($_GET['to_price']);
}

if (isset($_GET['house_type']))
{
	$whereArray[] = ' description_of_property = ' . (($_GET['house_type'] == 1) ? '"New Dwelling house /Apartment"' : '"Second-Hand Dwelling house /Apartment"') ;
	
	$urlArray[] = 'house_type=' . trim($_GET['house_type']);
}

if (count($_GET['postal_code']))
{
	$postalCode = ' postal_code IN (';
	
	foreach ($_GET['postal_code'] as $code)
	{
		$postalCode .= '?,';
		$params[] = $code;
		
		$urlArray[] = 'postal_code[]=' . $code;
	}
	
	$postalCode = rtrim($postalCode, ',');
	$postalCode .= ')';
	
	$whereArray[] = $postalCode;
}
else if (count($_GET['county']))
{
	$county = ' county IN (';
	
	foreach ($_GET['county'] as $code)
	{
		$county .= '?,';
		$params[] = $code;
		$urlArray[] = 'county[]=' . $code;
	}
	
	$county = rtrim($county, ',');
	$county .= ')';	
	
	$whereArray[] = $county;
}

if (isset($_GET['full_price']))
{
	$whereArray[] = ' not_full_market_price = 0';
	
	$urlArray[] = 'full_price=1';
}

if (isset($_GET['start_date']))
{
	$parts = explode('-', $_GET['start_date']);
	
	$params[] = "$parts[2]-$parts[1]-$parts[0]";
	$whereArray[] = 'date_of_sale >= ?';
	
	$urlArray[] = 'start_date=' . trim($_GET['start_date']);
}

if (isset($_GET['end_date']))
{
	$parts = explode('-', $_GET['end_date']);
	
	$params[] = "$parts[2]-$parts[1]-$parts[0]";
	$whereArray[] = 'date_of_sale <= ?';
	
	$urlArray[] = 'start_date=' . trim($_GET['start_date']);
}

$url = 'index.php?' . implode('&', $urlArray);
$whereString = (count($whereArray)) ? ' WHERE ' . implode(' AND ', $whereArray) : '';
//echo $whereString;
//echo "SELECT * $havingSelect FROM property $whereString $having ORDER BY date_of_sale DESC LIMIT ? OFFSET ?";
//$countQuery = $dbh->prepare("SELECT count(*) $havingSelect as total FROM property $whereString $having ORDER BY date_of_sale");
$selectQuery = $dbh->prepare("SELECT * $havingSelect FROM property $whereString $having ORDER BY distance LIMIT ? OFFSET ?");

$count = 1;

foreach ($params as $param)
{
	//$countQuery->bindValue($count, $param);
	$selectQuery->bindValue($count, $param);
	
	$count++;
}

$selectQuery->bindValue($count, (int)$limit, PDO::PARAM_INT);
$selectQuery->bindValue($count + 1, (int)$offset, PDO::PARAM_INT);
/*
if ($countQuery->execute())
{
	$counts = $countQuery->fetch(PDO::FETCH_ASSOC); 
};
if ($counts['total'])
*/
{
/*
	$totalPages = ceil($counts['total'] / $limit);
	$currentPage = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;
	
	$totalPagesToShow = ($totalPages < 10) ? $totalPages : 10; 
	$startPage = ($totalPages < 10) ? 1 : (($currentPage < 5) ? 1 : $currentPage - 5);
*/	
	if ($selectQuery->execute())
	{
		$results = $selectQuery->fetchAll();
		
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Content-type: application/json');
		
		echo json_encode($results);
	};
}