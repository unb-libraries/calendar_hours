# calendar_hours_client
The module connects to the REST interface provided by the calendar_hours_server module to integrate queried hours information into the front end of any Drupal application.

## Requirements

This module requires that a calendar_hours endpoint is active on either the same or another domain.

## Usage

### Define "Hours" Container

Any HTML can act as an hours container, whose contents will be replaced by an hours string or list. In order to define an element as an hours container, it 
needs to have a ```data-ch-id``` attribute present. For example, to display the hours for the unit that's represented by the calendar with ID "hil", put

```html
<div data-ch-id="hil">Hours of "hil" will be inserted here</div>
```

### Define the date

If not specified further, a container will be filled with today's hours. However, a container can be filled with hours from a different date by specifying a dynamic date...

```html
<div data-ch-id="hil" data-ch-days="0">Today's hours of "hil" will be inserted here. This is the default and is equivalent to not specifying 
'data-ch-days'</div>
<div data-ch-id="hil" data-ch-days="1">Tomorrow's hours of "hil" will be inserted here.</div>
```
or fixed date

```html
<div data-ch-id="hil" data-ch-date="2019-07-01">Hours of "hil" on Canada Day 2019 will be inserted here.</div>
```

### Render hours

Generally, an hours container will be filled with a list of hours "blocks", such as

```html
<div data-ch-id="hil"><span>2019-07-01</span><ul><li>08:00 AM - 05:00 PM</li></ul></div>
```

Howeverm there are several ways to influence how hours will be rendered, i.e. whether they will appear as a full date and time string, only include the time as 
well as how both dates and times will display. 

#### Specifiy Date and Time format

The following snipped will render only the day of the week and display the time in a 24h format

```html
<div data-ch-id="hil" data-ch-date-format="dddd" data-ch-time-format="HH:mm"><span>Monday</span><ul><li>08:00 - 17:00</li></ul></div>
```

Adding the "ch-nd" will not render the date at all. Any specified date format will be ignored. 

```html
<div class="ch-nd" data-ch-id="hil"><ul><li>08:00 AM - 05:00 PM</li></ul></div>
```

#### Static vs 'Live'

By default hours will be rendered independently of whether a unit is currently open or closed. This can be changed.

For example, by adding the ```ch-live``` class, the output will change to

```html
<div class="ch-nd ch-live" data-ch-id="hil"><ul><li>Open until 05:00 PM</li></ul></div>
```
or
```html
<div class="ch-nd ch-live" data-ch-id="hil"><ul><li>Closed until 08:00 AM</li></ul></div>
```

The ```ch-live-c``` and ```ch-live-co``` classes only render a "live" status when currently open and a regular output otherwise. ```ch-live-c``` renders as shown above, whereas ```ch-live-co``` adds a reopening time:

```html
<div class="ch-nd ch-live-co" data-ch-id="hil"><ul><li>Open until 05:00 PM. Reopens 08:00 AM</li></ul></div>
```

To add a date to closing/reopening times, use the ```ch-live-d``` class. As shown above, how the date will be rendered can be defined by the 
```data-ch-date-format``` property:

```html
<div class="ch-nd ch-live-co ch-live-d" data-ch-id="hil" data-ch-date-format="dddd"><ul><li>Open until Monday, 05:00 PM. Reopens Tuesday, 08:00 AM</li></ul></div>
```

To add a date only for the closing or the reopening time, use the ```ch-live-do``` or ```ch-live-dc``` classes:

```html
<div class="ch-nd ch-live-co ch-live-dc" data-ch-id="hil" data-ch-date-format="dddd"><ul><li>Open until 05:00 PM. Reopens Tuesday, 08:00 AM</li></ul></div>
```

To add a date only if the closing or reopening times are on a different date than the current day, use the ```ch-live-nsd``` (both closing/reopening time), 
```ch-live-nsdc``` (only closing time), or ```ch-live-nsdo``` (only reopening time).

#### Midnight Live

To display live hours only when a unit is open past midnight and until it closes, but regular hours otherwise, add the ```ch-live-am``` class in addition to any other classes.

If someone then visits the website at 01:00 AM they will see

```html
<div class="ch-nd ch-live-co ch-live-nsdo ch-live-am" data-ch-id="hil" data-ch-date-format="dddd"><ul><li>Open until 03:00 AM. Reopens Tuesday, 08:00 AM</li></ul></div>
```

while at 04:00 PM they will see

```html
<div class="ch-nd ch-live-co ch-live-nsdo ch-live-am" data-ch-id="hil" data-ch-date-format="dddd"><ul><li>08:00 AM - 05:00 PM</li></ul></div>
```

