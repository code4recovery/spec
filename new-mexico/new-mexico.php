<?php
//file json exporter for new mexico

$file = file('default.db');

$return = array();

$columns = array('slug', 'day_description', 'day', 'time', 'time_24_hour', 'field_6', 'name', 
	'location', 'location_id', 'notes', 'address', 'phone', 'region', 'field_14', 
	'coordinates', 'types_verbose', 'types', 'location_notes', 'url', 'district'
);

$decode_types = array(
	'12' => 'ST',
	'ABS' => 'LIT',
	'BB' => 'BB',
	'BEG' => 'BE',
	'C' => 'C',
	'CC' => 'BA',
	'D' => 'D',
	'FF' => 'FF',
	'GV' => 'GR',
	'M' => 'M',
	'MED' => 'MED',
	'O' => 'O',
	'S' => 'SP',
	'SA' => 'SM',
	'SP' => 'S',
	'SS' => 'ST',
	'ST' => 'ST',
	'TS' => 'TR',
	'WC' => 'X',
	'YP' => 'Y',
);

foreach ($file as &$line) {
	
	//split line by delimiter
	$line = explode('|', $line);
	
	//trim all elements
	$line = array_map('trim', $line);

	//associate with columns	
	$line = array_combine($columns, $line);
	
	//extract 
	extract($line);
	
	//decrement day of week
	$day--;
	
	//format time
	$time = date('H:i', strtotime($time));
	
	//types
	$types = explode(',', $types);
	$types = array_map('trim', $types);
	$types = array_intersect_key($decode_types, array_flip($types));
	$types = array_values($types);
	
	$return[] = compact('slug', 'day', 'time', 'name', 'location', 'address', 'region', 'types');
}

//dd($file);

header('Content-type: application/json; charset=utf-8');
echo json_encode($return);

function dd($variable) {
	echo '<pre>';
	print_r($variable);
	exit;
}