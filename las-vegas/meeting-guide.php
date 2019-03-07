<?php

//database connection info, use wordpress settings
if (file_exists('wp-config.php')) {
	include('wp-config.php');
} else {
	define('DB_HOST', 'localhost');
	define('DB_NAME', 'las_vegas');
	define('DB_USER', 'root');
	define('DB_PASSWORD', '');
}

//make sure errors are being reported
error_reporting(E_ALL);

//connect to database
try {
    $pdo = new PDO('mysql:charset=UTF8;dbname=' . DB_NAME . ';host=' . DB_HOST, DB_USER, DB_PASSWORD);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

//error handling
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$meta_decode = array(
	'_content_field_20' => 'Sunday',
	'_content_field_21' => 'Monday',
	'_content_field_22' => 'Wednesday',
	'_content_field_23' => 'Tuesday',
	'_content_field_24' => 'Thursday',
	'_content_field_25' => 'Friday',
	'_content_field_26' => 'Saturday',
	'_content_field_27' => 'notes',
	'_content_field_28' => 'phone',
	//'_content_field_29' => 'url',
	'_content_field_30' => 'email',
	'_address_line_1' => 'address',
	'_zip_or_postal_in	dex' => 'postal_code',
);

$decode_types = array(
	'12 X 12 Study' => '12x12',
	'Big Book Study' => 'B',
	'Candlelight' => 'CAN',
	'Childcare Available' => 'BA',
	'Closed Discussion' => array('C', 'D'),
	'Gay' => 'G',
	'Grapevine' => 'GR',
	'Literature' => 'LIT',
	'Meditation' => 'MED',
	'Men\'s Stag' => 'M',
	'Newcomers' => 'BE',
	'Open Discussion' => array('O', 'D'),
	'Secular / Agnostic' => 'A',
	'Signed Interpreter (Hearing Impaired)' => 'ASL',
	'Spanish Speaking' => 'S',
	'Speaker' => 'SP',
	'Step Study' => 'ST',
	'Wheelchair Accessible' => 'X',
	'Women Only' => 'W',
	'Young People' => 'YP',
);

//select data
try {

	//get all the meetings
	$meetings = $pdo->query('SELECT 
			p.id,
			p.post_name,
			p.post_title "name",
			p.post_modified_gmt "updated"
		FROM wp_posts p 
		WHERE 
			p.post_type = "w2dc_listing" AND 
			p.post_status = "publish"')
		->fetchAll(PDO::FETCH_ASSOC);

	//get the metadata
	$meta = $pdo->query('SELECT
			m.post_id, 
			m.meta_key, 
			m.meta_value
		FROM wp_postmeta m
		WHERE 
			m.post_id IN (SELECT id FROM wp_posts WHERE post_status = "publish" and post_type = "w2dc_listing") AND 
			m.meta_key IN (' . implode(', ', array_map(function($key){ return '"' . $key . '"';}, array_keys($meta_decode))) . ')')
		->fetchAll(PDO::FETCH_ASSOC);

	//get the tags
	$tags = $pdo->query('SELECT
			t.name,
			x.taxonomy,
			r.object_id "post_id"
		FROM wp_terms t 
		JOIN wp_term_taxonomy x ON t.term_id = x.term_id
		JOIN wp_term_relationships r ON x.term_taxonomy_id = r.term_taxonomy_id
		WHERE 
			x.taxonomy IN ("w2dc-category", "w2dc-tag")')
		->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die('SQL query failed: ' . $e->getMessage());
}

function dd($obj) {
	echo '<pre>';
	print_r($obj);
	exit;
}

function getMetaFor($meeting_id) {
	global $meta, $meta_decode;
	$values = array_filter($meta, function($item) use ($meeting_id) {
		return $item['post_id'] == $meeting_id;
	});
	$return = array();
	foreach ($values as $value) {
		$return[$meta_decode[$value['meta_key']]] = $value['meta_value'];
	}
	return $return;
}

function getTagsFor($meeting_id) {
	global $tags;
	$values = array_filter($tags, function($item) use ($meeting_id) {
		return $item['post_id'] == $meeting_id && $item['taxonomy'] == 'w2dc-category';
	});
	$return = array();
	foreach ($values as $value) {
		if (strtolower($value['name']) == 'noon') {
			$return[] = '12:00 PM';
		} elseif (strtolower($value['name']) == 'midnight') {
			$return[] = '12:00 AM';
		} else {
			$return[] = $value['name'];
		}			
	}
	return $return;
}

function getLocationName($meeting_id) {
	global $tags;
	$values = array_filter($tags, function($item) use ($meeting_id) {
		return ($item['post_id'] == $meeting_id) && ($item['taxonomy'] == 'w2dc-tag');
	});
	if (count($values)) return array_values($values)[0]['name'];
	return false;
}

//fetch data
$return = array();
foreach ($meetings as $meeting) {

	$meeting = array_merge($meeting, getMetaFor($meeting['id']));

	//handle joined address
	if (empty($meeting['address'])) continue;
	$meeting['address'] = str_replace('<br />', '<br/>', $meeting['address']);
	$meeting['address'] = str_replace('</br>', '<br/>', $meeting['address']);
	$meeting['address'] = explode('<br/>', $meeting['address']);
	$address_count = count($meeting['address']);

	$meeting['city'] = null;

	if ($address_count == 1) {
		$meeting['address'] = $meeting['address'][0];
	} elseif ($address_count == 2) {
		$meeting['city'] = $meeting['address'][1];
		$meeting['address'] = $meeting['address'][0];
	} elseif ($address_count == 3) {
		$meeting['location'] = $meeting['address'][0];
		$meeting['city'] = $meeting['address'][2];
		$meeting['address'] = $meeting['address'][1];
	}

	//get location name this way if it's set
	if ($location = getLocationName($meeting['id'])) {
		$meeting['location'] = $location;
	}

	//clean up city
	$meeting['city'] = str_replace(', NV', '', $meeting['city']);
	$meeting['city'] = str_replace(', Nevada', '', $meeting['city']);
	$meeting['city'] = str_replace(', USA', '', $meeting['city']);
	$meeting['city'] = str_replace(', United States', '', $meeting['city']);
	$meeting['state'] = 'NV';
	$meeting['country'] = 'USA';

	//address sometimes has the whole thing
	if (substr($meeting['address'], -9) == ', NV, USA') {
		$meeting['address'] = substr($meeting['address'], 0, -9);
		$meeting['address'] = explode(', ', $meeting['address']);
		$meeting['city'] = array_pop($meeting['address']);
		$meeting['address'] = implode(', ', $meeting['address']);
	}

	if (empty($meeting['city'])) $meeting['city'] = 'Las Vegas';

	//ad hoc correction
	if (strpos($meeting['address'], '101 Pavilion Center') !== false) {
		$meeting['address'] = '101 S Pavilion Center Dr';
		$meeting['city'] = 'Las Vegas';
		$meeting['location'] = 'Veterans Memorial Leisure Center';
	}
	if (strpos($meeting['address'], '701 Avenue N') !== false) {
		$meeting['address'] = '701 Avenue N';
		$meeting['city'] = 'Ely';
	}
	if (strpos($meeting['address'], 'McGill Methodist Church') !== false) {
		$meeting['location'] = 'McGill Methodist Church';
		$meeting['address'] = 'Second Street at Avenue J';
		$meeting['city'] = 'McGill';
	}

	$meeting['url'] = 'https://www.lvcentraloffice.org/aameetinglisting/' . $meeting['post_name'] . '/';

	//clean up values
	$meeting = array_map('strip_tags', $meeting);
	$meeting = array_map('trim', $meeting);
	$meeting = array_map(function($value){
		return empty($value) ? null : $value;
	}, $meeting);

	//handle types
	$types = getTagsFor($meeting['id']);
	$meeting['types'] = array();
	foreach ($types as $type) {
		if (array_key_exists($type, $decode_types)) {
			if (is_array($decode_types[$type])) {
				$meeting['types'] = array_merge($meeting['types'], $decode_types[$type]);
			} else {
				$meeting['types'][] = $decode_types[$type];
			}
		}
	}

	//try getting time from types
	$meeting['time'] = null;
	if ($time = array_filter($types, function($type){
		return strpos($type, ':') !== false;
	})) {
		$meeting['time'] = array_values($time)[0];
	}

	//also try getting it from the meeting name
	if (strpos($meeting['name'], '-') !== false) {
		$name_parts = explode('-', $meeting['name']);
		$name_parts = array_map('trim', $name_parts);
		foreach ($name_parts as $part) {
			if (in_array(substr(strtolower($part), -2), array('am', 'pm'))) {
				$meeting['time'] = $part;
				$meeting['name'] = implode(' - ', array_diff($name_parts, array($part)));
				break;
			}
		}
	}

	//loop through the days
	foreach (array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday') as $index => $day) {
		if (!in_array($day, $types)) continue;
		if (!empty($meeting[$day])) {
			$meeting['day'] = $index;
			if (strpos($meeting[$day], '-') === false) {
				//sometimes it's all time
				if (strpos($meeting[$day], ':')) {
					$meeting['time'] = $meeting[$day];
				}
			} else {
				//sometimes it's on one side of a dash
				$day_parts = explode('-', $meeting[$day]);
				$day_parts = array_map('trim', $day_parts);
				foreach ($day_parts as $part) {
					if (strtolower($part) == 'noon') $part = '12:00PM';
					if (strtolower($part) == 'midnight') $part = '12:00AM';
					if (!$meeting['time'] && strpos($part, ':') !== false) {
						$meeting['time'] = $part;
					}
					if (array_key_exists($part, $decode_types)) {
						if (is_array($decode_types[$part])) {
							$meeting['types'] = array_merge($meeting['types'], $decode_types[$part]);
						} else {
							$meeting['types'][] = $decode_types[$part];
						}
					}
				}
			}

			//format the time
			if ($meeting['time']) {
				$meeting['time'] = date('H:i', strtotime($meeting['time']));
			}

			//handle the slug
			$meeting['slug'] = $meeting['post_name'] . '--' . $meeting['day'];

			$return[] = $meeting;
		}
	}
}

//check both open and closed types
$return = array_map(function($meeting) {
	$meeting['types'] = array_unique($meeting['types']);
	sort($meeting['types']);
	//if both open and closed, remove both
	if (in_array('C', $meeting['types']) && in_array('O', $meeting['types'])) {
		$meeting['types'] = array_diff($meeting['types'], array('O', 'C'));
	}
	return $meeting;
}, $return);

//dd($return);

//encode JSON
$return = json_encode($return);
if (json_last_error()) {
	die('JSON error: ' . json_last_error_msg());
}

//make sure headers haven't already been sent, will cause error
if (headers_sent()) {
	die('Error: headers already sent!');
}

//output
header('Content-type: application/json; charset=utf-8');
echo $return;
