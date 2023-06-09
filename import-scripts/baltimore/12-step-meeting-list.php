<?php

//function to reformat CSV exported from baltimore office access database
function tsml_import_reformat($meetings) {

	//some vars we'll need
	$return = array();
	$state = 'MD';
	$country = 'US';
	$decode_days = array('Sun' => 'Sunday', 'Mon' => 'Monday', 'Tue' => 'Tuesday', 'Wed' => 'Wednesday', 'Thu' => 'Thursday', 'Fri' => 'Friday', 'Sat' => 'Saturday');
	$delimiters = array('@', ';', '&', '(');
	array_shift($meetings);
	
	//loop through the input
	foreach($meetings as $meeting) {
		//easy ones
		$day = array_key_exists($meeting[7], $decode_days) ? $decode_days[$meeting[7]] : $meeting[7];
		$time = $meeting[9];
		$name = $meeting[1];
		$location = $meeting[2];
		$region = $meeting[4];
		$postal_code = $meeting[5];
		$location_notes = '';
		$notes = $meeting[13];

		//if region has a slash, just take the first part and hope google figures it out
		if ($pos = strpos($region, '/')) {
			$city = substr($region, 0, $pos);
		} else {
			$city = $region;
		}

		//split address at delimiter if present
		$address = $meeting[3];
		foreach ($delimiters as $delimiter) {
			if ($pos = strpos($address, $delimiter)) {
				$location_notes .= substr($address, $pos);
				$address = substr($address, 0, $pos);
			}
		}
		
		//build types from a number of columns
		$types = array();
		
		//open column
		if ($meeting[10] == 'O') {
			$types[] = 'Open';
		} elseif ($meeting[10] == 'C') {
			$types[] = 'Closed';
		}
		
		//smoke, type and notes
		$type_values = explode(' ', strtoupper($meeting[11] . ' ' . $meeting[12] . ' ' . str_replace(',', ' ', $meeting[13])));
		foreach ($type_values as $value) {
			if ($value == 'AS BILL SEES IT') $types[] = 'As Bill Sees It';
			if ($value == 'BB') $types[] = 'Big Book';
			if ($value == 'CHIP') $types[] = 'Birthday';
			if ($value == 'D') $types[] = 'Discussion';
			if ($value == 'DAILY REFLECTIONS') $types[] = 'Daily Reflections';
			if ($value == 'DISCUSSION') $types[] = 'Discussion';
			if ($value == 'G') $types[] = 'LGBTQ';
			if ($value == 'LGBTQ') $types[] = 'LGBTQ';
			if ($value == 'LIT') $types[] = 'Literature';
			if ($value == 'MENS') $types[] = 'Men';
			if ($value == 'PROM') $types[] = 'Promises';
			if ($value == 'SPK') $types[] = 'Speaker';
			if ($value == 'SPANISH') $types[] = 'Spanish';
			if ($value == 'STEP') $types[] = 'Step Meeting';
			if ($value == 'TOPIC') $types[] = 'Discussion';
			if ($value == 'TRAD') $types[] = 'Traditions';
			if ($value == 'W') $types[] = 'Women';
			if ($value == 'WOMENS') $types[] = 'Women';
			if ($value == 'YP') $types[] = 'Young People';
		}
		
		//h column
		if ($meeting[14] == 'H') {
			$types[] = 'Wheelchair Access';
		}
		$types = implode(',', $types);
		
		//add to array
		$return[] = compact('day', 'time', 'name', 'location', 'address', 'city', 'state', 'postal_code', 'country', 'types', 'notes', 'location_notes', 'region');
	}

	//go back to non-associative format
	$meetings = array();
	$meetings[] = array_keys($return[0]);
	foreach ($return as $row) {
		$meetings[] = array_values($row);
	}
	return $meetings;	
}
