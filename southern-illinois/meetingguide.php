<?php
	//11/19/16 - v1.0 - Rob Campbell mrosetech@gmail.com
	//This script pulls meetings from database in a preexisting format, and outputs them on a page in JSON format designed to be read by Meeting Guide - https://meetingguide.org/
	//01/27/2017 - v1.1 - Updated to use json_encode()
	//01/27/2017 - v1.2 - Added JSON header and removed closing PHP tag
	
	$sql = "SELECT 	name AS 'name',
							meeting_id AS 'slug',
							day AS 'day',
							TIME_FORMAT(start_time,'%H:%i') AS 'time',
							TIME_FORMAT(end_time,'%H:%i') AS 'end_time',
							location AS 'location',
							location AS 'group',
							notes AS 'notes',
							upd_dt AS 'updated',
							address AS 'address',
							city AS 'city',
							zip AS 'postal_code',
							state AS 'state',
							'US' AS 'country',
							city AS 'region',
							lat AS 'latitude',
							lng AS 'longitude'
							FROM meeting_lst 
							WHERE 1 AND status = 1 ORDER BY meeting_id ASC";
	include '../m/include/mysql_connect.php';
	$result = mysqli_query($con,$sql);
	if (!$result) {
		printf("Error: %s\n", mysqli_error($con));
		exit();
	}
	while($r = mysqli_fetch_assoc($result)) {
		$rows[] = $r;
	}
	$json_meetings = json_encode($rows);
	header('Content-type: application/json; charset=utf-8');
	print $json_meetings;
