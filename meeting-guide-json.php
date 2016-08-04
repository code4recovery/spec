<?php
//simple PHP script to output API for a basic database

//database connection info, edit me!
$server		= '127.0.0.1';
$username	= 'root';
$password	= '';
$database	= 'your_database_name';

//if you have only a meetings table, use this variable
$sql		= 'SELECT * FROM meetings';

//if you have both meetings and locations, try this one. You may need to customize it some.
//$sql		= 'SELECT meetings.*, locations.* FROM meetings JOIN locations ON meetings.location_id = locations.id';

//send header and turn off error reporting so we can get a proper HTTP result
header('Content-type: application/json; charset=utf-8');
error_reporting(0);

//connect to database
if (empty($server)) error('$server variable is empty');
$link = mysql_connect($server, $username, $password) or error('could not connect to database server');
mysql_select_db($database, $link) or error('could not select database');
mysql_set_charset('utf8', $link);

//select data
if (empty($sql)) error('$sql variable is empty');
$result = mysql_query($sql, $link);
if (!$result) error(mysql_error($link));

//fetch data into array
$return = array();
while ($row = mysql_fetch_assoc($result)) $return[] = $row;
mysql_free_result($result);
mysql_close($link);

//return JSON
output($return);

//function to handle errors
function error($string) {
	output(array(
		'error' => $string,
	));
}

//function to output json
function output($array) {
	$return = json_encode($array);
	if (json_last_error()) error(json_last_error_msg());
	die($return);
}