<?php

namespace Drupal\calendar_hours_server\Plugin;

use Drupal\calendar_hours_server\Entity\HoursCalendar;
use Drupal\calendar_hours_server\Response\Block;
use Drupal\Core\Plugin\PluginBase;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

abstract class CalendarApiBase extends PluginBase implements CalendarApiInterface {

  const DATE_FORMAT = 'Y-m-d';
  const DATE_TIME_FORMAT = 'c';

  /**
   * Services this Plugin may need to rely on.
   *
   * @var array
   */
  protected $services;

  /**
   * {@inheritdoc}
   */
  public function injectService($alias, $service_id) {
    try {
      $service = \Drupal::getContainer()->get($service_id);
      $this->services[$alias] = $service;
    }
    catch (ServiceNotFoundException $e) {
      echo $e->getMessage();
    }
  }

  /**
   * {@inheritDoc}
   */
  public function setHours(HoursCalendar $calendar, $event_id, $from, $to) {
    if ($this->doSetHours($calendar, $event_id, $from, $to)) {
      $calendar->refresh();
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Perform the actual hours update on the remote API.
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
  abstract protected function doSetHours(HoursCalendar $calendar, $event_id, $from, $to);

  /**
   * {@inheritDoc}
   */
  public function createHours(HoursCalendar $calendar, Block $block) {
    if ($this->doCreateHours($calendar, $block)) {
      $calendar->refresh();
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Perform the actual creation of a new block of hours on the remote API.
   *
   * @param \Drupal\calendar_hours_server\Entity\HoursCalendar $calendar
   *   The calendar to receive the block.
   * @param \Drupal\calendar_hours_server\Response\Block $block
   *   The block.
   *
   * @return bool
   *   TRUE if the hours could successfully be created. FALSE otherwise.
   */
  abstract protected function doCreateHours(HoursCalendar $calendar, Block $block);

  }

  /**
   * Retrieve a depending service.
   *
   * @param string $name
   *   Name of the service.
   *
   * @return mixed
   *   An instance of the requested service.
   */
  public function __get($name) {
    $service_id = strtolower(preg_replace("/(?<=\\w)(?=[A-Z])/","_$1", $name));
    return $this->services[$service_id];
  }

  /**
   * Retrieve the date format.
   *
   * @return string
   *   A date time string.
   */
  public function getDateFormat() {
    return self::DATE_FORMAT;
  }

  /**
   * Retrieve the datetime format.
   *
   * @return string
   *   A date time string.
   */
  public function getDateTimeFormat() {
    return self::DATE_TIME_FORMAT;
  }

}
