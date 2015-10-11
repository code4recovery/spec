# 12 Step Meetings API

## Overview
The purpose of this API is to facilitate the free exchange of timely, accurate information about twelve step meetings between interested parties. There should be as few limits as possible on access to data. 

This API is a product of a working group of the NAATW (National AA Technology Workshop).

## Guidelines
1. **The API is read-only.** This system does not expose any security vulnerabilities because nothing destructive can happen to the data. The API doesn't even accept parameters.
1. **Individual anonymity must be protected.** The data must not contain any personally-identifying information without the explicit consent of the person involved. It's the responsibility of the publisher (the area) to obtain this consent.
1. **Data should come from its primary source.** All information about an area's meetings must come from the area itself. Third-party information is not to be considered authoritative. Whenever information is listed by this API, it is believed to be canonical.
1. **The information should be accurate.** The area reasonably believes all information in the API to be current and accurate for public consumption.
1. **The data should be open to all.** The area should not place restrictions on who may access the data. The data should be freely available to all who request it.
1. **The information should always be available.** The area should make the data publicly available at all times. Temporary issues may affect availablility, but after 72 hours the information must be considered invalid.

## API Files
To avoid repetition, the API consists of three related files that represent all the transmittable information about the area. 

###area.json
An area (often an Intergroup or a General Service District) is responsible for maintaining a list of meetings within a geographic boundary. Although the boundaries may overlap, meetings should not be listed by multiple areas.

The area JSON file should be linked from the head of the website:

```
<meta name="12_step_meetings_api" content="http://aasanjose.org/api/area">
```
The area JSON should look like this:

```
{
	name: "Intergroup Central Office of Santa Clara County, Inc.",
	location: "San Jose, CA, USA",
	program: "AA",
	home_url: "http://aasanjose.org/",
	meetings_url: "http://aasanjose.org/meetings",
	api_locations: "http://aasanjose.org/api/locations",
	api_meetings: "http://aasanjose.org/api/meetings",
	api_version: "1.0",
	software: "12 Step Meeting List",
	software_version: "1.5.6"
}
```
* **name** is the name of the institution. It's preferred that it be as short as possible.
* **location** is the primary location, generally the county name or the largest city in the area. Please avoid listing multiple cities if a single regional description is more concise.
* **program** is the name of the program, eg AA, NA, OA, etc.
* **home_url** website homepage
* **meetings_url** the url of the main meetings view
* **api_locations** the url of the locations JSON
* **api_meetings** the url of the meetings JSON
* **api_version** the current version of this API is 1.0
* **software** the name of the software used to maintain the meeting list. In the case of plugins, it's the plugin name, rather than the platform name.
* **software_version** version of the meeting list software

###locations.json
A location is a place with a single, unique address. To conform to the spec, locations should be address-verified, geocoded and not include building names or apartment / room numbers. (That information belongs in the meeting notes, because different meetings may be in different rooms at the same location.)

The locations.json file should show all locations in the area. Each location should have at least one meeting.

```
[
	{
		id: "saturday-nite-live",
		name: "Saturday Nite Live",
		address: "2634 Union Avenue",
		city: "San Jose",
		state: "CA",
		postal_code: "95124",
		country: "US",
		latitude: "37.2630048",
		longitude: "-121.9557222",
		timezone: "America/Los_Angeles",
		notes: "Maplewood Plaza",
		regions: [
			"West San Jose",
			"San Jose"
		],
		url: "http://aasanjose.org/locations/saturday-nite-live",
		updated: "2015-09-08 17:48:23"
	},{
		…
	}
]
```
* **id** may be an integer or a string, so long as it is uniqe to the location and URL-safe
* **name** the name of the location, not to contain any other information like address information, cross street, building physical description, room number, etc.
* **address** this address should be verified through a service such as Google, and should be free of apartment numbers or other secondary address information
* **city** address-verified city
* **state** two-letter US state code
* **postal_code** ZIP in the US
* **latitude** latitude
* **longitude** longitude
* **timezone** timezone in the [tz format](https://en.wikipedia.org/wiki/List_of_tz_database_time_zones)
* **notes** location notes apply to every meeting
* **regions** locations should generally belong to a single location, the exception is when a region is a part of another region, eg San Jose and Downtown.
* **url** optional link to all the meetings at a particular location
* **updated** GMT time when the location was created or last modified

###meetings.json
Meetings always occur at a location. Online, telephone, or roving meetings are not included in this API. When a meeting time is null it is considered 'by appointment.' All other meetings are at on a single day at a single time. Meetings that recur should be considered separate meetings.

The meetings.json file should show all meetings in the area.

```
{
	id: "after-work-topic-meeting",
	name: "After Work Topic Meeting",
	time: "18:00",
	day: "1",
	types: [
		"Open",
		"Wheelchair Accessible"
	],
	group: "Saturday Nite Live Group",
	notes: "",
	url: "http://aasanjose.org/meetings/after-work-topic-meeting",
	updated: "2014-05-31 00:32:23"
)
```

* **id** may be an integer or a string, so long as it is uniqe to the meeting and URL-safe
* **name** the name of the meeting, not to contain any other information like "Men Only" or "Chips Last Sunday".
* **time** 24-hour time, no AM or PM
* **types** free-form meeting types. These should ideally be very common meeting types.
* **group** Group name, optional. If this is the only meeting that the group has, it's really not necessary to put it there.
* **notes** meeting notes only apply to this meeting
* **url** link to the canonical place for information about the meeting
* **updated** GMT time when the location was created or last modified

##Moving Forward
This API is considered to be part of an evolving effort to share information. Future versions may include parameters or push changes.

Email suggestions or requests to [josh@aasanjose.org](mailto:josh@aasanjose.org).
