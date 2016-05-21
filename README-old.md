# 12 Step Meetings API

## Note
This is a proposal for version 2 of the API. Version 1 is documented here: [https://meetingguide.org/api](https://meetingguide.org/api)

## Overview
The purpose of this API is to facilitate the free exchange of timely, accurate information about twelve step meetings between interested parties. There should be as few limits as possible on access to data. 

The API is provided by 'areas' (geographic areas, not to be confused with General Service Areas). Frequently these areas are local intergroups. Possible consumers of the API are web and app developers, as well as search engines. 

This API specification is a product of a working group of the NAATW (National AA Technology Workshop). It's not directly affiliated with any particular program.

## Guidelines
1. **The API is read-only.** This system does not expose any security vulnerabilities because nothing destructive can happen to the data. The API does not accept commands or even parameters. 
1. **Individual anonymity must be protected.** The data must not contain any personally-identifying information without the explicit consent of the person involved. It's the responsibility of the publisher (the area) to obtain this consent.
1. **Data should originate from its primary source.** All information about an area's meetings must come from the area itself. Third-party information is not to be considered authoritative. Whenever information is listed by this API, it is believed to be canonical.
1. **The information should be accurate.** The area providing the data reasonably believes all information in the API to be current and accurate for public consumption.
1. **The data should be open to all.** The area should not place restrictions on who may access the data. The data should be freely available to all who request it. Authentication should not be necessary.
1. **The information should always be current.** The area should make the data publicly available at all times, and consumers should update their cache regularly. Temporary issues may affect synchronization, but after 72 hours the information must be considered expired and no longer valid.

## API File
The API is a single JSON file. It can be named anything, and located anywhere on a website. It should be linked from the head:

```
<meta name="12_step_meetings_api" content="/wp-admin/admin-ajax.php?action=api">
```
The area JSON should look like this:

```
{
	name: "Intergroup Central Office of Santa Clara County, Inc.",
	location: "San Jose, CA, USA",
	program: "AA",
	api_version: "1.0",
	software: "12 Step Meeting List",
	software_version: "1.6.2",
	locations: [
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
			updated: "2015-09-08T17:48:23Z"
			meetings: [
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
					updated: "2014-05-31T00:32:23Z"
				},{
					…
				}
			]
		},{
			…
		}
	]
}
```
* **name** is the official name of the institution providing the information. It's preferred that it be as short as possible.
* **location** is the primary location covered by this resource, generally the county name or the largest city in the area. Please avoid listing multiple cities if a single regional description is more concise.
* **program** is the name of the program, eg AA, NA, OA, etc.
* **api_version** the current version of this API is 1.0
* **software** (optional) the name of the software used to maintain the meeting list. In the case of plugins, it's the plugin name, rather than the platform name.
* **software_version** (optional) version of the meeting list software
* **locations** A location is a place with a single, unique address. Two different locations may not share the same address. Each location should be listed once.
	* **id** may be an integer or a string, so long as it is uniqe to the location and URL-safe
	* **name** the name of the location, not to contain any other information like address information, cross street, building physical description, room number, etc.
	* **address** this address should be verified through a service such as Google, and should be free of apartment numbers or other secondary address information. That should go in either the location notes or the meeting notes.
	* **city** city or locality
	* **state** two-letter state code in the US, free form in other countries
	* **postal_code** five-digit ZIP in the US, free form in other countries
	* **country** two-letter code
	* **latitude** latitude
	* **longitude** longitude
	* **timezone** timezone in the [tz format](https://en.wikipedia.org/wiki/List_of_tz_database_time_zones)
	* **notes** (optional) location notes are to be listed on every meeting at the location
	* **regions** locations should generally belong to a single region, the exception is when a region is a subset of another region, eg Downtown and San Jose.
	* **url** (optional) link to all the meetings at a particular location
	* **updated** GMT time when the location was created or last modified in the [ISO 8601](https://en.wikipedia.org/wiki/ISO_8601) format
	* **meetings** is an array of meetings at the location. every location must have at least one meeting. Meetings consist of:
		* **id** may be an integer or a string, so long as it is uniqe to the meeting and URL-safe
		* **name** the name of the meeting, not to contain any other information like "Men Only" or "Chips Last Sunday". It's common for the same names to be used by multiple meetings.
		* **time** 24-hour time, no AM or PM
		* **types** free-form meeting types. These should ideally be common meeting types, otherwise it will cause a lot of searches with no results.
		* **group** (optional) name of the group. If this is the only meeting that the group has, it's not necessary to put it there.
		* **notes** (optional) meeting notes only apply to this meeting
		* **url** link to the canonical place for information about the meeting
		* **updated** GMT time when the meeting was created or last modified in the [ISO 8601](https://en.wikipedia.org/wiki/ISO_8601) format


##Next Steps
This API is considered to be part of an evolving effort to share information. Future versions may include new features or changes. Email suggestions or requests to [josh@aasanjose.org](mailto:josh@aasanjose.org).