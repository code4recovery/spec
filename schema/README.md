# Meeting Guide API Schemas

## Overview

This directory contains JSON schemas used for validating meeting data structures in the Meeting Guide API. These schemas are designed to ensure data consistency and integrity for applications that manage meeting information. These schemas are based on the JSON Schema Draft-07 specification.

### Schemas Included

1. **meeting.schema.json**
   - **Description**: Defines the structure for individual meeting entries, including details such as name, location, time, and contact information. Only `name` and `slug` are required but additional properties should be used when possible.
   - **Key Properties**:

| Property               | Description                                                                                   | Type          |
|------------------------|-----------------------------------------------------------------------------------------------|---------------|
| `name`                 | The name of the meeting, max 64 characters.                                                   | string        |
| `slug`                 | A unique identifier for the meeting, used as the primary key, max 255 characters.             | string        |
| `day`                  | The day(s) of the week the meeting occurs (integer or array of integers 0-6).                 | integer/array of integers |
| `time`                 | The start time of the meeting in HH:MM 24-hour format.                                        | string        |
| `end_time`             | The optional end time of the meeting in HH:MM 24-hour format.                                 | string        |
| `timezone`             | The optional timezone of the meeting in tz database format.                                   | string        |
| `formatted_address`    | The complete address of the meeting location.                                                 | string        |
| `address`              | The street address of the meeting location.                                                   | string        |
| `city`                 | The city where the meeting is held.                                                           | string        |
| `state`                | The state where the meeting is held, 2 uppercase letters.                                     | string        |
| `postal_code`          | The postal code of the meeting location, 5 digits.                                            | string        |
| `country`              | The country where the meeting is held.                                                        | string        |
| `region`               | An optional geographical subset of meeting locations.                                         | string        |
| `sub_region`           | An optional further specification of the region.                                              | string        |
| `regions`              | A hierarchical array of regions, from general to specific.                                    | array of strings |
| `conference_url`       | The URL for an online meeting, URI format.                                                    | string        |
| `conference_url_notes` | Optional metadata about the conference URL.                                                   | string        |
| `conference_phone`     | The phone number for dialing into an online meeting.                                          | string        |
| `conference_phone_notes`| Optional metadata about the conference phone number.                                         | string        |
| `types`                | An optional array of standardized meeting types.                                              | array of strings |
| `notes`                | Optional additional details about the meeting.                                                | string        |
| `location`             | The name of the building or landmark where the meeting is held.                               | string        |
| `location_notes`       | Optional notes applicable to all meetings at the location.                                    | string        |
| `latitude`             | The optional latitude of the meeting location.                                                | number        |
| `longitude`            | The optional longitude of the meeting location.                                               | number        |
| `approximate`          | Indicates if the address is approximate ("yes" or "no").                                      | string        |
| `updated`              | The optional timestamp indicating when the listing was last updated, in YYYY-MM-DD HH:MM:SS format. | string        |
| `group`                | The optional name of the group providing the meeting.                                         | string        |
| `group_notes`          | Optional group-related notes.                                                                 | string        |
| `venmo`                | The optional Venmo handle for contributions.                                                  | string        |
| `square`               | The optional Square Cash App cashtag for contributions.                                       | string        |
| `paypal`               | The optional PayPal.me username for contributions.                                            | string        |
| `url`                  | The optional URL pointing to the meeting's listing on the area website, URI format.           | string        |
| `edit_url`             | The optional URL that trusted servants can use to edit the meeting's listing, URI format.     | string        |
| `feedback_emails`      | An optional array of feedback email addresses for the service entity.                         | array of strings |
| `feedback_url`         | The optional URL for providing feedback about the meeting, URI format.                        | string        |
| `entity`               | The name of the service entity responsible for the listing.                                   | string        |
| `entity_email`         | The public email address for the service entity, email format.                                | string        |
| `entity_location`      | A description of the service area of the entity.                                              | string        |
| `entity_logo`          | The URL of the logo of the service entity, URI format.                                        | string        |
| `entity_phone`         | The phone number of the service entity.                                                       | string        |
| `entity_website_url`   | The website homepage of the service entity, URI format.                                       | string        |
| `contact_1_name`       | The name of the first contact person for the meeting.                                         | string        |
| `contact_1_email`      | The email address of the first contact person, email format.                                  | string        |
| `contact_1_phone`      | The phone number of the first contact person.                                                 | string        |
| `contact_2_name`       | The name of the second contact person for the meeting.                                        | string        |
| `contact_2_email`      | The email address of the second contact person, email format.                                 | string        |
| `contact_2_phone`      | The phone number of the second contact person.                                                | string        |
| `contact_3_name`       | The name of the third contact person for the meeting.                                         | string        |
| `contact_3_email`      | The email address of the third contact person, email format.                                  | string        |
| `contact_3_phone`      | The phone number of the third contact person.                                                 | string        |

2. **meeting-type.schema.json**
   - **Description**: Lists available, proposed, and proposed changed meeting types as enumerations.

3. **feed.schema.json**
   - **Description**: Defines the structure of a feed containing multiple meeting entries.

## Usage

These schemas can be used to validate JSON data structures in applications that require structured meeting information. They help ensure that data conforms to expected formats and constraints, reducing errors and improving data quality.

### PHP Example

Here is an example of how you might use a PHP library to validate data against the `meeting.schema.json`:

```php
<?php
require 'vendor/autoload.php';

use JsonSchema\Validator;
use JsonSchema\Constraints\Constraint;

$validator = new Validator();
$data = json_decode(file_get_contents('example.json'));
$schema = json_decode(file_get_contents('spec/meeting.schema.json'));

$validator->validate($data, $schema, Constraint::CHECK_MODE_APPLY_DEFAULTS);

if ($validator->isValid()) {
    echo "The supplied JSON validates against the schema.\n";
} else {
    echo "JSON does not validate. Violations:\n";
    foreach ($validator->getErrors() as $error) {
        echo sprintf("[%s] %s\n", $error['property'], $error['message']);
    }
}
```

### JavaScript Example

Here is an example of how you might use the ajv library in JavaScript to validate data against the `meeting.schema.json`:

```javascript
const Ajv = require('ajv');
const ajv = new Ajv();
const meetingSchema = require('./spec/meeting.schema.json');
const data = {
  "name": "Weekly Meditation Group",
  "slug": "weekly-meditation-group",
  "day": 1,
  "time": "19:30",
  "formatted_address": "123 Main St, Springfield, IL 62701, USA"
};

const validate = ajv.compile(meetingSchema);
const valid = validate(data);
if (!valid) console.log(validate.errors);
```

## Contribution

Contributions to improve and expand the schemas are welcome. Please ensure any changes are compatible with JSON Schema Draft-07 and include appropriate documentation.
