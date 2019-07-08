# calendar_hours
The Calendar Hours module lets businesses and institutions administer their hours via an externally hosted calendar service, such as Google Calendars.

## Module overview

| Module | Description |
| ------ | ----------- |
| calendar_hours_server | This module provides a REST interface through which clients can query hours. The module provides the means to configure which externally hosted calendars to sync with. |
| [calendar_hours_client](calendar_hours_client/README.md) | The module connects to the REST interface provided by the calendar_hours_server module to integrate queried hours information into the front end of any Drupal application. |
| calendar_hours_google | This module provides allows to work with Google Calendar API. |

