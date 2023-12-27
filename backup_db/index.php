<?php 
include ('../includes/connection.php');

include ('dumper.php');

try {
	$world_dumper = Shuttle_Dumper::create(array(
		'host' => $host,
		'username' => $username,
		'password' => base64_decode($password),
		'db_name' => $db_name,
	));
	
	//~ $file_name="penguin.sql";
	$file_name=$db_name.".sql.gz";

	// dump the database to gzipped file
	$world_dumper->dump($file_name);

	// dump the database to plain text file
	//$world_dumper->dump($file_name);
	
	header('Content-Description: File Transfer');
	header('Content-Type: application/force-download');
	header("Content-Disposition: attachment; filename=\"" . basename($file_name) . "\";");
	header('Content-Transfer-Encoding: binary');
	header('Expires: 0');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
	header('Content-Length: ' . filesize($file_name));
	ob_clean();
	flush();
	readfile($file_name); //showing the path to the server where the file is to be download
	exit;
	
/*
	$wp_dumper = Shuttle_Dumper::create(array(
		'host' => 'localhost',
		'username' => 'root',
		'password' => 'penguin',
		'db_name' => 'tru_col_final',
	));

	// Dump only the tables with wp_ prefix
	$wp_dumper->dump('tru_col_final.sql', 'wp_');
	
	$countries_dumper = Shuttle_Dumper::create(array(
		'host' => 'localhost',
		'username' => 'root',
		'password' => 'penguin',
		'db_name' => 'tru_col_final',
		'include_tables' => array('country_list', 'currency_xrate'), // only include those tables
	));
	$countries_dumper->dump('selected_table.sql.gz');

	// Dump exclude_tables with wp_ prefix
	$world_dumper = Shuttle_Dumper::create(array(
		'host' => 'localhost',
		'username' => 'root',
		'password' => 'penguin',
		'db_name' => 'tru_col_final',
		'exclude_tables' => array('country_list'), 
	));
	$world_dumper->dump('excluding_tables.sql.gz');
	*/

} catch(Shuttle_Exception $e) {
	echo "Couldn't dump database: " . $e->getMessage();
}
