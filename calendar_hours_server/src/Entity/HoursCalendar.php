<?php

namespace Drupal\calendar_hours_server\Entity;

use Drupal\calendar_hours_server\Plugin\CalendarApiInterface;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * ConfigEntity representing a GoogleCalendar.
 *
 * @ConfigEntityType(
 *   id = "hours_calendar",
 *   label = @Translation("Hours Calendar"),
 *   handlers = {
 *     "list_builder" = "Drupal\calendar_hours_server\HoursCalendarListBuilder",
 *     "storage" = "Drupal\calendar_hours_server\Entity\HoursCalendarStorage",
 *     "form" = {
 *       "edit" = "Drupal\calendar_hours_server\Form\HoursCalendarForm",
 *       "enable" = "Drupal\calendar_hours_server\Form\HoursCalendarForm",
 *       "disable" = "Drupal\calendar_hours_server\Form\HoursCalendarDisableForm",
 *     }
 *   },
 *   config_prefix = "hours_calendar",
 *   admin_permission = "administer hours calendars",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "title",
 *     "status" = "status",
 *   },
 *   config_export = {
 *     "id",
 *     "title",
 *     "vendor",
 *     "foreign_id"
 *   },
 *   links = {
 *     "edit-form" = "/admin/config/services/hours/calendars/{hours_calendar}/edit",
 *     "enable" = "/admin/config/services/hours/calendars/{vendor}/{foreign_id}/enable",
 *     "disable" = "/admin/config/services/hours/calendars/{hours_calendar}/disable",
 *   }
 * )
 */
class HoursCalendar extends ConfigEntityBase {

  /**
   * Hours Calendar ID.
   *
   * @var string
   */
  public $id;

  /**
   * Calendar Title.
   *
   * @var string
   */
  public $title;

  /**
   * Name of the Vendor, e.g. 'google'.
   *
   * @var string
   */
  public $vendor;

  /**
   * Foreign Calendar ID, e.g. as used by Google Calendar API.
   *
   * @var string
   */
  public $foreign_id;

  /**
   * @var \Drupal\calendar_hours_server\Plugin\CalendarApiBase
   */
  protected $calendarApi;

  /**
   * Set the handler to connect to the vendor's API.
   *
   * @param CalendarApiInterface $calendar_api
   */
  public function setCalendarApi(CalendarApiInterface $calendar_api) {
    $this->calendarApi = $calendar_api;
  }

  /**
   * {@inheritdoc}
   *
   * Returns <vendor>__<foreign_id> if no ID has been explicitly set.
   */
  public function id() {
    if (!$this->status()) {
      return sprintf('%s__%s', $this->vendor, $this->foreign_id);
    }
    return parent::id();
  }

  /**
   * Checks whether an ID has been set.
   *
   * {@inheritdoc}
   */
  public function status() {
    return isset($this->id);
  }

  /**
   * Suggests a value for ID, based on the given $field.
   *
   * @param string $field
   *   Name of a field to use as base for suggesting an ID.
   *
   * @return string
   *   An acronym of $field.
   */
  public function suggestId($field) {
    if(!$this->status() && preg_match_all('/\b(\w)/',strtolower($field),$m)) {
      return implode('',$m[1]);
    }
    return $field;
  }

  /**
   * Add 'vendor' and 'vid' query parameters, if the target URL mathces the 'enable' route.
   *
   * {@inheritdoc}
   */
  public function toUrl($rel = 'edit-form', array $options = []) {
    if (!$this->status() && $rel === 'enable') {
      $options['query'] = [
        'vendor' => $this->vendor,
        'vid' => $this->foreign_id,
      ];
    }
    return parent::toUrl($rel, $options);
  }

  /**
   * Retrieve hours for the defined time period.
   *
   * @param DrupalDateTime $from
   *   Earliest Date to be included in the hours response.
   * @param DrupalDateTime $to
   *   Latest Date to be included in the hours response.
   *
   * @return \Drupal\calendar_hours_server\Response\Block[]
   */
  public function getHours($from, $to) {
    return $this->calendarApi->getHours($this, $from, $to);
  }

  /**
   * Retrieve any alerts affecting the unit represented by this calendar.
   *
   * @param array $options
   *   Accepted keys are:
   *     - from: timestamp; lower boundary to search for alerts
   *     - to: timestamp; upper boundary to search for alerts
   *
   * @return Alert[]
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getAlerts($options = []) {
    $alert_query = \Drupal::entityQuery('hours_calendar_alert');
    $base_condition = $alert_query->orConditionGroup()
      ->condition('calendar', $this->id)
      ->notExists('calendar');

    $timezone = \Drupal::currentUser()->getTimeZone();
    if (!array_key_exists('from', $options)) {
      $beginning_of_today = new DrupalDateTime('now', $timezone);
      $beginning_of_today->setTime(0, 0, 0);
      $options['from'] = $beginning_of_today->format('c');
    }

    if (!array_key_exists('to', $options)) {
      $end_of_today = new DrupalDateTime('now', $timezone);
      $end_of_today->setTime(23, 59, 59);
      $options['to'] = $end_of_today->format('c');
    }

    $optional_conditions = $alert_query->orConditionGroup()
      ->condition('visible_interval__value', [$options['from'], $options['to']], 'BETWEEN')
      ->condition('visible_interval__end_value', [$options['from'], $options['to']], 'BETWEEN')
      ->condition($alert_query->andConditionGroup()
        ->condition('visible_interval__value', $options['from'], '<=')
        ->condition('visible_interval__end_value', $options['to'], '>=')
      );

    $alert_ids = $alert_query
      ->condition($base_condition)
      ->condition($optional_conditions)
      ->execute();

    /** @var Alert[] $alerts */
    $alerts = $this->entityTypeManager()->getStorage('hours_calendar_alert')->loadMultiple(array_keys($alert_ids));

    return $alerts;
  }

  /**
   * Retrieve the next time the unit represented by this calendar opens or, if already open, reopens.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime
   *     Time the unit opens (if currently closed) or reopens (if currently open). NULL if it never reopens again.
   */
  public function getOpensAt() {
    return $this->calendarApi->getOpensAt($this);
  }

  /**
   * Retrieve the next time the unit represented by the given calendar closes.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime
   *     Time the unit closes next. NULL if already closed.
   */
  public function getClosesAt() {
    return $this->calendarApi->getClosesAt($this);
  }

  /**
   * {@inheritDoc}
   */
  public function calculateDependencies() {
    parent::calculateDependencies();
    $module_name = $this->calendarApi->getPluginDefinition()['provider'];
    $this->addDependency('module', $module_name);
    return $this;
  }

}
