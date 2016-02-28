<?php
//simple PHP script to output API for baltimoreaa.org

//database connection info, edit me
$server = '127.0.0.1';
$username = 'root';
$password = '';
$database = 'aa_baltimore';

error_reporting(E_ERROR | E_WARNING | E_PARSE);

function slugify($text)
{ 
  // replace non letter or digits by -
  $text = preg_replace('~[^\\pL\d]+~u', '-', $text);

  // trim
  $text = trim($text, '-');

  // transliterate
  $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

  // lowercase
  $text = strtolower($text);

  // remove unwanted characters
  $text = preg_replace('~[^-\w]+~', '', $text);

  if (empty($text))
  {
    return 'n-a';
  }

  return $text;
}

class Meeting
{
	function __construct( $row )
	{
		$types = array();

		if ($row->mOpen == 'O') {
			$types[] = 'O';
		} elseif ($this->mOpen == 'C') {
			$types[] = 'C';			
		}
		if (stristr($row->mNotes, 'big book')) $types[] = 'BB';
		if (stristr($row->mNotes, 'chip')) $types[] = 'H';
		if (stristr($row->mNotes, 'gay')) $types[] = 'G';
		if (stristr($row->mNotes, 'grapevine')) $types[] = 'GR';
		if (stristr($row->mNotes, 'spanish')) $types[] = 'S';
		if (stristr($row->mNotes, 'step')) $types[] = 'ST';
		if (stristr($row->mNotes, 'trad')) $types[] = 'TR';
		if (stristr($row->mNotes, 'women')) {
			$types[] = 'W';
		} elseif (stristr($row->mNotes, 'men')) {
			$types[] = 'M';
		}
		if ($row->mAccess == 'H') $types[] = 'X';
		if ($row->mType == 'Discussion') {
			$types[] = 'D';
		} elseif ($row->mType == 'Speaker') {
			$types[] = 'SP';
		}		
		
		$this->name = $row->mName;
		$this->slug = $row->mID;
		$this->notes = $row->Notes;
		$this->updated = date('c');
		//$this->url = '';
		$this->time = $row->mInternational;
		$this->day = $row->mDayNo -1;
		$this->types = $types;
		$this->address = $row->mAdd2;
		$this->city = $row->mCity;
		$this->state = 'MD';
		$this->postal_code = $row->mZip;
		$this->country = 'US';
		if (strstr($row->mSpecial, ',')) {
			$this->latitude = explode(',',$row->mSpecial)[0];
			$this->longitude = explode(',',$row->mSpecial)[1];
		}
		$this->timezone = 'America/New_York';
		$this->location = $row->mAdd1;
		$this->location_slug = slugify($row->mAdd1);
		//$this->location_notes = '';
	}
}

mysql_connect($server, $username, $password);
mysql_select_db($database);

$result = mysql_query('SELECT * FROM meeting_directory WHERE mID <> "Group ID"'); //checking because header row present in data sample

$data = array();
while ($row = mysql_fetch_object($result)) {
	$obj = new Meeting($row);
	if (!empty($obj->latitude)) //checking because some rows are empty, looks like import issue maybe
	{
		array_push($data, $obj);
	}
}

header('Content-type: application/json; charset=utf-8');
echo json_encode($data);

mysql_free_result($result);
