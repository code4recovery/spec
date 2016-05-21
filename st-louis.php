<?php
//simplest possible PHP script to output api

//database connection info, edit me
$server = '127.0.0.1';
$username = 'root';
$password = '';
$database = 'aa_st_louis';

//create some variables we're going to need
$meetings = array();

//connect to mysql
$db = mysql_connect($server, $username, $password) or die('Unable to connect to MySQL!');
mysql_select_db($database, $db) or die('Could not select database!');

//select meetings
$result = mysql_query('SELECT 
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
while ($r = mysql_fetch_array($result)) {
	$meetings[] = $r;
}

//output
header('Content-type: application/json; charset=utf-8');
echo json_encode($meetings);