<?php

namespace Drupal\calendar_hours_google\Plugin\calendar_hours\api;

use Drupal\calendar_hours_server\Entity\HoursCalendar;
use Drupal\calendar_hours_server\Plugin\CalendarApiBase;
use Drupal\calendar_hours_server\Response\Block;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Class GoogleCalendarApi.
 *
 * @package Drupal\calendar_hours_google\Plugin\calendar_hours\api
 *
 * @CalendarApi(
 *   id = "google_calendar_api",
 *   label = @Translation("Google Calendar API"),
 *   vendor = "google",
 *   services = {
 *     "google_calendar_api" = "gapi.calendar",
 *   }
 * )
 */
class GoogleCalendarApi extends CalendarApiBase {

  /**
   * {@inheritdoc}
   */
  public function getHours(HoursCalendar $calendar, $from_date = 'now', $to_date = 'now') {
    $timezone = new \DateTimeZone($this->googleCalendarApi->calendars->get($calendar->foreign_id)->timeZone);
    $from = new DrupalDateTime($from_date, $timezone);
    $to = new DrupalDateTime($to_date, $timezone);

    $events = $this->googleCalendarApi->events->listEvents($calendar->foreign_id, [
      'singleEvents' => TRUE,
      'timeMin' => $from->setTime(0, 0, 0)->format('c'),
      'timeMax' => $to->setTime(23, 59, 59)->format('c'),
      'fields' => 'items(end/dateTime,start/dateTime,summary)',
    ]);

    foreach ($events->getItems() as $event) {
      $blocks[] = new Block($calendar->id,
        new DrupalDateTime($event->start->dateTime, $timezone),
        new DrupalDateTime($event->end->dateTime, $timezone)
      );
    }
    return isset($blocks) ? $blocks : [];
  }

  /**
   * {@inheritdoc}
   */
  public function getForeignIds() {
    $ids = [];
    $calendar_items = $this->googleCalendarApi->calendarList->listCalendarList();
    foreach ($calendar_items->getItems() as $calendar_item) {
      $ids[$calendar_item->id] = $calendar_item->getSummary();
    }
    return $ids;
  }

  /**
   * {@inheritdoc}
   */
  public function getCalendarTitle($foreign_id) {
    return $this->googleCalendarApi->calendars->get($foreign_id)->getSummary();
  }

  /**
   * {@inheritDoc}
   */
  public function getOpensAt(HoursCalendar $calendar) {
    $timezone = new \DateTimeZone($this->googleCalendarApi->calendars->get($calendar->foreign_id)->timeZone);
    $from = new DrupalDateTime('now', $timezone);

    $eventList = $this->googleCalendarApi->events->listEvents($calendar->foreign_id, [
      'singleEvents' => TRUE,
      'timeMin' => $from->format('c'),
      'maxResults' => 2,
      'orderBy' => 'startTime',
      'fields' => 'items(end/dateTime,start/dateTime,summary)',
    ]);

    $events = $eventList->getItems();
    foreach ($events as $event) {
      $start = new DrupalDateTime($event->start->dateTime, $timezone);
      if ($start > $from) {
        return $start;
      }
    }

    return NULL;
  }

  /**
   * {@inheritDoc}
   */
  public function getClosesAt(HoursCalendar $calendar) {
    $timezone = new \DateTimeZone($this->googleCalendarApi->calendars->get($calendar->foreign_id)->timeZone);
    $from = new DrupalDateTime('now', $timezone);

    $eventList = $this->googleCalendarApi->events->listEvents($calendar->foreign_id, [
      'singleEvents' => TRUE,
      'timeMin' => $from->format('c'),
      'maxResults' => 2,
      'orderBy' => 'startTime',
      'fields' => 'items(end/dateTime,start/dateTime,summary)',
    ]);

    $events = $eventList->getItems();
    foreach ($events as $event) {
      $start = new DrupalDateTime($event->start->dateTime, $timezone);
      $end = new DrupalDateTime($event->end->dateTime, $timezone);
      if ($start < $from && $end > $from) {
        return $end;
      }
    }

    return NULL;
  }

}