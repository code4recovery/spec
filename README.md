# Meeting Guide API

The goal of the Meeting Guide API is help sync information about AA meetings. It was developed for the [Meeting Guide app](https://www.aa.org/meeting-guide-app), but it is non-proprietary and other systems are encouraged to make use of it.

If you have feedback, please put an issue on this repository.

## Usage

To implement the API on your server, create a script that can take information from your database and format it in the correct specification (see below).

Your data must not break anyone's anonymity. No last names should be used in meeting data.

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
		"url": "https://district123.org/meetings/sunday-serenity",
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
		"approximate": "no",
		"entity": "District 123",
		"entity_email": "info@district123.org",
		"entity_location": "Example County, California",
		"entity_logo": "https://district123.org/images/logo.svg",
		"entity_phone": "+1-123-456-7890",
		"entity_url": "https://district123.org",
		"feedback_emails": [
			"meetingupdates@district123.org"
		],
	},
	...
]
```

## Table Format

Alternately, the data can be in a table format, such as in a Google Sheet ([template](https://docs.google.com/spreadsheets/d/1iA8oVtddHVEZ8gslWPlTrfBfbgJpiS0Tt6sFOTi_5dk/edit#gid=687617754)). The first row should be a header row, and the columns can be in any order. The column names should be plain-language versions of the JSON keys, for example `conference_url` becomes `Conference URL`. Column names currently must be in English, but the data can be in any language.

## Field Definitions

### Required Fields

Many fields can be included in a meeting listing, but only two are required in each one: `name` and `slug`. When an optional field does not apply to a meeting it can be omitted.

#### `name`

Name is a required string. It should be the meeting name, where possible. Some areas use group names instead, although that's more ambiguous. 255 characters max. Best practices for meeting names:

- make the name fewer than 64 characters so it's not truncated in the app.
- don't include information that can be contained elsewhere in the listing, such as the day, time, and type
- avoid using the words `AA` and `meeting` in the name, because it is redundant

#### `slug`

Slug is required, and must be unique to your data set. It should preferably be a string, but integer IDs are fine too. This is the primary key for the meeting, and is used to identify the meeting in the app, and to form bookmark URLs. It should be URL-safe, and not contain spaces or special characters. It should be 64 characters max, but ideally shorter. Usually it is a representation of the meeting name. When a name belongs to several meetings, a number can be used to disambiguate, for example `sunday-serenity-7`.

When using a Google Sheet, this field is should be called `ID`.

### Time Fields

Some meetings are "by appointment" and do not have a specific time. For weekly meetings, `day` and `time` are required.

Note: the Meeting Guide spec is to be used for weekly meetings only. It is recommended to use a separate page to list monthly or non-recurring meetings.

The Meeting Guide app only displays meetings that have a day and time. TSML UI displays appointment meetings at the bottom.

#### `day`

Day is required for meetings that are not by appointment and may be an integer or an array of integers 0-6, representing Sunday (`0`) through Saturday (`6`).

#### `time`

Time is required for meetings that are not by appointment and is a five-character string in the `HH:MM` 24-hour time format.

#### `end_time`

End Time is an optional five-character string in the `HH:MM` 24-hour time format. The Meeting Guide app and TSML UI use this value when present when adding a meeting to a user's calendar. Also TSML UI uses this to display a "meetings in progress" banner at the top. It will default to one hour after the start time if omitted.

#### `timezone`

Timezone is an optional string in [tz database format](https://en.wikipedia.org/wiki/List_of_tz_database_time_zones) (for example `America/New_York`). This is helpful when displaying meetings in a variety of time zones using TSML UI.

### Geographic Fields

To be listed in the Meeting Guide app, meetings must also have some geographic information. This can be either in the format:

```json
"formatted_address": "4953 W Addison St, Chicago, IL 60641, USA"
```

or separated into individual fields:

```json
"address": "4953 W Addison St",
"city": "Chicago",
"state": "IL",
"postal_code": "60641",
"country": "US"
```

Take special care to strip extra information from the address, such as 'upstairs' or 'around back,' since this is the primary cause of geocoding problems. That information belongs in the `notes` field, see below.

Online meetings may have an approximate address (for example `Chicago, IL, USA`), but in-person meetings must have a specific address.

### Region Fields

#### `region`

Region is an optional string that represents a geographical subset of meeting locations. Usually this is a neighborhood or city. District numbers are discouraged because they require special program knowledge to be understood. This is ignored by the Meeting Guide importer but helpful for TSML UI.

#### `sub_region`

Sub Region can be used when `region` is present to further specify the location. This is ignored by the Meeting Guide importer but helpful for TSML UI.

#### `regions`

Regions can be used instead of Region and Sub Region to support any number of hierarchical regions. For example:

```json
"regions": ["Illinois", "Chicago", "Wicker Park"]
```

When using regions, the most general region is first, and the most specific is last. There should be variation among your top-level regions. For example, a statewide meeting finder whose meetings were all within Illinois should not include `Illinois` as the top level. A region that's shared across all meetings should be omitted.

When using a Google Sheet, you may separate multiple regions with a `>`.

Online meetings may belong to geographic regions, which represents the origin or affinity of the meeting, even if its members may be more geographically diverse. Alternately, some sites use an arbitrary region, such as `Online`.

### Online Meeting Fields

Online meetings must have either a `conference_url` and/or `conference_phone` field to be considered active.

#### `conference_url`

Conference URL is an optional string and should be a common videoconferencing service such as Zoom or Google Hangouts. It should launch directly into the meeting and not link to an intermediary page.

#### `conference_url_notes`

Conference URL Notes is an optional string which contains metadata about the `conference_url` (for example a meeting password in plain text for those groups unwilling to publish a one-tap URL).

#### `conference_phone`

Conference Phone is an optional telephone number to dial into a specific meeting. Should be numeric, except a `+` symbol may be used for international dialers, and `,`, `*`, and `#` can be used to form one-tap phone links.

#### `conference_phone_notes`

Conference Phone Notes is an optional string with metadata about the `conference_phone` (for example a numeric meeting password or other user instructions).

### Recommended Fields

#### `types`

Types is an optional array of standardized meeting types. See [Meeting Types](#meeting-types) below. While this field is optional, it is highly recommended to include it. The app uses this field to filter meetings, and it's a primary way that users find meetings. While not every meeting will have types, most meetings should.

#### `notes`

Notes is an optional long text field to hold additional details about the meeting. Line breaks are ok, but HTML will be stripped. As opposed to `location_notes` and `group_notes` (see below), the `notes` field is not shared with other meetings, so it's a good place to put any freeform information that is specific to this meeting.

No HTML or other formatting should go in this field. It is plain text.

#### `location`

Location is an optional string and should be a recognizable building or landmark name. Most apps will share this field with other meetings at the same address.

No HTML or other formatting should go in this field. It is plain text.

#### `location_notes`

Location Notes is an optional long text field with notes applying to all meetings at the location. Most apps will share this field with other meetings at the same address.

### Optional Fields

#### `latitude` and `longitude`

Latitude and Longitude are optional numeric values indicating the geoposition of the meeting. Only five decimal places of precision are necessary here (1.11m). These values are ignored by the Meeting Guide importer, but are helpful for TSML UI, which uses them to display a map of the meeting location, and infer that the meeting address is not approximate.

#### `approximate`

Approxmiate is an optional stringified boolean value, that, when present, indicates whether the address is an approximate location (`"yes"`) or a specific point on a map such as a street address (`"no"`). This is ignored by the Meeting Guide importer but is used by TSML UI when `latitude` and `longitude` are not present.

#### `updated`

Updated is an optional UTC timestamp in the format `YYYY-MM-DD HH:MM:SS` and indicates when the listing was last updated.

#### `group`

Group is an optional string representing the name of the group providing the meeting. Groups can hold meetings in multiple locations. This is ignored by the Meeting Guide importer but is used by TSML UI.

#### `group_notes`

Group Notes is an optional long text field. Line breaks are ok, but HTML will be stripped. This is ignored by the Meeting Guide importer but is used by TSML UI. When importing, the 12 Step Meeting List plugin will apply this value to all meetings that share the same group name.

No HTML or other formatting should go in this field. It is plain text.

#### `venmo`

Venmo is an optional string and should be a [Venmo handle](https://help.venmo.com/hc/en-us/articles/235432448-Check-or-Edit-Your-Username), for example `@AAGroupName`. This is understood to be the address for 7th Tradition contributions to the meeting, and not any other entity.

#### `square`

Square is an optional string and should be a [Square Cash App cashtag](https://cash.app/help/us/en-us/3123-cashtags), for example `$AAGroupName`. This is understood to be the address for 7th Tradition contributions to the meeting, and not any other entity.

#### `paypal`

PayPal is an optional string and should be a [PayPal.me username](https://www.paypal.com/us/cshelp/article/what-is-paypalme-help432), for example `AAGroupName`. This is understood to be the address for 7th Tradition contributions to the meeting, and not any other entity.

#### `homegroup_online`

`homegroup_online` is an optional string and should be a [Homegroup Online](https://homegroup.online) group code, such as `tbc` [in this example](https://donate.homegroup.online/tbc/). This is understood to be the address for 7th Tradition contributions to the meeting, and not any other entity.

#### `url`

URL is optional and should point to the meeting's listing on the area website. This is used by the Meeting Guide app but not TSML UI.

#### `edit_url`

Edit URL is an optional string URL that trusted servants can use to edit the specific meeting's listing. This is ignored by Meeting Guide but used by TSML UI.

#### `feedback_emails`

Feedback Emails is an array of feedback addresses for the service entity responsible for the listing. When using a Google Sheet, separate multiple addresses with a `,`.

#### `feedback_url`

Feedback URL is an optional string URL that can be used instead of `feedback_emails` to provide feedback about the meeting. These can be on-site or off-site absolute URLs, for example:

- `https://example.org/feedback?meeting=meeting-slug-1`
- `https://typeform.com/to/23904203?meeting=meeting-slug-1`
- `mailto:webservant@domain.org?subject=meeting-slug-1`.

### Service Entity Fields

#### `entity`

Entity is the name of the service entity responsible for the listing. entity info is optional, but `entity` is required if any of the other entity fields are present.

#### `entity_email`

Entity Email is a public email address for the service entity responsible for the listing. This should be a single email address.

#### `entity_location`

Entity Location is a human-readable physical description of the service area of the entity, for example `Whatcom County, Washington`.

#### `entity_logo`

Entity Logo is the URL of the logo of the service entity responsible for the listing. It should begin with `https://`. Ideally the image this points to is a vector-based SVG so it can be scaled to any size. Additionally, the image should be square, and have a transparent background. Finally, colors should be specified using `currentColor` so that they can adapt to the color mode (light, dark) of the app.

#### `entity_phone`

Entity Phone is the phone number of the service entity responsible for the listing. Should be in the format `+1-123-456-7890` and start with country code for international dialing.

#### `entity_website_url`

Entity Website URL is the website homepage of the service entity responsible for the listing. This should begin with `https://`.

### Contact Fields

Contact fields are optional, and are typically used in places where meeting density is low, and users may need to contact the meeting to confirm its status.

Contact fields should not break anyone's anonymity. No last names should be used in contact information.

`contact_1_name` is the name of the first contact person for the meeting.

`contact_1_email` is the email address of the first contact person for the meeting.

`contact_1_phone` is the phone number of the first contact person for the meeting.

`contact_2_name` is the name of the second contact person for the meeting.

`contact_2_email` is the email address of the second contact person for the meeting.

`contact_2_phone` is the phone number of the second contact person for the meeting.

`contact_3_name` is the name of the third contact person for the meeting.

`contact_3_email` is the email address of the third contact person for the meeting.

`contact_3_phone` is the phone number of the third contact person for the meeting.

## Common Questions & Concerns

### We use different meeting codes!

That's ok. App users don't actually see the codes, just the types they translate to.

### Our meeting type isn't listed!

Types have to be consistent across the app to make a good user experience. It's common that a user might see meeting results from several areas at a time (this happens in small areas, and near borders). The set of meeting types we use is a mutually-agreed-upon set of names across 70+ areas. If you have a request to edit the list, we will bring it up at our steering committee meeting.

### Meeting Guide requirements

Some applications have requirements about what content needs to be in the feed. Meeting Guide, for example, requires `slug`, `day`, `time`, as well as geographic information to be present for it to be imported.

#### Why is slug / ID necessary?

Slug is a required unique field because there is an app feature where users may "favorite" a meeting, and in order for that to persist across sessions we must attach it to a unique field.

#### Why are day and time required?

It's perfectly fine for meetings to be 'by appointment' and this often happens in places where there are not many meetings. The Meeting Guide app, however, needs this information to present useful information to the user.

#### Why is geographic information necessary for online-only meetings?

Meeting Guide has far too many meetings in its database to expose them all to individual users. To present only the most relevant information, Meeting Guide selects meetings that are "nearby" - even if that meeting is online. In these cases, the location can be thought of as a point of origin for the meeting, or a geographic affinity.

Use approximate locations for these meetings. `formatted_address` is the most flexible field for this, and values can be things like: `Wicker Park, Chicago, IL, USA` (neighborhood), `Chicago, IL, USA` (city), or `Illinois, USA` (state). It's also fine to use `country`, `city`, and `state` fields atomically.

These locations should standardized (can you find it with Google Maps?), and imply the time zone. For this reason, don't use a broad geographic area like `United States` that spans multiple time zones.

### Why can't we have HTML in meeting notes?

Data should be portable across a range of devices, some of which might not display HTML.

### What about business meetings or other monthly meetings?

This API is for weekly recovery meetings. We recommend using another method (separate page, calendar plugin) to display those types of meetings.

## Meeting Types

The codes below are only used for transmitting meeting data. App users will only see the full definitions.

The codes below should be considered 'reserved.' In your implementation, it's ok to alter the description (for example
"Topic Discussion" rather than "Discussion") so long as the intent is the same. For example, "Child Care Available" is a common substitute for "Babysitting Available." It's also ok to add types, they will be ignored by the importer, but be careful not to use any existing or proposed codes.

Also when adding a custom type, it's wise to stay away from any [ISO 369 language codes](https://en.wikipedia.org/wiki/List_of_ISO_639_language_codes), since these could be added in the future.

<!-- Types -->
|Code|English|Español|Français|日本語|Nederlands|Português|Slovenčina|Svenska|
|---|---|---|---|---|---|---|---|---|
|`11`|11th Step Meditation|Meditación del Paso 11|Méditation sur la 11e Étape|ステップ11 黙想|Stap 11 meditatie|Meditação do 11º Passo|Meditácia 11. kroku|11th Stegs Meditation|
|`12x12`|12 Steps & 12 Traditions|12 Pasos y 12 Tradiciones|12 Étapes et 12 Traditions|12のステップと12の伝統|12 Stappen en 12 Tradities|12 Passos e 12 Tradições|12 Krokov & 12 Tradícií|12 Steg & 12 Traditioner|
|`A`|Secular|Secular|Séculier|無宗教|Seculier|Secular|Svetské|Sekulärt|
|`ABSI`|As Bill Sees It|Como lo ve Bill|Réflexions de Bill|ビルはこう思う|Zoals Bill het ziet|Na opinião de Bill|Ako to vidí Bill|Som Bill Ser Det|
|`AF`|Afrikaans|Afrikáans|Afrikaans|アフリカーンス語|Afrikaans|Afrikaans|Afrikánčina|Afrikaans|
|`AL`|Concurrent with Alateen|Concurrente con Alateen|En même temps qu’Alateen|アラティーンと同時進行|Gelijktijdig met Alateen|Em simultâneo com Alateen|Súbežne s Alateen|Tillsammans med Alateen|
|`AL-AN`|Concurrent with Al-Anon|Concurrente con Al-Anon|En même temps qu’Al-Anon|アラノンと同時進行|Gelijktijdig met Al-Anon|Em simultâneo com Al-Anon|Súbežne s Al-Anon|Tillsammans med Al-Anon|
|`AM`|Amharic|Amárico|Amharique|アムハラ語|Amhaars|Amárico|Amharčina|Amhariska|
|`AR`|Arabic|Árabe|Arabe|アラビア語|Arabisch|Árabe|Arabské|Arabiska|
|`ASL`|American Sign Language|Lenguaje por señas|Langage des Signes|アメリカ手話|Amerikaanse gebaren taal|Língua Gestual Americana|Americký posunkový jazyk|Amerikanskt teckenspråk|
|`B`|Big Book|Libro Grande|Gros Livre|ビッグブック|Big Book|Livro Azul|Veľká Kniha|Stora Boken|
|`BA`|Babysitting Available|Guardería disponible|Garderie d’enfants disponible|ベビーシッターあり|Kinderopvang aanwezig|Babysitting disponível|Dostupné opatrovanie detí|Barnvakt Finns|
|`BE`|Newcomer|Principiantes|Nouveau/nouvelle|ビギナーズ|Nieuwkomer|Recém-chegados|Nováčikovia|Nykomling|
|`BG`|Bulgarian|Búlgaro|Bulgare|ブルガリア語|Bulgaars|Búlgaro|Bulharské|Bulgariska|
|`BI`|Bisexual|Bisexual|Bisexuel|バイセクシャル|Biseksueel|Bisexual|Bisexuálne|Bisexuellt|
|`BRK`|Breakfast|Desayuno|Petit déjeuner|朝食|Ontbijt|Pequeno-Almoço|Raňajky|Frukost|
|`C`|Closed|Cerrada|Fermé|クローズド|Gesloten|Fechada|Uzatvorené|Slutet|
|`CAN`|Candlelight|Luz de una vela|À la chandelle|キャンドル|Candlelight|Luz de Velas|Sviečky|Tända Ljus|
|`CF`|Child-Friendly|Niño amigable|Enfants acceptés|お子さま歓迎|Kindvriendelijk|Amigável para Crianças|Priateľský k deťom|Barnvänligt|
|`D`|Discussion|Discusión|Discussion|ディスカッション|Discussie|Discussão|Diskusia|Diskussion|
|`DA`|Danish|Danés|Danois|デンマーク語|Deens|Dinamarquês|Dánsky|Danska|
|`DB`|Digital Basket|Canasta digital|Panier numérique|電子献金|Digitale mand|Cesto Digital|Digitálny košík|Digital Korg|
|`DD`|Dual Diagnosis|Diagnóstico dual|Double diagnostic|重複診断|Dubbele diagnose|Duplo Diagnóstico|Duálna diagnóza|Dubbel Diagnos|
|`DE`|German|Alemán|Allemand|ドイツ語|Duits|Alemão|Nemecké|Tyska|
|`DR`|Daily Reflections|Reflexiones Diarias|Réflexions quotidiennes|今日を新たに|Dagelijkse weerspiegelingen|Reflexões Diárias|Denné úvahy|Dagliga Reflektioner|
|`EL`|Greek|Griego|Grec|ギリシャ語|Grieks|Grego|Grécke|Grekiska|
|`EN`|English|Inglés|Anglais|英語|Engels|Inglês|Anglické|Engelska|
|`FA`|Persian|Persa|Persan|ペルシア語|Perzisch|Persa|Perzské|Persiska|
|`FI`|Finnish|Finlandés|Finlandais|フィンランド語|Fins|Finlandês|Fínčina|Finska|
|`FF`|Fragrance Free|Sin fragancia|Sans parfum|香水なし|Geen parfum|Sem Perfumes|Bez vône|Parfym Fritt|
|`FR`|French|Francés|Français|フランス語|Frans|Francês|Francúzsky|Franska|
|`G`|Gay|Gay|Gai|ゲイ|Homo|Gay|Gay|Gay|
|`GR`|Grapevine|La Viña|Grapevine|グレープバイン|Wijnstok|Grapevine|Grapevine|Grapevine|
|`H`|Birthday|Cumpleaños|Anniversaire|バースデー|Verjaardag|Aniversário|Narodeniny|Födelsedag|
|`HE`|Hebrew|Hebreo|Hébreu|ヘブライ語|Hebreeuws|Hebreu|Hebrejské|Hebreiska|
|`HI`|Hindi|Hindi|Hindi|ヒンディー語|Hindi|Hindi|Hindi|Hindi|
|`HR`|Croatian|Croata|Croate|クロアチア語|Kroatisch|Croata|Chorvátsky|Kroatiska|
|`HU`|Hungarian|Húngaro|Hongrois|ハンガリー語|Hongaars|Hungaro|Maďarské|Ungerska|
|`IS`|Icelandic|Islandés|Islandais|アイスランド語|IJslands|Islandês|Islanské|Isländska|
|`ITA`|Italian|Italiano|Italien|イタリア語|Italiaans|Italiano|Taliansky|Italienska|
|`JA`|Japanese|Japonés|Japonais|日本語|Japans|Japonês|Japonské|Japanska|
|`KA`|Georgian|Georgiano|Géorgien|ジョージア語|Georgisch|Georgiano|Gruzínske|Georgiska|
|`KOR`|Korean|Coreano|Coréen|韓国語|Koreaans|Coreano|Kórejske|Koreanska|
|`L`|Lesbian|Lesbiana|Lesbienne|レズビアン|Lesbisch|Lésbica|Lesbické|Lesbiskt|
|`LGBTQ`|LGBTQ|LGBTQ|LGBTQ|LGBTQ|LGBTQ|LGBTQ|LGBTQ|HBTQ|
|`LIT`|Literature|Literatura|Publications|書籍|Literatuur|Literatura|Literatúra|Litteratur|
|`LS`|Living Sober|Viviendo Sobrio|Vivre… Sans alcool|リビングソーバー|Sober leven|Viver Sóbrio|Triezvy život|Leva Nyktert|
|`LT`|Lithuanian|Lituano|Lituanien|リトアニア語|Litouws|Lituano|Litovské|Litauiska|
|`M`|Men|Hombres|Hommes|男性|Mannen|Homens|Muži|Mansmöte|
|`MED`|Meditation|Meditación|Méditation|黙想|Meditatie|Meditação|Meditácia|Meditationsmöte|
|`ML`|Malayalam|Malayalam|Malayalam|マラヤーラム語|Malayalam|Malaiala|Malajálamsky|Malayalam|
|`MT`|Maltese|Maltés|Maltais|マルタ語|Maltees|Maltês|Maltézske|Maltesiska|
|`N`|Native American|Nativo Americano|Autochtone|ネイティブアメリカン|Indiaan|Nativo Americano|Domorodí Američania|Ur-amerikanskt|
|`NB`|Non-Binary|No binario|Non binaire|ノンバイナリー|Niet-binair|Não binário|Nebinárne|Icke-binär|
|`NDG`|Indigenous|Indígena|Indigène|先住民|Inheems|Indígena|Domorodé|Urfolkligt|
|`NE`|Nepali|Nepalí|Népalais|ネパール語|Nepalees|Nepalês|Nepálsky|Nepali|
|`NL`|Dutch|Holandés|Néerlandais|オランダ語|Nederlands|Holandês|Holandské|Holländska|
|`NO`|Norwegian|Noruego|Norvégien|ノルウェー語|Noors|Norueguês|Nórsky|Norska|
|`O`|Open|Abierta|Ouvert(e)|オープン|Open|Aberta|Otvorené|Öppet|
|`OUT`|Outdoor|Al aire libre|En plein air|アウトドア|Buiten|Ao ar livre|Vonkajšie|Utomhus|
|`P`|Professionals|Profesionales|Professionnels|職業人|Professionals|Profissionais|Profesionáli|Professionella|
|`POA`|Proof of Attendance|Prueba de Asistencia|Preuve de Présence|出席証明|Bewijs van Aanwezigheid|Comprovante de Presença|Doklad o účasti|Närvarobevis|
|`POC`|People of Color|Gente de color|Gens de couleur|有色人種|Mensen van kleur|Pessoas de Côr|Farební ľudia|Färgade|
|`POL`|Polish|Polaco|Polonais|ポーランド語|Pools|Polaco|Poľské|Polska|
|`POR`|Portuguese|Portugués|Portugais|ポルトガル語|Portugees|Português|Portugalské|Portugisiska|
|`PUN`|Punjabi|Punjabi|Pendjabi|パンジャブ語|Punjabi|Punjabi|Pandžábske|Punjabi|
|`RUS`|Russian|Ruso|Russe|ロシア語|Russisch|Russo|Ruské|Ryska|
|`S`|Spanish|Español|Espagnol|スペイン語|Spaans|Espanhol|Španielské|Spanska|
|`SEN`|Seniors|Personas mayores|Séniors|シニア|Senioren|Séniores|Seniori|Seniorer|
|`SK`|Slovak|Eslovaco|Slovaque|スロバキア語|Slowaaks|Eslovaco|Slovenské|Slovakiska|
|`SL`|Slovenian|Esloveno|Slovène|スロベニア語|Sloveens|Esloveno|Slovinské|Slovenska|
|`SM`|Smoking Permitted|Se permite fumar|Permis de fumer|喫煙可|Roken toegestaan|Permitido Fumar|Fajčenie povolené|Rökning Tillåten|
|`SP`|Speaker|Orador|Conférencier|スピーカー|Spreker|Partilhador|Spíker|Talare|
|`ST`|Step Study|Estudio de pasos|Sur les Étapes|ステップ|Stap studie|Estudo de Passos|Štúdium Krokov|Stegmöte|
|`SV`|Swedish|Sueco|Suédois|スウェーデン語|Zweeds|Sueco|Švédske|Svenska|
|`T`|Transgender|Transgénero|Transgenre|トランスジェンダー|Transgender|Transgénero|Transgender|Transpersoner|
|`TC`|Location Temporarily Closed|Ubicación temporalmente cerrada|Emplacement temporairement fermé|一時的休止中|Locatie tijdelijk gesloten|Local Temporáriamente Encerrado|Miesto dočasne zatvorené|Tillfälligt Stängt|
|`TH`|Thai|Tailandés|Thaï|タイ語|Thais|Tailandês|Thajské|Thailändska|
|`TL`|Tagalog|Tagalo|Tagalog|タガログ語|Tagalog|Tagalo|Tagalské|Tagalog|
|`TR`|Tradition Study|Estudio de tradicion|Étude des Traditions|伝統|Traditie Studie|Estudo de Tradições|Tradičné štúdium|Traditionsmöte|
|`TUR`|Turkish|Turco|Turc|トルコ語|Turks|Turco|Turecký|Turkiska|
|`UK`|Ukrainian|Ucraniano|Ukrainien|ウクライナ語|Oekraïens|Ucraniano|Ukrajinské|Ukrainska|
|`W`|Women|Mujer|Femmes|女性|Vrouwen|Mulheres|Ženy|Kvinnomöte|
|`X`|Wheelchair Access|Acceso en silla de ruedas|Accès aux fauteuils roulants|車いすアクセス|Toegankelijk voor rolstoelgebruikers|Acesso a Cadeiras de Rodas|Prístup pre vozíčkarov|Handikappanpassat|
|`XB`|Wheelchair-Accessible Bathroom|Baño accesible para sillas de ruedas|Toilettes accessibles aux fauteuils roulants|車いす使用者用トイレ|Rolstoeltoegankelijke badkamer|WC com Acesso a Cadeiras de Rodas|Bezbariérová kúpeľňa|Handikappanpassad WC|
|`XT`|Cross Talk Permitted|Se permite opinar|Conversation croisée permise|クロストーク可能|Cross-sharen toegestaan|Prtilhas Cruzadas Permitidas|Cross Talk povolený|Kommentarer Tilltåtna|
|`Y`|Young People|Gente joven|Jeunes|ヤング|Jongeren|Jovens|Mladí ľudia|Young People|
<!-- End Types -->

## Proposed New Types

The following types are proposed for future use. They are not currently in use in the app.

<!-- Proposed Types -->
|Code|English|Español|Français|日本語|Nederlands|Português|Slovenčina|Svenska|
|---|---|---|---|---|---|---|---|---|
|`BV-I`|Blind / Visually Impaired|Ciego / Discapacidad Visual|Aveugle / Malvoyant|視覚障害者|Blind / Visueel gehandicapt|Cego / Deficiência Visual|Nevidiaci / Zrakovo postihnutí|Blind / Synskadad|
|`D-HOH`|Deaf / Hard of Hearing|Sordo / Duro de Oído|Sourd / Malentendant|聴覚障害者|Doof / Hardhoren|Surdo / Duro de Ouvido|Nepočujúci / Nedoslýchaví|Döv / Hörselskadad|
|`LO-I`|Loners / Isolationists|Solitarios / Aislacionistas|Solitaires / Isolationnistes|孤独 / 孤立主義者|Eenlingen / Isolationisten|Solitários / Isolacionistas|Samotári / Izolacionisti|Ensamvargar / Isolationister|
|`QSL`|Quebec Sign Language|Lengua de Señas de Quebec|Langue des Signes Québécoise|ケベック手話|Quebec -gebarentaal|Língua Gesual Quebec|Quebecký posunkový jazyk|Quebecskt Teckenspråk|
|`RSL`|Russian Sign Language|Lengua de Señas Rusa|Langue des Signes Russe|ロシア手話|Russische gebarentaal|Língua Gestual Russa|Ruský posunkový jazyk|Ryskt Teckenspråk|
<!-- End Proposed Types -->

## Proposed Changed Types

The following types being considered for a name change.

<!-- Proposed Changed Types -->
|Code|English|Español|Français|日本語|Nederlands|Português|Slovenčina|Svenska|
|---|---|---|---|---|---|---|---|---|
|`LGBTQ`|LGBTQIAA+|LGBTQIAA+|LGBTQIAA+|LGBTQIAA+|LGBTQIAA+|LGBTQIAA+|LGBTQIAA+|HBTQIAA+|
<!-- End Proposed Changed Types -->

## Sharing Your Data

If you choose, you may make your feed discoverable by linking to it (like RSS) in your site's `<HEAD>`.

```HTML
<link rel="alternate" type="application/json" title="Meetings Feed" href="https://example.com/etc/meetings-feed">
```

The script may have any name, and be in any directory, but it should be a fully qualified URL, and the `title="Meetings Feed"` attribute is required.

## Use the Spec in your code

### PHP

---

### Code4Recovery Spec Composer Package

This package contains a class that makes the most up-to-date meeting types available to your application.

### Installation

```shell
composer require code4recovery/spec
```

### Get all available languages

Returns an array of available languages.

```php
$spec::getLanguages();

// this returns:
[
	'en' => 'English',
    'es' => 'Español',
    'fr' => 'Français',
    'ja' => '日本語',
    'sv' => 'Svenska',
    'sk' => 'Slovenčina',
];
```

### Get all types

Returns an object containing types in every language.

```php
$spec::getAllTypes();

// this returns (truncated):
{
  "11": {
    "en": "11th Step Meditation",
    "es": "Meditación del Paso 11",
    "fr": "Méditation sur la 11e Étape",
    "ja": "ステップ11 黙想",
    "sv": "11th Stegs Meditation",
    "sk": "Meditácia 11. kroku"
  },
   "12x12": {
    "en": "12 Steps & 12 Traditions",
    "es": "12 Pasos y 12 Tradiciones",
    "fr": "12 Étapes et 12 Traditions",
    "ja": "12のステップと12の伝統",
    "sv": "12 Steg & 12 Traditioner",
    "sk": "12 Krokov & 12 Tradícií"
  },
  ...
};
```

### Get types by language

Returns an array of types translated into a specified language. Pass the desired language key as a string ('en', 'es', 'fr', etc.)

```php
$spec::getTypesByLanguage('en');

// returns (truncated):
[
    "11" => "11th Step Meditation"
    "12x12" => "12 Steps & 12 Traditions"
    "A" => "Secular"
    "ABSI" => "As Bill Sees It"
    ...
];
```

### TypeScript / JavaScript

---

### Installation

```shell
npm i @code4recovery/spec
```

### Usage

```js
import { getTypesForLanguage } from '@code4recovery/spec';

const types = getTypesForLanguage('en');

// returns:
{
  "11": "11th Step Meditation",
  "12x12": "12 Steps & 12 Traditions",
  A: "Secular",
  ABSI: "As Bill Sees It",
  ...
}
```

## License

Code4Recovery Spec is made available under the MIT License (MIT). Please see [License File](LICENSE) for more information.
