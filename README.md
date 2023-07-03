# Meeting Guide API

The goal of the Meeting Guide API is help sync information about AA meetings. It was developed for the [Meeting Guide app](https://www.aa.org/meeting-guide-app), but it is non-proprietary and other systems are encouraged to make use of it.

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

### Meeting Guide requirements

Some applications have requirements about what content needs to be in the feed. Meeting Guide, for example, requires `slug`, `day`, `time`, as well as geographic information to be present for it to be imported.

#### Why is slug necessary?

Slug is a required unique field because there is an app feature where users may 'favorite' a meeting, and in order for that to persist across sessions we must attach it to a unique field. It might seem intuitive that meeting location + time would be a unique combination, but in practice we see cases where there are in fact simultaneous meetings at the same location.

#### Why are day and time required?

It's perfectly fine for meetings to be 'by appointment' and this often happens in places where there are not many meetings. The app, however, needs this information to present useful information to the user.

#### Why is geographic information necessary for online-only meetings?

Meeting Guide has far too many meetings in its database to expose them all to individual users. To present only the most relevant information, Meeting Guide selects meetings that are "nearby" - even if that meeting is online. In these cases, the location can be thought of as a point of origin for the meeting, or a geographic affinity.

Use approximate locations for these meetings. `formatted_address` is the most flexible field for this, and values can be things like: `Wicker Park, Chicago, IL, USA` (neighborhood), `Chicago, IL, USA` (city), or `Illinois, USA` (state). It's also fine to use `country`, `city`, and `state` fields atomically.

These locations should standardized (can you find it with Google Maps?), and imply the time zone. For this reason, don't use a broad geographic area like `United States` that spans multiple time zones.

### Why can't we have HTML in meeting notes?

Data should be portable across a range of devices, some of which might not display HTML.

### What about business meetings or other monthly meetings?

This API is for weekly recovery meetings.

## Meeting Types

The codes below are only used for transmitting meeting data. App users will only see the full definitions.

The codes below should be considered 'reserved.' In your implementation, it's ok to alter the description (for example
"Topic Discussion" rather than "Discussion") so long as the intent is the same. For example, "Child Care Available" is a common substitute
for "Babysitting Available." It's also ok to add types,
they will be ignored by the importer, but be careful not to use any existing or proposed codes.

<!-- Types -->
|Code|English|Español|Français|日本語|Svenska|
|---|---|---|---|---|---|
|`11`|11th Step Meditation|Meditación del Paso 11|Méditation sur la 11e Étape|ステップ11 黙想|11th Stegs Meditation|
|`12x12`|12 Steps & 12 Traditions|12 Pasos y 12 Tradiciones|12 Étapes et 12 Traditions|12のステップと12の伝統|12 Steg & 12 Traditioner|
|`A`|Secular|Secular|Séculier|無宗教|Sekulärt|
|`ABSI`|As Bill Sees It|Como lo ve Bill|Réflexions de Bill|ビルはこう思う|Som Bill Ser Det|
|`AL`|Concurrent with Alateen|Concurrente con Alateen|En même temps qu’Alateen|アラティーンと同時進行|Tillsammans med Alateen|
|`AL-AN`|Concurrent with Al-Anon|Concurrente con Al-Anon|En même temps qu’Al-Anon|アラノンと同時進行|Tillsammans med Al-Anon|
|`ASL`|American Sign Language|Lenguaje por señas|Langage des Signes|アメリカ手話|Amerikanskt teckenspråk|
|`B`|Big Book|Libro Grande|Gros Livre|ビッグブック|Stora Boken|
|`BA`|Babysitting Available|Guardería disponible|Garderie d’enfants disponible|ベビーシッターあり|Barnvakt Finns|
|`BE`|Newcomer|Principiantes|Nouveau/nouvelle|ビギナーズ|Nykomling|
|`BI`|Bisexual|Bisexual|Bisexuel|バイセクシャル|Bisexuellt|
|`BRK`|Breakfast|Desayuno|Petit déjeuner|朝食|Frukost|
|`C`|Closed|Cerrada|Fermé|クローズド|Slutet|
|`CAN`|Candlelight|Luz de una vela|À la chandelle|キャンドル|Tända Ljus|
|`CF`|Child-Friendly|Niño amigable|Enfants acceptés|お子さま歓迎|Barnvänligt|
|`D`|Discussion|Discusión|Discussion|ディスカッション|Diskussion|
|`DB`|Digital Basket|Canasta digital|Panier numérique|電子献金|Digital Korg|
|`DD`|Dual Diagnosis|Diagnóstico dual|Double diagnostic|重複診断|Dubbel Diagnos|
|`DR`|Daily Reflections|Reflexiones Diarias|Réflexions quotidiennes|今日を新たに|Dagliga Reflektioner|
|`EN`|English|Inglés|Anglais|英語|Engelska|
|`FF`|Fragrance Free|Sin fragancia|Sans parfum|香水なし|Parfym Fritt|
|`FR`|French|Francés|Français|フランス語|Franska|
|`G`|Gay|Gay|Gai|ゲイ|Gay|
|`GR`|Grapevine|La Viña|Grapevine|グレープバイン|Grapevine|
|`H`|Birthday|Cumpleaños|Anniversaire|バースデー|Födelsedag|
|`HE`|Hebrew|Hebreo|Hébreu|ヘブライ語|Hebreiska|
|`ITA`|Italian|Italiano|Italien|イタリア語|Italienska|
|`JA`|Japanese|Japonés|Japonais|日本語|Japanska|
|`KOR`|Korean|Coreano|Coréen|韓国語|Koreanska|
|`L`|Lesbian|Lesbianas|Lesbienne|レズビアン|Lesbiskt|
|`LGBTQ`|LGBTQ|LGBTQ|LGBTQ|LGBTQ|LGBTQ|
|`LIT`|Literature|Literatura|Publications|書籍|Litteratur|
|`LS`|Living Sober|Viviendo Sobrio|Vivre… Sans alcool|リビングソーバー|Leva Nyktert|
|`M`|Men|Hombres|Hommes|男性|Mansmöte|
|`MED`|Meditation|Meditación|Méditation|黙想|Meditationsmöte|
|`N`|Native American|Nativo Americano|Autochtone|ネイティブアメリカン|Ur-amerikanskt|
|`NDG`|Indigenous|Indígena|Indigène|先住民|Urfolkligt|
|`O`|Open|Abierta|Ouvert(e)|オープン|Öppet|
|`OUT`|Outdoor|Al aire libre|En plein air|アウトドア|Utomhus|
|`P`|Professionals|Profesionales|Professionnels|職業人|Professionella|
|`POC`|People of Color|Gente de color|Gens de couleur|有色人種|Färgade|
|`POL`|Polish|Polaco|Polonais|ポーランド語|Polska|
|`POR`|Portuguese|Portugués|Portugais|ポルトガル語|Portugisiska|
|`PUN`|Punjabi|Punjabi|Pendjabi|パンジャブ語|Punjabi|
|`RUS`|Russian|Ruso|Russe|ロシア語|Ryska|
|`S`|Spanish|Español|Espagnol|スペイン語|Spanska|
|`SEN`|Seniors|Personas mayores|Séniors|シニア|Seniorer|
|`SM`|Smoking Permitted|Se permite fumar|Permis de fumer|喫煙可|Rökning Tillåten|
|`SP`|Speaker|Orador|Conférencier|スピーカー|Talare|
|`ST`|Step Study|Estudio de pasos|Sur les Étapes|ステップ|Stegmöte|
|`T`|Transgender|Transgénero|Transgenre|トランスジェンダー|Transpersoner|
|`TC`|Location Temporarily Closed|Ubicación temporalmente cerrada|Emplacement temporairement fermé|一時的休止中|Tillfälligt Stängt|
|`TR`|Tradition Study|Estudio de tradicion|Étude des Traditions|伝統|Traditionsmöte|
|`W`|Women|Mujer|Femmes|女性|Kvinnomöte|
|`X`|Wheelchair Access|Acceso en silla de ruedas|Accès aux fauteuils roulants|車いすアクセス|Handikappanpassat|
|`XB`|Wheelchair-Accessible Bathroom|Baño accesible para sillas de ruedas|Toilettes accessibles aux fauteuils roulants|車いす使用者用トイレ|Handikappanpassad WC|
|`XT`|Cross Talk Permitted|Se permite opinar|Conversation croisée permise|クロストーク可能|Kommentarer Tilltåtna|
|`Y`|Young People|Gente joven|Jeunes|ヤング|Young People|
<!-- End Types -->

## Proposed New Types

The following types are proposed for future use. They are not currently in use in the app.

<!-- Proposed Types -->
|Code|English|Español|Français|日本語|Svenska|
|---|---|---|---|---|---|
|`AM`|Amharic|Amárico|Amharique|アムハラ語|Amhariska|
|`BV-I`|Blind / Visually Impaired|Ciego / Discapacidad Visual|Aveugle / Malvoyant|視覚障害者|Blind / Synskadad|
|`D-HOH`|Deaf / Hard of Hearing|Sordo / Duro de Oído|Sourd / Malentendant|聴覚障害者|Döv / Hörselskadad|
|`DA`|Danish|Danés|Danois|デンマーク語|Danska|
|`DE`|German|Alemán|Allemand|ドイツ語|Tyska|
|`EL`|Greek|Griego|Grec|ギリシャ語|Grekiska|
|`FA`|Persian|Persa|Persan|ペルシア語|Persiska|
|`HI`|Hindi|Hindi|Hindi|ヒンディー語|Hindi|
|`HR`|Croatian|Croata|Croate|クロアチア語|Kroatiska|
|`HU`|Hungarian|Húngaro|Hongrois|ハンガリー語|Ungerska|
|`LO-I`|Loners / Isolationists|Solitarios / Aislacionistas|Solitaires / Isolationnistes|孤独 / 孤立主義者|Ensamvargar / Isolationister|
|`LT`|Lithuanian|Lituano|Lituanien|リトアニア語|Litauiska|
|`ML`|Malayalam|Malayalam|Malayalam|マラヤーラム語|Malayalam|
|`POA`|Proof of Attendance|Prueba de Asistencia|Preuve de Présence|出席証明|Närvarobevis|
|`QSL`|Quebec Sign Language|Lengua de Señas de Quebec|Langue des Signes Québécoise|ケベック手話|Quebecskt Teckenspråk|
|`RSL`|Russian Sign Language|Lengua de Señas Rusa|Langue des Signes Russe|ロシア手話|Ryskt Teckenspråk|
|`SK`|Slovak|Eslovaco|Slovaque|スロバキア語|Slovakiska|
|`SV`|Swedish|Sueco|Suédois|スウェーデン語|Svenska|
|`TH`|Thai|Tailandés|Thaï|タイ語|Thailändska|
|`TL`|Tagalog|Tagalo|Tagalog|タガログ語|Tagalog|
|`UK`|Ukrainian|Ucraniano|Ukrainien|ウクライナ語|Ukrainska|
<!-- End Proposed Types -->

## Proposed Changed Types

The following types being considered for a name change.

<!-- Proposed Changed Types -->
|Code|English|Español|Français|日本語|Svenska|
|---|---|---|---|---|---|
|`LGBTQ`|LGBTQIAA+|LGBTQIAA+|LGBTQIAA+|LGBTQIAA+|HBTQIAA+|
<!-- End Proposed Changed Types -->

## Sharing Your Data

If you choose, you may make your feed discoverable by linking to it (like RSS) in your site's `<HEAD>`.

```HTML
<link rel="alternate" type="application/json" title="Meetings Feed" href="https://example.com/etc/meetings-feed">
```

The script may have any name, and be in any directory, but it should be a fully qualified URL, and the `title="Meetings Feed"` attribute is required.

## Next Steps

Some possible next steps for this format include:

- metadata so that service entities can indicate their preferred name and URL
- contact information for following up on issues with feed or meeting info
- language split out into its own fields
- indication of which language was used for geocoding
