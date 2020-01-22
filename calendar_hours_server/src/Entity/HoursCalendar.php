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
 *       "edit_hours" = "Drupal\calendar_hours_server\Form\HoursCalendarEditHoursForm",
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
 *     "edit-hours-form" = "/admin/config/services/hours/calendars/{hours_calendar}/hours",
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
   * @param DrupalDateTime|string $from
   *   Earliest Date to be included in the hours response.
   * @param DrupalDateTime|string $to
   *   Latest Date to be included in the hours response.
   *
   * @return \Drupal\calendar_hours_server\Response\Block[]
   */
  public function getHours($from, $to) {
    if (!is_string($from)) {
      $from = $from->format($this->calendarApi->getDateFormat());
    }
    if (!is_string($to)) {
      $to = $to->format($this->calendarApi->getDateFormat());
    }
    return $this->calendarApi->getHours($this, $from, $to);
  }

  /**
   * Set hours for the defined time period.
   *
   * @param string $event_id
   *   ID of the event to update.
   * @param \Drupal\Core\Datetime\DrupalDateTime|string $from
   *   Earliest Date to be included in the hours response.
   * @param \Drupal\Core\Datetime\DrupalDateTime|string $to
   *   Latest Date to be included in the hours response.
   *
   * @return bool
   *   TRUE if hours could successfully be updated. FALSE otherwise.
   */
  public function setHours($event_id, $from, $to) {
    if (!is_string($from)) {
      $from = $from->format($this->calendarApi->getDateTimeFormat());
    }
    if (!is_string($to)) {
      $to = $to->format($this->calendarApi->getDateTimeFormat());
    }
    return $this->calendarApi->setHours($this, $event_id, $from, $to);
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
