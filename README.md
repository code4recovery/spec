# Meeting Guide API

The goal of the Meeting Guide API is help sync information about AA meetings. It was developed for the [Meeting Guide app](https://meetingguide.org/), but it is non-proprietary and other systems are encouraged to make use of it.

If you have feedback, please put an issue on this repository.

## Usage

To implement the API on your server, create a file that can take information from your database and format it in the correct specification (see below).

The file [meeting-guide-json.php](meeting-guide-json.php) contains the simplest version of the JSON feed for PHP. It will require a little customization to work properly.

For security, your script should not accept any parameters. It should be read-only.

It's critically important that your data not break anyone's anonymity. No last names should be used in meeting notes, and no one's face should be pictured in meeting images.

Test your feed with the [Meeting Guide JSON Feed Validator](https://meetingguide.org/validate). Once it's ready, or if you have questions, use the [Meeting Guide contact form](https://meetingguide.org/contact).

If you would like to share your script, we'll include a copy in this repository so that it might help future users.

## Specification

The JSON file is expected to contain a simple array of meetings. [Here is an example](https://aasanjose.org/wp-admin/admin-ajax.php?action=meetings) of a live JSON feed.

```JSON
[
	{
		"name": "Sunday Serenity",
		"slug": "sunday-serenity",
		"day": 0,
		"time": "18:00",
		"end_time": "19:30",
		"location": "Alano Club",
		"group": "The Serenity Group",
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

`slug` is required, and must be unique to your data set. It should preferably be a string, but integer IDs are fine too.

`day` is required and may be an integer or an array of integers 0-6, representing Sunday (0) through Saturday (6).

`time` is a required five-character string in the `HH:MM` 24-hour time format.

`end_time` is an optional five-character string in the `HH:MM` 24-hour time format.

`location` is an optional string and should be a recognizable building or landmark name.

`group` is an optional string.

`notes` is an optional long text field. Line breaks are ok, but HTML will be stripped.

`location_notes` is an optional long text field with notes applying to all meetings at the location.

`updated` is an optional UTC timestamp in the format `YYYY-MM-DD HH:MM:SS`.

`url` is optional and should point to the meeting's listing on the area website.

`image` is an optional url that should point to an image representing the location. We recommend an image of the building's facade. Ideally this is a JPG image 1080px wide by 540px tall.

`types` is an optional array of standardized meeting types. See the types section below.

`formatted_address` either this or the address / city / state / postal_code / country combination are required.

`address`, `city`, `state`, `postal_code`, and `country` are all optional strings, but together they must form an address that Google can identify. `address` and `city` are suggested. Take special care to strip extra information from the address, such as 'upstairs' or 'around back,' since this is the primary cause of geocoding problems. (That information belongs in the `notes` field.) Intersections are usually ok, but approximate addresses, such as only a city or route, do not have enough precision to be useful.

`region` is an optional string that represents a geographical subset of meeting locations. Usually this is a neighborhood or city. District numbers are discouraged because they require special program knowledge to be understood.

## Common Questions & Concerns

#### We use different meeting codes!

That's ok. App users don't actually see the codes, just the types they translate to.

#### Our meeting type isn't listed!

Types have to be consistent across the app to make a good user experience. It's common that a user might see meeting results from several areas at a time (this happens in small areas, and near borders). The set of meeting types we use is a mutually-agreed-upon set of names across 70+ areas. If you have a request to edit the list, we will bring it up at our steering committee meeting.

#### Why is slug necessary?

Slug is a required unique field because there is an app feature where users may 'favorite' a meeting, and in order for that to persist across sessions we must attach it to a unique field. It might seem intuitive that meeting location + time would be a unique combination, but in practice we see cases where there are in fact simultaneous meetings at the same location.

#### Why are day and time required?

It's perfectly fine for meetings to be 'by appointment' and this often happens in places where there are not many meetings. The app, however, needs this information to present useful information to the user.

#### Why can't we have HTML in meeting notes?

We are trying to make the data portable across a range of devices, some of which might not display HTML.

## Meeting Types

The codes below are only used for transmitting meeting data. App users will only see the full definitions.

The codes below should be considered 'reserved.' In your implementation, it's ok to alter the description (for example
"Topic Discussion" rather than "Discussion") so long as the intent is the same. "Child Care Available" is a common substitute
for "Babysitting Available." "American Sign Language" or "ASL" rather than "Sign Language." It's also ok to add types,
they will be ignored by the importer.

Code | Definition
---- | ----------
`11` | 11th Step Meditation
`ASBI` | As Bill Sees It
`A` | Atheist / Agnostic
`BA` | Babysitting Available
`B` | Big Book
`H` | Birthday
`BRK` | Breakfast
`BUS` | Business (meetings tagged this are not displayed in Meeting Guide)
`CF` | Child-Friendly
`C` | Closed
`CAN` | Candlelight
`AL-AN` | Concurrent with Al-Anon
`AL` | Concurrent with Alateen
`XT` | Cross Talk Permitted
`DR` | Daily Reflections
`D` | Discussion
`DD` | Dual Diagnosis
`FF` | Fragrance Free
`FR` | French
`G` | Gay
`GR` | Grapevine
`ITA` | Italian
`KOR` | Korean
`L` | Lesbian
`LIT` | Literature
`LS` | Living Sober
`LGBTQ` | LGBTQ
`MED` | Meditation
`M` | Men
`N` | Native American
`BE` | Newcomer
`NS` | Non-Smoking (type is ignored by Meeting Guide)
`O` | Open
`POL` | Polish
`POR` | Portuguese
`PUN` | Punjabi
`RUS` | Russian
`ASL` | Sign Language
`SM` | Smoking Permitted
`S` | Spanish
`SP` | Speaker
`ST` | Step Meeting
`TR` | Tradition Study
`T` | Transgender
`X` | Wheelchair Access
`W` | Women
`Y` | Young People
