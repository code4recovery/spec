<?php
//new hampshire export
//get CSV data from wp_datatables

//database connection info, edit me!
$server		= '127.0.0.1';
$username	= 'root';
$password	= '';
$database	= 'aa_new_hampshire';

//send header and turn off error reporting so we can get a proper HTTP result
header('Content-type: application/json; charset=utf-8');
error_reporting(0);

//connect to database
if (empty($server)) error('$server variable is empty');
$link = mysql_connect($server, $username, $password) or error('could not connect to database server');
mysql_select_db($database, $link) or error('could not select database');
mysql_set_charset('UTF-8', $link);

//get file information
$result = mysql_query('SELECT content FROM wp_wpdatatables WHERE id = 4', $link);
if (!$result) error(mysql_error($link));
$table = mysql_fetch_assoc($result);
$file_name = $table['content'];

//check to make sure file exists
if (!file_exists($file_name)) error('file could not be found');

//parse csv into array
$csv = array_map('str_getcsv', file($file_name));

//grab headers from first row and sanitize them
$headers = array_map(function($title){
    // Convert all dashes/underscores into separator
    $title = preg_replace('!['.preg_quote('-').']+!u', '_', $title);
    // Remove all characters that are not the separator, letters, numbers, or whitespace.
    $title = preg_replace('![^'.preg_quote('_').'\pL\pN\s]+!u', '', mb_strtolower($title));
    // Replace all separator characters and whitespace by a single separator
    $title = preg_replace('!['.preg_quote('_').'\s]+!u', '_', $title);
    return trim($title, '_');
}, array_shift($csv));

//apply headers to array
$csv = array_map(function($input) use($headers) {
	return array_combine($headers, $input);
}, $csv);

//return JSON
output($csv);

//function to handle errors
function error($string) {
	output(array(
		'error' => $string,
	));
}

//function to output json
function output($array) {
	die(json_encode($array));
}
