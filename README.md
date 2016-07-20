# Meeting Guide API

The goal of the Meeting Guide API is help sync information about AA meetings. It was developed for the [Meeting Guide app](https://meetingguide.org/), but it is non-proprietary and other systems are encouraged to make use of it.

If you have feedback, please put an issue on this repository.

##Usage

To implement the API on your server, create a file that can take information from your database and format it in the correct specification (see below). 

The file [meeting-guide-json.php](meeting-guide-json.php) contains the simplest version of the JSON feed for PHP.

For security, your script should not accept any parameters. It should be read-only.

Test your feed with the [Meeting Guide JSON Feed Validator](https://meetingguide.org/validate). Once it's ready, or if you have questions, use the [Meeting Guide contact form](https://meetingguide.org/contact).

If you would like to share your JSON script, we would be pleased to include it in the repository so that it may help future users.

##Specification
The JSON file is expected to contain a simple array of meetings. [Here is an example](https://aasanjose.org/wp-admin/admin-ajax.php?action=meetings) of a live JSON feed.

```JSON
[
	{
		"name": "Sunday Serenity",
		"slug": "sunday-serenity",
		"notes": "Ring buzzer. Meeting is on the 2nd floor.",
		"updated": "2014-05-31 00:32:23",
		"url": "https://intergroup.org/meetings/sunday-serenity",
		"time": "18:00",
		"day": 0,
		"types": [
			"O"
		],
		"location": "Alano Club",
		"address": "123 Main Street",
		"city": "Anytown",
		"state": "CA",
		"postal_code": "98765",
		"country": "US",
		"timezone": "America/Los_Angeles",
		"region": "Downtown"
	},
	...
]	
```

`name` is a required string. It should be the meeting name, where possible. Some areas use group names instead, although that's more abiguous. 255 characters max.

`slug` is required, and must be unique. It should preferably be a string, but integer IDs are fine too.

`notes` are optional. Line breaks are ok, but HTML will be stripped. Long text field.

`updated` is required and should be a machine-readable timestamp.

`url` is optional. If present, it must be the unique URL for that meeting only.

`time` is required and is a five-character string in a HH:MM 24-hour time format.

`day` is required and can be an integer or an array of integers 0-6, representing Sunday (0) through Saturday (6).

`types` is an optional array of standardized meeting types. See the types section below.

`location` is a required string and should be a recognizable building or landmark name.

`address`, `city`, `state`, `postal_code`, and `country` are all optional strings, but together they must form an address that Google can identify or else the meeting will be skipped. `address` and `city` are strongly suggested. Take special care to strip extra information from the address, such as 'upstairs' or 'around back,' since this is the primary cause of geocoding issues. Intersections are usually ok, but approximate addresses, such as only a city name, do not have enough precision to be useful.

`timezone` is an optional string, but if present must be in the [PHP List of Supported Timezones](http://php.net/manual/en/timezones.php).

`region` is an optional string that represents a geographical subset of meeting locations. Usually this is a neighborhood or city. District numbers are discouraged as they require special program knowledge to be useful.

##Meeting Types
`A` Atheist / Agnostic  
`BA` Babysitting Available  
`BE` Beginner  
`B` Big Book  
`CF` Child-Friendly  
`H` Chips  
`C` Closed  
`CAN` Candlelight  
`AL-AN` Concurrent with Al-Anon  
`AL` Concurrent with Alateen  
`XT` Cross Talk Permitted  
`DLY` Daily  
`FF` Fragrance Free  
`FR` French  
`G` Gay  
`GR` Grapevine  
`L` Lesbian  
`LIT` Literature  
`LGBTQ` LGBTQ  
`MED` Meditation  
`M` Men  
`O` Open  
`ASL` Sign Language  
`SM` Smoking Permitted  
`S` Spanish  
`SP` Speaker  
`ST` Step Meeting  
`D` Topic Discussion  
`TR` Tradition  
`T` Transgender  
`X` Wheelchair Accessible  
`W` Women  
`Y` Young People  

