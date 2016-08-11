<?php
//script to implement meeting guide api for st. louis (https://github.com/meeting-guide/api)

//database connection info, edit me
$server = '127.0.0.1';
$username = 'root';
$password = '';
$database = 'st_louis';

//create some variables we're going to need
$return = array();

//connect to mysql
$db = mysql_connect($server, $username, $password) or die('Unable to connect to MySQL!');
mysql_select_db($database, $db) or die('Could not select database!');

//select meetings
$meetings = mysql_query('SELECT 
		AutoNumber,
		GroupName,
		MeetingDayNum,
		MeetingPlaceName,
		MeetingLoc,
		MeetingAddress,
		MeetingCity,
		MeetingState,
		MeetingZipCode,
		MeetingStatus,
		MeetingGenderID,
		MeetingHandicapFacil,
		MeetingSmokingStatus,
		MeetingNotes,
		MeetingSpecialInterest,
		MeetingBabySitting,
		MeetingLanguage,
		MeetingFormat,
		NewComerMeeting,
		MeetingTime,
		asl
	FROM tblmeetings
	WHERE GroupMeetStat = "A" AND MeetingAddress IS NOT NULL AND MeetingAddress <> ""');

//loop through
while ($meeting = mysql_fetch_array($meetings)) {

	$slug			= $meeting['AutoNumber'];
	$time			= $meeting['MeetingTime'];
	$name			= $meeting['GroupName'];
	$day			= [$meeting['MeetingDayNum'] - 1];
	$location		= $meeting['MeetingPlaceName'];
	$notes			= $meeting['MeetingNotes'];
	$address		= $meeting['MeetingAddress'];
	$city = $region = $meeting['MeetingCity'];
	$state			= empty($state) ? 'MO' : $meeting['MeetingState'];
	$postal_code	= $meeting['MeetingZipCode'];
	$country		= 'US';
	
	//some meetings are named "Meeting starts at 9:45am"
	if (substr($name, 0, 18) == 'Meeting starts at ') {
		$time = substr($name, 18);
	}
	
	//fix for lois
	if ($name == 'Nick of Time') $time = '12:15:00';

	//remove neighborhood info from city name
	if (substr($city, 0, 8) == 'St Louis') $city = 'St. Louis';

	//types			
	$types = [];
	if ($meeting['MeetingBabySitting'] == 1) $types[] = 'BA'; //babysitting
	if ($meeting['MeetingSmokingStatus'] == 2) $types[] = 'SM'; //smoking
	if ($meeting['MeetingLanguage'] == 'SPANISH') $types[] = 'S';
	if ($meeting['MeetingGenderID'] == 3) {
		$types[] = 'M';
	} elseif ($meeting['MeetingGenderID'] == 2) {
		$types[] = 'W';
	}
	if ($meeting['MeetingStatus'] == 1) {
		$types[] = 'O';
	} elseif ($meeting['MeetingStatus'] == 2) {
		$types[] = 'C';
	}
	if (($meeting['MeetingHandicapFacil'] == 1) || ($meeting['MeetingHandicapFacil'] == 2)) {
		$types[] = 'X';
	}
	if ($meeting['NewComerMeeting'] == 'Y') $types[] = 'BE'; //beginner
	
	if (stristr($meeting['MeetingFormat'], 'step')) $types[] = 'ST';
	if (stristr($meeting['MeetingFormat'], 'big book')) $types[] = 'BB';
	if (stristr($meeting['MeetingFormat'], 'speaker')) $types[] = 'SP';
	if (stristr($meeting['MeetingFormat'], 'tradition')) $types[] = 'TR';

	switch($meeting['MeetingFormat']) {
		case '12 X 12': 
			$types[] = 'ST';
			break;
		case '11th Step Med': 
			$types[] = 'MED';
			break;
		case 'AA / Alanon': 
			$types[] = 'AL-AN';
			break;
		case 'AA Literature Study': 
		case 'As Bill Sees It': 
		case 'Big Book/Living Sober': 
		case 'Came to Believe / Living Sober': 
		case 'Daily Reflections': 
		case 'Living Sober': 
			$types[] = 'LIT';
			break;
		case 'Birthday': 
			$types[] = 'H';
			break;
		case 'Candlelight': 
			$types[] = 'CAN';
			break;
		case 'Concepts/Step/Traditions': 
		case 'Grapevine': 
			$types[] = 'GR';
			break;
		case 'Topic': 
		case 'Topic/Discussion': 
		case 'Topic/Reading': 
		case 'Participation': 
		case 'Discussion': 
		case 'Duscussion': 
			$types[] = 'D';
			break;
		case 'Newcomer': 
			$types[] = 'BE';
			break;
		case 'Young People': 
			$types[] = 'Y';
			break;
	}
	
	$types = array_unique($types);

	$return[] = compact('name', 'slug', 'day', 'time', 'location', 'address', 'city', 'state', 'region', 'postal_code', 'country', 'types');
}

//output
header('Content-type: application/json; charset=utf-8');
echo json_encode($return);