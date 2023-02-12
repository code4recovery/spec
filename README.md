# Meeting Guide API

The goal of the Meeting Guide API is help sync information about AA meetings. It was developed for the [Meeting Guide app](https://www.aa.org/pages/en_US/meeting-guide), but it is non-proprietary and other systems are encouraged to make use of it.

If you have feedback, please put an issue on this repository.

## Usage

To implement the API on your server, create a file that can take information from your database and format it in the correct specification (see below).

For security, your script should not accept any parameters. It should be read-only.

Your data must not break anyone's anonymity. No last names should be used in meeting notes, and no one's face should be pictured in meeting images.

You may test your feed with the [Meeting Guide JSON Feed Validator](https://meetingguide.org/validate). Once it's ready, or if you have questions, see [How to Connect to Meeting Guide](https://meetingguide.helpdocs.io/article/g0ykqkdq0u-connecting-to-meeting-guide-step-by-step).

If you would like to share your script, we'll include a copy in this repository so that it might help future users.

## Specification

The JSON file is expected to contain a simple array of meetings. [Here is an example](https://sheets.code4recovery.org/storage/12Ga8uwMG4WJ8pZ_SEU7vNETp_aQZ-2yNVsYDFqIwHyE.json) of a live JSON feed.

```JSON
[
	{
		"name": "Sunday Serenity",
		"slug": "sunday-serenity-14",
		"day": 0,
		"time": "18:00",
		"end_time": "19:30",
		"location": "Alano Club",
		"group": "The Serenity Group",
		"notes": "Ring buzzer. Meeting is on the 2nd floor.",
		"updated": "2014-05-31 14:32:23",
		"url": "https://intergroup.org/meetings/sunday-serenity",
		"types": [
			"O",
			"T",
			"LGBTQ"
		],
		"address": "123 Main Street",
		"city": "Anytown",
		"state": "CA",
		"postal_code": "98765",
		"country": "US",
		"approximate": "no"
	},
	...
]
```

`name` is a required string. It should be the meeting name, where possible. Some areas use group names instead, although that's more abiguous. 255 characters max.

`slug` is required, and must be unique to your data set. It should preferably be a string, but integer IDs are fine too.

`day` is required and may be an integer or an array of integers 0-6, representing Sunday (0) through Saturday (6).

`time` is a required five-character string in the `HH:MM` 24-hour time format.

`end_time` is an optional five-character string in the `HH:MM` 24-hour time format.

`timezone` is an optional string in [tz database format](https://en.wikipedia.org/wiki/List_of_tz_database_time_zones) e.g. `America/New_York`.

`types` is an optional array of standardized meeting types. See the types section below.

`notes` is an optional long text field to hold additional details about the meeting. Line breaks are ok, but HTML will be stripped.

`conference_url` is an optional URL to a specific public videoconference meeting. This should be a common videoconferencing service such as Zoom or Google Hangouts. It should launch directly into the meeting and not link to an intermediary page.

`conference_url_notes` is an optional string which contains metadata about the `conference_url` (eg meeting password in plain text for those groups unwilling to publish a one-tap URL).

`conference_phone` is telephone number to dial into a specific meeting. Should be numeric, except a `+` symbol may be used for international dialers, and `,`, `*`, and `#` can be used to form one-tap phone links.

`conference_phone_notes` is an optional string with metadata about the `conference_phone` (eg a numeric meeting password or other user instructions).

`location` is an optional string and should be a recognizable building or landmark name.

`location_notes` is an optional long text field with notes applying to all meetings at the location.

`formatted_address` either this or the address / city / state / postal_code / country combination are required.

`address`, `city`, `state`, `postal_code`, and `country` are all optional strings, but together they must form an address that Google can identify. `address` and `city` are suggested. Take special care to strip extra information from the address, such as 'upstairs' or 'around back,' since this is the primary cause of geocoding problems. (That information belongs in the `notes` field.) Intersections are usually ok, but approximate addresses, such as only a city or route, do not have enough precision to be listed in the app.

`latitude` and `longitude` are optional numeric values indicating the geoposition of the meeting. Only five decimal places of precision are necessary here (1.11m). These values are ignored by the Meeting Guide importer.

`approximate` is an optional stringified boolean value, that, when present, indicates whether the address is an approximate location (`"yes"`) or a specific point on a map such as a street address (`"no"`). This is ignored by the Meeting Guide importer.

`region` is an optional string that represents a geographical subset of meeting locations. Usually this is a neighborhood or city. District numbers are discouraged because they require special program knowledge to be understood.

`updated` is an optional UTC timestamp in the format `YYYY-MM-DD HH:MM:SS` and indicates when the listing was last updated.

`image` is an optional url that should point to an image representing the location. We recommend an image of the building's facade. Ideally this is a JPG image 1080px wide by 540px tall.

`group` is an optional string representing the name of the group providing the meeting. Groups can hold meetings in multiple locations.

`group_notes` is an optional long text field. Line breaks are ok, but HTML will be stripped.

`venmo` is an optional string and should be a [Venmo handle](https://help.venmo.com/hc/en-us/articles/235432448-Check-or-Edit-Your-Username), eg `@AAGroupName`. This is understood to be the address for 7th Tradition contributions to the meeting, and not any other entity.

`square` is an optional string and should be a [Square Cash App cashtag](https://cash.app/help/us/en-us/3123-cashtags), eg `$AAGroupName`. This is understood to be the address for 7th Tradition contributions to the meeting, and not any other entity.

`paypal` is an optional string and should be a [PayPal.me username](https://www.paypal.com/us/cshelp/article/what-is-paypalme-help432), eg `AAGroupName`. This is understood to be the address for 7th Tradition contributions to the meeting, and not any other entity.

`url` is optional and should point to the meeting's listing on the area website.

`edit_url` is an optional string URL that trusted servants can use to edit the specific meeting's listing.

`feedback_url` is an optional string URL that can be used to provide feedback about the meeting. These could be local links, eg `/feedback?meeting=meeting-slug-1`, remote links, eg `https://typeform.com/to/23904203?meeting=meeting-slug-1`, or email links, eg `mailto:webservant@domain.org?subject=meeting-slug-1`.

## Common Questions & Concerns

### We use different meeting codes!

That's ok. App users don't actually see the codes, just the types they translate to.

### Our meeting type isn't listed!

Types have to be consistent across the app to make a good user experience. It's common that a user might see meeting results from several areas at a time (this happens in small areas, and near borders). The set of meeting types we use is a mutually-agreed-upon set of names across 70+ areas. If you have a request to edit the list, we will bring it up at our steering committee meeting.

### Why is slug necessary?

Slug is a required unique field because there is an app feature where users may 'favorite' a meeting, and in order for that to persist across sessions we must attach it to a unique field. It might seem intuitive that meeting location + time would be a unique combination, but in practice we see cases where there are in fact simultaneous meetings at the same location.

### Why are day and time required?

It's perfectly fine for meetings to be 'by appointment' and this often happens in places where there are not many meetings. The app, however, needs this information to present useful information to the user.

### Why can't we have HTML in meeting notes?

We are trying to make the data portable across a range of devices, some of which might not display HTML.

### What about business meetings or other monthly meetings?

This API is for weekly recovery meetings.

## Meeting Types

The codes below are only used for transmitting meeting data. App users will only see the full definitions.

The codes below should be considered 'reserved.' In your implementation, it's ok to alter the description (for example
"Topic Discussion" rather than "Discussion") so long as the intent is the same. "Child Care Available" is a common substitute
for "Babysitting Available." "American Sign Language" or "ASL" rather than "Sign Language." It's also ok to add types,
they will be ignored by the importer.

| Code    | English                        | Español                              | Français                                     |
| ------- | ------------------------------ | ------------------------------------ | -------------------------------------------- |
| `11`    | 11th Step Meditation           | Meditación del Paso 11               | Méditation sur la 11e Étape                  |
| `12x12` | 12 Steps & 12 Traditions       | 12 Pasos y 12 Tradiciones            | 12 Étapes et 12 Traditions                   |
| `A`     | Secular                        | Secular                              | Séculier                                     |
| `ABSI`  | As Bill Sees It                | Como lo ve Bill                      | Réflexions de Bill                           |
| `AL`    | Concurrent with Alateen        | Concurrente con Alateen              | En même temps qu’Alateen                     |
| `AL-AN` | Concurrent with Al-Anon        | Concurrente con Al-Anon              | En même temps qu’Al-Anon                     |
| `ASL`   | American Sign Language         | Lenguaje por señas                   | Langage des Signes                           |
| `B`     | Big Book                       | Libro Grande                         | Gros Livre                                   |
| `BA`    | Babysitting Available          | Guardería disponible                 | Garderie d’enfants disponible                |
| `BE`    | Newcomer                       | Principiantes                        | Nouveau/nouvelle                             |
| `BI`    | Bisexual                       | Bisexual                             | Bisexuel                                     |
| `BRK`   | Breakfast                      | Desayuno                             | Petit déjeuner                               |
| `C`     | Closed                         | Cerrada                              | Fermé                                        |
| `CAN`   | Candlelight                    | Luz de una vela                      | À la chandelle                               |
| `CF`    | Child-Friendly                 | Niño amigable                        | Enfants acceptés                             |
| `D`     | Discussion                     | Discusión                            | Discussion                                   |
| `DB`    | Digital Basket                 | Canasta digital                      | Panier numérique                             |
| `DD`    | Dual Diagnosis                 | Diagnóstico dual                     | Double diagnostic                            |
| `DR`    | Daily Reflections              | Reflexiones Diarias                  | Réflexions quotidiennes                      |
| `EN`    | English                        | Inglés                               | Anglais                                      |
| `FF`    | Fragrance Free                 | Sin fragancia                        | Sans parfum                                  |
| `FR`    | French                         | Francés                              | Français                                     |
| `G`     | Gay                            | Gay                                  | Gai                                          |
| `GR`    | Grapevine                      | La Viña                              | Grapevine                                    |
| `H`     | Birthday                       | Cumpleaños                           | Anniversaire                                 |
| `HE`    | Hebrew                         | Hebreo                               | Hébreu                                       |
| `ITA`   | Italian                        | Italiano                             | Italien                                      |
| `JA`    | Japanese                       | Japonés                              | Japonais                                     |
| `KOR`   | Korean                         | Coreano                              | Coréen                                       |
| `L`     | Lesbian                        | Lesbianas                            | Lesbienne                                    |
| `LGBTQ` | LGBTQ                          | LGBTQ                                | LGBTQ                                        |
| `LIT`   | Literature                     | Literatura                           | Publications                                 |
| `LS`    | Living Sober                   | Viviendo Sobrio                      | Vivre… Sans alcool                           |
| `M`     | Men                            | Hombres                              | Hommes                                       |
| `MED`   | Meditation                     | Meditación                           | Méditation                                   |
| `N`     | Native American                | Nativo Americano                     | Autochtone                                   |
| `NDG`   | Indigenous                     | Indígena                             | Indigène                                     |
| `O`     | Open                           | Abierta                              | Ouvert(e)                                    |
| `OUT`   | Outdoor                        | Al aire libre                        | En plein air                                 |
| `P`     | Professionals                  | Profesionales                        | Professionnels                               |
| `POC`   | People of Color                | Gente de color                       | Gens de couleur                              |
| `POL`   | Polish                         | Polaco                               | Polonais                                     |
| `POR`   | Portuguese                     | Portugués                            | Portugais                                    |
| `PUN`   | Punjabi                        | Punjabi                              | Pendjabi                                     |
| `RUS`   | Russian                        | Ruso                                 | Russe                                        |
| `S`     | Spanish                        | Español                              | Espagnol                                     |
| `SEN`   | Seniors                        | Personas mayores                     | Séniors                                      |
| `SM`    | Smoking Permitted              | Se permite fumar                     | Permis de fumer                              |
| `SP`    | Speaker                        | Orador                               | Conférencier                                 |
| `ST`    | Step Study                     | Estudio de pasos                     | Sur les Étapes                               |
| `T`     | Transgender                    | Transgénero                          | Transgenre                                   |
| `TC`    | Location Temporarily Closed    | Ubicación temporalmente cerrada      | Emplacement temporairement fermé             |
| `TR`    | Tradition Study                | Estudio de tradicion                 | Étude des Traditions                         |
| `W`     | Women                          | Mujer                                | Femmes                                       |
| `X`     | Wheelchair Access              | Acceso en silla de ruedas            | Accès aux fauteuils roulants                 |
| `XB`    | Wheelchair-Accessible Bathroom | Baño accesible para sillas de ruedas | Toilettes accessibles aux fauteuils roulants |
| `XT`    | Cross Talk Permitted           | Se permite opinar                    | Conversation croisée permise                 |
| `Y`     | Young People                   | Gente joven                          | Jeunes                                       |

## Sharing Your Data

If you choose, you may make your feed discoverable by linking to it (like RSS) in your site's `<HEAD>`.

```HTML
<link rel="alternate" type="application/json" title="Meetings Feed" href="https://example.com/etc/meetings-feed">
```

The script may have any name, and be in any directory, but it should be a fully qualified URL, and the `title="Meetings Feed"` attribute is required.

## Next Steps

Some possible next steps for this format include:

- organizational metadata so that an org can indicate its preferred name and URL
- contact information for following up on issues with feed or meeting info
- language split out into its own fields
- indication of which language was used for geocoding
