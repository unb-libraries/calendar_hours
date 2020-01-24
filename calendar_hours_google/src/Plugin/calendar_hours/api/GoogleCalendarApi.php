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
   * Access Google Calendar API.
   *
   * @return mixed
   *   The API instance.
   */
  protected function api() {
    return $this->googleCalendarApi;
  }

  /**
   * Create an event query object.
   *
   * @param $calendar_id
   *   The ID of the calendar to query.
   *
   * @return \Drupal\calendar_hours_google\Plugin\calendar_hours\api\EventQuery
   *   An event query instance.
   */
  protected function getEventQuery($calendar_id) {
    return new EventQuery($this->api(), $calendar_id);
  }

  /**
   * {@inheritdoc}
   */
  public function getHours(HoursCalendar $calendar, $from_date = 'now', $to_date = 'now') {
    $timezone = $this->getTimezone($calendar);
    $from = new DrupalDateTime($from_date, $timezone);
    $to = new DrupalDateTime($to_date, $timezone);

    $query = $this->getEventQuery($calendar->foreign_id)
      ->setStartDate($from)
      ->setEndDate($to);

    foreach ($query->execute() as $event) {
      $blocks[] = new Block($event->id, $calendar->id,
        new DrupalDateTime($event->start->dateTime, $timezone),
        new DrupalDateTime($event->end->dateTime, $timezone)
      );
    }
    return isset($blocks) ? $blocks : [];
  }

  /**
   * {@inheritDoc}
   */
  public function setHours(HoursCalendar $calendar, $event_id, $from, $to) {
    $start = new \Google_Service_Calendar_EventDateTime();
    $start->setDateTime($from);
    $end = new \Google_Service_Calendar_EventDateTime();
    $end->setDateTime($to);

    $event = new \Google_Service_Calendar_Event();
    $event->setStart($start);
    $event->setEnd($end);

    $events = $this->api()->events;

    if ($event = $events->patch($calendar->foreign_id, $event_id, $event)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritDoc}
   */
  public function createHours(HoursCalendar $calendar, Block $block) {
    $start = new \Google_Service_Calendar_EventDateTime();
    $start->setDateTime($block->getStart()->format('c'));
    $end = new \Google_Service_Calendar_EventDateTime();
    $end->setDateTime($block->getEnd()->format('c'));

    $event = new \Google_Service_Calendar_Event();
    $event->setStart($start);
    $event->setEnd($end);

    if ($this->api()->events->insert($calendar->foreign_id, $event)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritDoc}
   */
  public function close(HoursCalendar $calendar, DrupalDateTime $date) {
    $events = $this->getEventQuery($calendar->foreign_id)
      ->setStartDate($date)
      ->setEndDate($date)
      ->execute();

    $eventList = $this->api()->events;

    $success = TRUE;
    foreach ($events as $event) {
      /** @var \Google_Service_Calendar_Event $event */
      if (!$eventList->delete($calendar->foreign_id, $event->id)) {
        $success = FALSE;
      }
    }
    return $success;
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
    $timezone = $this->getTimezone($calendar);
    $from = new DrupalDateTime('now', $timezone);

    $query = $this->getEventQuery($calendar->foreign_id)
      ->setStartTime($from)
      ->setMaxResults(2);

    foreach ($query->execute() as $event) {
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
    $timezone = $this->getTimezone($calendar);
    $from = new DrupalDateTime('now', $timezone);

    $query = $this->getEventQuery($calendar->foreign_id)
      ->setStartTime($from)
      ->setMaxResults(2);

    foreach ($query->execute() as $event) {
      $start = new DrupalDateTime($event->start->dateTime, $timezone);
      $end = new DrupalDateTime($event->end->dateTime, $timezone);
      if ($start < $from && $end > $from) {
        return $end;
      }
    }

    return NULL;
  }

  /**
   * {@inheritDoc}
   */
  public function getTimezone(HoursCalendar $calendar) {
    $timezone_name = $this->googleCalendarApi
      ->calendars
      ->get($calendar->foreign_id)
      ->timeZone;
    return new \DateTimeZone($timezone_name);
  }

}