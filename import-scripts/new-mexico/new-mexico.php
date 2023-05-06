<?php
//file json exporter for new mexico

$file = file('cgi-bin/dbman/default.db');

$return = array();

$columns = array('slug', 'day_description', 'day', 'time', 'time_24_hour', 'field_6', 'name', 
	'location', 'location_id', 'notes', 'address', 'phone', 'region', 'field_14', 
	'coordinates', 'types_verbose', 'types', 'location_notes', 'url', 'district'
);

$decode_types = array(
	'11' => '11',
	'12' => 'ST',
	'AB' => 'ABSI',
	'ABS' => 'ABSI',
	'ASL' => 'ASL',
	'B' => 'BE',
	'BB' => 'BB',
	'BEG' => 'BE',
	'BRK' => 'BRK',
	'BUS' => 'BUS',
	'C' => 'C',
	'CC' => 'BA',
	'D' => 'D',
	'DR' => 'DR',
	'FF' => 'FF',
	'G' => 'G',
	'GV' => 'GR',
	'L' => 'L',
	'LS' => 'LS',
	'M' => 'M',
	'MED' => 'MED',
	'N' => 'N',
	'NC' => 'NC',
	'NS' => 'Non-Smoking',
	'O' => 'O',
	'S' => 'SP',
	'SA' => 'SM',
	'SP' => 'S',
	'SS' => 'ST',
	'ST' => 'ST',
	'TS' => 'TR',
	'W' => 'W',
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
	
	//make address clearer to google
	$address = str_replace('  ', ' ', $address);
	if (substr($address, -4) == ', NM') {
		$address = substr($address, 0, strlen($address) - 4) . ', New Mexico';
	}
	
	//fix two specific issues
	if ($address == 'San Felipe Pueblo, Uninc Sandoval County, New Mexico') {
		$address = 'San Felipe Pueblo, New Mexico';
	} elseif ($address == 'Call DCM') {
		$address = 'Albuquerque, New Mexico';
	}

	//types
	$types = explode(',', $types);
	$types = array_map('trim', $types);
	$types = array_intersect_key($decode_types, array_flip($types));
	$types = array_values($types);
	
	$return[] = compact('slug', 'day', 'time', 'name', 'location', 'address', 'region', 'types');
}

//send correct mime header
header('Content-type: application/json; charset=utf-8');

//output json
echo json_encode($return);