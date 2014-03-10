<?php
include 'boot.php';

if (!$argv[1]) {
	echo "USAGE: {$argv[0]} <FILENAME>" . PHP_EOL;	
}

if (($handle = fopen($argv[1], "r")) !== FALSE) {
	$sth = $dbh->prepare("INSERT INTO property (date_of_sale,address,postal_code,county,price,not_full_market_price,description_of_property,property_size_description) VALUE (?,?,?,?,?,?,?,?)");
	
	//Discard first line
	fgetcsv($handle, 1000, ",");
	
	while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
		$dateParts = explode('/', $data[0]);
		$data[0] = "$dateParts[2]-{$dateParts[1]}-{$dateParts[0]}";
		
		$count = 1;
		foreach($data as $value) {
			$sth->bindValue($count, $value);
			$count++;
		}
		
		if ($sth->execute()) {
			echo '.';
		} else {
			echo 'Error executing' . PHP_EOL;
		}
	}
	fclose($handle);
} else {
	echo "Could not open {$argv[0]}" . PHP_EOL;
}