<?php
//simple PHP script to output API for baltimoreaa.org

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);

//database connection info, edit me
$server = 'localhost';
$username = 'root';
$password = '';
$database = 'baltimore';

$sql	  = 'SELECT * FROM meeting_directory WHERE mID <> "Group ID"'; //checking because header row present in data sample

class Meeting
{
	function __construct( $row )
	{

		$this->name = $row->mName;
		$this->time = $row->mInternational;
		if ($this->time == '2400') $this->time = '23:59'; //ad-hoc replacement
		$this->day = $row->mDayNo -1; //day should be 0 - 6 (sun - sat)
		$this->slug = $row->mID . '-' . $this->day . '-' . str_replace(':', '', $this->time); //id by itself is not unique
		$this->notes = $row->mNotes;
		$this->location = $row->mAdd1;

		//fixing addresses that have extra stuff in them
		if ($pos = strpos($row->mAdd2, ' (')) {
			$this->address = substr($row->mAdd2, 0, $pos);
			$this->notes .= substr($row->mAdd2, $pos);
		} elseif ($pos = strpos($row->mAdd2, ';')) {
			$this->address = substr($row->mAdd2, 0, $pos);
			$this->notes .= substr($row->mAdd2, $pos);
		} elseif ($pos = strpos($row->mAdd2, ', ')) {
			$this->address = substr($row->mAdd2, 0, $pos);
			$this->notes .= substr($row->mAdd2, $pos);
		} elseif ($pos = strpos($row->mAdd2, '- ')) {
			$this->address = substr($row->mAdd2, 0, $pos);
			$this->notes .= substr($row->mAdd2, $pos);
		} elseif ($pos = strpos($row->mAdd2, '-rear')) { //don't want to simply match - since it's correct in many addresses
			$this->address = substr($row->mAdd2, 0, $pos);
			$this->notes .= substr($row->mAdd2, $pos);
		} elseif ($pos = strpos($row->mAdd2, '-near')) { //don't want to simply match - since it's correct in many addresses
			$this->address = substr($row->mAdd2, 0, $pos);
			$this->notes .= substr($row->mAdd2, $pos);
		} elseif ($pos = strpos($row->mAdd2, '-Roland')) { //don't want to simply match - since it's correct in many addresses
			$this->address = substr($row->mAdd2, 0, $pos);
			$this->notes .= substr($row->mAdd2, $pos);
		} elseif ($pos = strpos($row->mAdd2, '-Smith')) { //don't want to simply match - since it's correct in many addresses
			$this->address = substr($row->mAdd2, 0, $pos);
			$this->notes .= substr($row->mAdd2, $pos);
		} elseif ($pos = strpos($row->mAdd2, ' @ ')) {
			$this->address = substr($row->mAdd2, 0, $pos);
			$this->notes .= substr($row->mAdd2, $pos);
		} elseif ($pos = strpos($row->mAdd2, ' bet. ')) {
			$this->address = substr($row->mAdd2, 0, $pos);
			$this->notes .= substr($row->mAdd2, $pos);
		} elseif ($pos = strpos($row->mAdd2, ' at ')) {
			$this->address = substr($row->mAdd2, 0, $pos);
			$this->notes .= substr($row->mAdd2, $pos);
		} elseif ($pos = strpos($row->mAdd2, ' & ')) {
			$this->address = substr($row->mAdd2, 0, $pos);
			$this->notes .= substr($row->mAdd2, $pos);
		} else {
			$this->address = $row->mAdd2;
		}
		
		//ad-hoc replacements
		$this->address = str_replace(' La.', ' Lane', $this->address);
		$this->address = str_replace('Comm Life Ctr', '961 Johnsville Road', $this->address);
		$this->address = str_replace('Hanover Price', 'Hanover Pike', $this->address);
		
		//fixing cities that have extra stuff
		if ($pos = strpos($row->mCity, '/')) {
			$this->city = substr($row->mCity, 0, $pos);
		} else {
			$this->city = $row->mCity;
		}
		
		$this->state = 'MD';
		$this->postal_code = $row->mZip;
		$this->country = 'US';
		//list($this->latitude, $this->longitude) = explode(',', trim($row->mSpecial));
		$this->timezone = 'America/New_York';

		//build array of meeting codes
		$this->types = array();
		if ($row->mOpen == 'O') {
			$this->types[] = 'O';
		} elseif ($row->mOpen == 'C') {
			$this->types[] = 'C';			
		}
		if (stristr($row->mNotes, 'big book')) $this->types[] = 'BB';
		if (stristr($row->mNotes, 'chip')) $this->types[] = 'H';
		if (stristr($row->mNotes, 'gay')) $this->types[] = 'G';
		if (stristr($row->mNotes, 'grapevine')) $this->types[] = 'GR';
		if (stristr($row->mNotes, 'spanish')) $this->types[] = 'S';
		if (stristr($row->mNotes, 'step')) $this->types[] = 'ST';
		if (stristr($row->mNotes, 'trad')) $this->types[] = 'TR';
		if (stristr($row->mNotes, 'women')) {
			$this->types[] = 'W';
		} elseif (stristr($row->mNotes, 'men')) {
			$this->types[] = 'M';
		}
		if ($row->mAccess == 'H') $types[] = 'X';
		if ($row->mType == 'Discussion') {
			$this->types[] = 'D';
		} elseif ($row->mType == 'Speaker') {
			$this->types[] = 'SP';
		}		
	}
}

$link = mysqli_connect($server, $username, $password, $database) or die('could not connect to database server');

$result = mysqli_query($link, $sql);
if (!$result) die(mysqli_error($link));
	
$data = array();
while ($row = mysqli_fetch_object($result)) {
	$obj = new Meeting($row);
	if (empty($obj->address)) continue; //checking because some rows are empty, looks like import issue maybe
	array_push($data, $obj);
}

header('Content-type: application/json; charset=utf-8');
echo json_encode($data);

mysqli_free_result($result);
mysqli_close($link);


function dd($obj) {
	echo '<pre>';
	print_r($obj);
	exit;
}