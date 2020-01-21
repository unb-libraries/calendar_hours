<?php

namespace Drupal\calendar_hours_google\Plugin\calendar_hours\api;

use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Class to query Google Calendar events.
 *
 * @package Drupal\calendar_hours_google\Plugin\calendar_hours\api
 */
class EventQuery {

  const DATE_FORMAT = 'Y-m-d';
  const DATE_TIME_FORMAT = 'c';

  const FIELD_ID = 'id';
  const FIELD_SERIES_ID = 'iCalUID';
  const FIELD_START_TIME = 'start/dateTime';
  const FIELD_END_TIME = 'end/dateTime';
  const FIELD_SUMMARY = 'summary';

  const PARAM_FIELDS = 'fields';
  const PARAM_START_TIME = 'timeMin';
  const PARAM_END_TIME = 'timeMax';
  const PARAM_TIMEZONE = 'timeZone';
  const PARAM_RETURN_SINGLE_EVENTS = 'singleEvents';
  const PARAM_MAX_RESULTS = 'maxResults';
  const PARAM_ORDER_BY = 'orderBy';

  const ORDER_BY_EVENT_START_TIME = 'startTime';
  const ORDER_BY_EVENT_MODIFICATION_TIME = 'updated';

  // The highest value which the API accepts.
  protected const MAX_RESULTS_MAX = 2500;

  /**
   * Google API instance.
   *
   * @var \Google_Service_Calendar
   */
  protected $api;

  /**
   * The ID of the calendar to query.
   * @var string
   */
  protected $calendarId;

  /**
   * Array of fields to include in the response.
   *
   * @var array
   */
  protected $fields;

  /**
   * Array of parameters to influence query results.
   *
   * @var array
   */
  protected $params;

  /**
   * Creates an EventQuery instance.
   *
   * @param \Google_Service_Calendar $api
   *   The Google API.
   * @param $calendar_id
   *   The ID of the calendar to query.
   */
  public function __construct(\Google_Service_Calendar $api, $calendar_id) {
    $this->api = $api;
    $this->calendarId = $calendar_id;
  }

  /**
   * Access the Google Calendar API.
   *
   * @return \Google_Service_Calendar
   *   A Google Calendar API instance.
   */
  protected function api() {
    return $this->api;
  }

  /**
   * Retrieve the ID of the calendar to query.
   *
   * @return string
   *   A string.
   */
  public function getCalendarId() {
    return $this->calendarId;
  }

  /**
   * Retrieve an array of fields which should be included in the response.
   *
   * @return array
   *   Array of field identifiers.
   */
  public function getFields() {
    if (!isset($this->fields)) {
      $this->fields = [
        self::FIELD_ID,
        self::FIELD_SERIES_ID,
        self::FIELD_START_TIME,
        self::FIELD_END_TIME,
        self::FIELD_SUMMARY,
      ];
    }
    return $this->fields;
  }

  /**
   * Set the given field to be included in the response.
   *
   * @param string $field
   *   The field identifier.
   */
  public function addField($field) {
    if (!in_array($field, $this->getFields())) {
      $this->fields[] = $field;
    }
  }

  /**
   * Retrieve the query parameters.
   *
   * @return array
   *   Array of query parameters and their assigned values.
   */
  public function getParams() {
    if (!isset($this->params)) {
      $this->params = [];
    }
    $params = array_merge(
      $this->defaultParams(),
      $this->params,
      [
        self::PARAM_FIELDS => sprintf('items(%s)',
          implode(',', $this->getFields())
        ),
      ]);

    return $params;
  }

  /**
   * Retrieve default query parameters.
   *
   * @return array
   *   Array of default query parameters and their assigned values.
   */
  protected function defaultParams() {
    $now = new DrupalDateTime();
    $last_midnight = $now->setTime(0, 0, 0)
      ->format(self::DATE_TIME_FORMAT);

    return [
      self::PARAM_START_TIME => $last_midnight,
      self::PARAM_RETURN_SINGLE_EVENTS => TRUE,
      self::PARAM_ORDER_BY => self::ORDER_BY_EVENT_START_TIME,
    ];
  }

  /**
   * Set a parameter to filter query results.
   *
   * @param string $key
   *   The parameter key.
   * @param string $value
   *   The parameter value.
   *
   * @return \Drupal\calendar_hours_google\Plugin\calendar_hours\api\EventQuery
   *   The called instance.
   */
  protected function setParam($key, $value) {
    $this->params[$key] = $value;
    return $this;
  }

  /**
   * Filter results by events ending on or past the given date.
   *
   * @param string|\Drupal\Core\Datetime\DrupalDateTime $date
   *   The date. If given as string it must match DATE_FORMAT.
   *
   * @return \Drupal\calendar_hours_google\Plugin\calendar_hours\api\EventQuery
   *   The called instance.
   *
   */
  public function setStartDate($date) {
    if (!($date instanceof DrupalDateTime)) {
      $date = self::createDateTimeFromString($date);
    }
    return $this->setParam(
      self::PARAM_START_TIME, $date
        ->setTime(0, 0, 0)
        ->format(self::DATE_TIME_FORMAT));
  }

  /**
   * Filter results by events ending on or past the given time.
   *
   * @param string|\Drupal\Core\Datetime\DrupalDateTime $time
   *   The time. If given as string it must match DATE_TIME_FORMAT.
   *
   * @return \Drupal\calendar_hours_google\Plugin\calendar_hours\api\EventQuery
   *   The called instance.
   *
   */
  public function setStartTime($time) {
    if (!($time instanceof DrupalDateTime)) {
      $time = self::createDateTimeFromString($time);
    }
    return $this->setParam(
      self::PARAM_START_TIME, $time
      ->format(self::DATE_TIME_FORMAT));
  }

  /**
   * Filter results by events starting on or before the given date.
   *
   * @param string|\Drupal\Core\Datetime\DrupalDateTime $date
   *   The date. If given as string it must match DATE_FORMAT.
   *
   * @return \Drupal\calendar_hours_google\Plugin\calendar_hours\api\EventQuery
   *   The called instance.
   */
  public function setEndDate($date) {
    if (!($date instanceof DrupalDateTime)) {
      $date = self::createDateTimeFromString($date);
    }
    return $this->setParam(
      self::PARAM_END_TIME, $date
        ->setTime(23, 59, 59)
        ->format(self::DATE_TIME_FORMAT));
  }

  /**
   * Filter results by events starting on or before the given time.
   *
   * @param string|\Drupal\Core\Datetime\DrupalDateTime $time
   *   The time. If given as string it must match DATE_TIME_FORMAT.
   *
   * @return \Drupal\calendar_hours_google\Plugin\calendar_hours\api\EventQuery
   *   The called instance.
   */
  public function setEndTime($time) {
    if (!($time instanceof DrupalDateTime)) {
      $time = self::createDateTimeFromString($time);
    }
    return $this->setParam(
      self::PARAM_END_TIME, $time
      ->format(self::DATE_TIME_FORMAT));
  }

  /**
   * Attempts to create a datetime object from the given datetime string.
   *
   * If an error occurs, the current time will be returned.
   *
   * @param string $date
   *   Datetime string formatted according to either DATE_FORMAT
   *   or DATE_TIME_FORMAT.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime
   *   The created datetime or a default object.
   */
  protected static function createDateTimeFromString($date) {
    foreach ([self::DATE_FORMAT, self::DATE_TIME_FORMAT] as $format) {
      try {
        return DrupalDateTime::createFromFormat($format, $date);
      }
      catch(\Exception $e) {
        continue;
      }
    }
    return new DrupalDateTime();
  }

  /**
   * Set the timezone for returned event dates and times.
   *
   * @param $timezone_name
   *   A string describing a valid timezone.
   */
  public function setTimeZone($timezone_name) {
    if ($timezone = new \DateTimeZone($timezone_name)) {
      $this->setParam(self::PARAM_TIMEZONE, $timezone_name);
    }
  }

  /**
   * Set whether recurring events shall be returned separately.
   *
   * @param bool $single_events
   *   Whether to return single events or not.
   *
   * @return \Drupal\calendar_hours_google\Plugin\calendar_hours\api\EventQuery
   *   The called instance.
   */
  public function setReturnEventSeriesAsSingleEvents($single_events) {
    return $this->setParam(self::PARAM_RETURN_SINGLE_EVENTS, $single_events);
  }

  /**
   * Limit the number of results that are included in the response.
   *
   * @param int $max_results
   *   A positive integer <= MAX_MAX_RESULTS.
   *
   * @return \Drupal\calendar_hours_google\Plugin\calendar_hours\api\EventQuery
   *   The called instance.
   */
  public function setMaxResults($max_results) {
    if ($max_results > 0 && $max_results <= self::MAX_RESULTS_MAX) {
      return $this->setParam(self::PARAM_MAX_RESULTS, $max_results);
    }
    return $this;
  }

  /**
   * Set the sort order of the result set.
   *
   * @param $order_by
   *   The key by which to order returned events.
   *
   * @return \Drupal\calendar_hours_google\Plugin\calendar_hours\api\EventQuery
   *   The called instance.
   */
  public function setOrderBy($order_by) {
    $allowed_values = [
      self::ORDER_BY_EVENT_START_TIME,
      self::ORDER_BY_EVENT_MODIFICATION_TIME,
    ];
    if (in_array($order_by, $allowed_values)) {
      return $this->setParam(self::PARAM_ORDER_BY, $order_by);
    }
    return $this;
  }

  /**
   * Execute the query.
   *
   * @return \Google_Service_Calendar_Event
   *   List of Google Calendar events.
   */
  public function execute() {
    $events = $this->api()
      ->events
      ->listEvents(
        $this->getCalendarId(), $this->getParams());
    return $events->getItems();
  }

}
