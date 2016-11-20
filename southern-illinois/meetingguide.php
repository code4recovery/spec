<?php
	//11/19/16 - Rob C. mrosetech@gmail.com
	//This script pulls meetings from database in a preexisting format, and outputs them on a page in JSON format designed to be read by Meeting Guide - https://meetingguide.org/
	//dayMeetingGuide() converts MySQL numeric day of week to Meeting Guide numeric day of week
	//Sun = 1, ... Sat=7 --> Sun=0,...Sat=6
	function dayMeetingGuide($dayIn){
		switch($dayIn){
			case 1://Sunday
				$dayOut = 0;
				break; 
			case 2://Monday
				$dayOut = 1;
				break; 
			case 3://Tuesday
				$dayOut = 2;
				break; 
			case 4://Wednesday
				$dayOut = 3;
				break; 
			case 5://Thursday
				$dayOut = 4;
				break; 
			case 6://Friday
				$dayOut = 5;
				break; 
			case 7://Saturday
				$dayOut = 6;
				break; 
		}
		return $dayOut;
	}
	function escapeChars($inString){
		//Do any character processing here if needed
		//$inString = str_replace("[","",$inString);
		//$inString = str_replace("]","",$inString);
		//$inString = str_replace("&amp;","and",$inString);
		//$inString = str_replace("&","and",$inString);
		//$inString = str_replace("/","\/",$inString);
		//Remove \ - Do this last
		$outString = str_replace("\"","",$inString);
		return $outString;
	}
	include '../m/include/mysql_connect.php';
/*	Example - we include this above:
	$hostname = "host";
	$username = "username";
	$dbname = "dbname";
	$password = "password";
	$con = mysqli_connect($hostname, $username, $password,$dbname);
	if (mysqli_connect_errno()){
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}
*/
	//Query all active meetings
	$meeting_sql = "SELECT * FROM meeting_lst WHERE 1 AND status = 1 ORDER BY meeting_id ASC";
	$meeting_result = mysqli_query($con,$meeting_sql);
	//If any errors were encounted pulling meetings, display the error and stop processing
	if (!$meeting_result) {
		printf("Error: %s\n", mysqli_error($con));
		exit();
	}
	//Get a total count of meetings for troubleshooting and identifying last meeting
	$meeting_count = mysqli_num_rows($meeting_result);
	if($meeting_count > 0){
		//i will be a counter - the last meeting in the JSON output should not have a comma at the end
		$i = 0;
		//Start JSON Output
		print '[';
		//Loop through active meetings building JSON elements
		while($meeting = mysqli_fetch_array($meeting_result)) {
			//Increment counter
			//date("H:i");
			$i+=1;
			print '{';
			print '"name": "'	.escapeChars($meeting['name']).'",';
			print '"slug": "'	.$meeting['meeting_id'].'",';
			print '"day": '		.dayMeetingGuide($meeting['day']).',';
			print '"time": "'	.date("H:i",strtotime($meeting['start_time'])).'",';
			print '"end_time": "'.date("H:i",strtotime($meeting['start_time'])).'",';
			print '"location": "'.escapeChars($meeting['location']).'",';
			print '"group": "'	.escapeChars($meeting['name']).'",';
			print '"notes": " '	.escapeChars($meeting['notes']).'",';
			print '"updated": "'.$meeting['upd_dt'].'",';
			//print '"url": "'.$meeting['url'].'",'; //Coming soon
			print '"address": "'.escapeChars($meeting['address']).'",';
			print '"city": "'	.$meeting['city'].'",';
			print '"state": "'	.$meeting['state'].'",';
			print '"postal_code": "'.$meeting['zip'].'",';
			print '"country": "US",';
			print '"region": "'	.$meeting['city'].'",';
			print '"latitude": "'.$meeting['lat'].'",';
			print '"longitude": "'.$meeting['lng'].'"';
			print '}';
			//If this is not the last meeting, add a comma to separate JSON elements
			if($i < $meeting_count){
				print ',';
			}
		}
		//Close JSON block
		print ']';	
	}
?>