<?php

namespace Drupal\calendar_hours_server\Plugin;

use Drupal\calendar_hours_server\Entity\HoursCalendar;
use Drupal\Core\Datetime\DrupalDateTime;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Interface CalendarApiInterface.
 *
 * @package Drupal\calendar_hours_server\Plugin
 */
interface CalendarApiInterface {


  /**
   * Inject a service this API may depend on.
   *
   * @param $alias
   *   An alias to store the service reference under.
   * @param $service_id
   *   The ID of the service to be injected.
   * @throws ServiceNotFoundException
   */
  public function injectService($alias, $service_id);

  /**
   * Retrieve opening hours for the unit represented by the given calendar.
   *
   * @param \Drupal\calendar_hours_server\Entity\HoursCalendar $calendar
   *   The calendar containing the hours information.
   * @param string $from_date
   *   Earliest Date to be included in the hours response.
   * @param string $to_date
   *   Latest Date to be included in the hours response.
   *
   * @return \Drupal\calendar_hours_server\Response\Block[]
   *   List of Blocks representing hours for the requested time period.
   */
  public function getHours(HoursCalendar $calendar, $from_date, $to_date);

  /**
   * Assign hours for the unit represented by the given calendar and the given event.
   *
   * @param \Drupal\calendar_hours_server\Entity\HoursCalendar $calendar
   *   The calendar containing the hours information.
   * @param string $event_id
   *   ID of the event to update.
   * @param string $from
   *   Earliest Date to be included in the hours response.
   * @param string $to
   *   Latest Date to be included in the hours response.
   *
   * @return bool
   *   TRUE if hours could be successfully set. FALSE otherwise.
   */
  public function setHours(HoursCalendar $calendar, $event_id, $from, $to);

  /**
   * Remove all events for the given calendar and on the given date.
   *
   * @param \Drupal\calendar_hours_server\Entity\HoursCalendar $calendar
   *   The calendar.
   * @param \Drupal\Core\Datetime\DrupalDateTime $date
   *   The date.
   *
   * @return bool
   *   TRUE if all events could successfully be removed. FALSE otherwise.
   */
  public function close(HoursCalendar $calendar, DrupalDateTime $date);

  /**
   * List IDs of all Calendars provided by the vendor.
   *
   * @return array
   */
  public function getForeignIds();

  /**
   * Retrieve the title for a given calendar.
   *
   * @param string $foreign_id
   *   The foreign_id to find the title for.
   *
   * @return string
   *   Title of the calendar.
   */
  public function getCalendarTitle($foreign_id);

  /**
   * Retrieve the next time the unit represented by the given calendar opens or, if already open, reopens.
   *
   * @param \Drupal\calendar_hours_server\Entity\HoursCalendar $calendar
   *   The calendar to query.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime
   *     Time the unit opens (if currently closed) or reopens (if currently open). NULL if it never reopens again.
   */
  public function getOpensAt(HoursCalendar $calendar);

  /**
   * Retrieve the next time the unit represented by the given calendar closes.
   *
   * @param \Drupal\calendar_hours_server\Entity\HoursCalendar $calendar
   *   The calendar to query.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime
   *     Time the unit closes next. NULL if already closed.
   */
  public function getClosesAt(HoursCalendar $calendar);

  /**
   * Retrieve the timezone setting of the given calendar.
   *
   * @param \Drupal\calendar_hours_server\Entity\HoursCalendar $calendar
   *   The calendar to query.
   *
   * @return \DateTimeZone
   *   A timezone object.
   */
  public function getTimezone(HoursCalendar $calendar);

}