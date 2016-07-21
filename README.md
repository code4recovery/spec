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
		"location": "Alano Club",
		"slug": "sunday-serenity",
		"day": 0,
		"time": "18:00",
		"notes": "Ring buzzer. Meeting is on the 2nd floor.",
		"updated": "2014-05-31 14:32:23",
		"url": "https://intergroup.org/meetings/sunday-serenity",
		"image": "https://intergroup.org/assets/img/locations/alano-club.jpg",
		"types": [
			"O"
		],
		"address": "123 Main Street",
		"city": "Anytown",
		"state": "CA",
		"postal_code": "98765",
		"country": "US",
		"region": "Downtown"
	},
	...
]	
```

`name` is a required string. It should be the meeting name, where possible. Some areas use group names instead, although that's more abiguous. 255 characters max.

`location` is a required string and should be a recognizable building or landmark name.

`slug` is required, and must be unique to your data set. It should preferably be a string, but integer IDs are fine too.

`day` is required and may be an integer or an array of integers 0-6, representing Sunday (0) through Saturday (6).

`time` is a required five-character string in the `HH:MM` 24-hour time format.

`notes` is an optional long text field. Line breaks are ok, but HTML will be stripped.

`updated` is an optional UTC timestamp in the format `YYYY-MM-DD HH:MM:SS`.

`url` is optional and should point to the meeting's listing on the area website.

`image` is an optional url that should point to an image representing the meeting. We recommend an image of the building's facade. Ideally this is a JPG image 1080px wide by 540px tall.

`types` is an optional array of standardized meeting types. See the types section below.

`address`, `city`, `state`, `postal_code`, and `country` are all optional strings, but together they must form an address that Google can identify. `address` and `city` are suggested. Take special care to strip extra information from the address, such as 'upstairs' or 'around back,' since this is the primary cause of geocoding problems. (That information belongs in the `notes` field.) Intersections are usually ok, but approximate addresses, such as only a city or route, do not have enough precision to be useful.

`region` is an optional string that represents a geographical subset of meeting locations. Usually this is a neighborhood or city. District numbers are discouraged because they require special program knowledge to be understood.

##Common Questions & Concerns

####We use different meeting codes! 
That's ok. App users don't actually see the codes, just the types they translate to.

####Our meeting type isn't listed!
Types have to be consistent across the app to make a good user experience. It's common that a user might see meeting results from several areas at a time (this happens near borders). The set of meeting types we use is a mutually-agreed-upon set of names across 30+ areas. If you have a request to edit the list, we will bring it up as business with at our steering committee meeting.

####Why is slug necssary?
Slug is a necessary field because there is an app feature where users may 'favorite' a meeting, and in order for that mark to persist across sessions we must attach it to a unique field. It might seem intuitive that meeting location + time would be a unique combination, but in practice we see cases where there are in fact simultaneous meetings at the same location.

####Why can't we have HTML in meeting notes?
We are trying to make the data portable across a range of devices, some of which might not display HTML.

##Meeting Types
The codes below are only used for transmitting meeting data. App users will only see the full definitions.

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

