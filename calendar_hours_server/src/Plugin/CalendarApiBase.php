<?php

namespace Drupal\calendar_hours_server\Plugin;

use Drupal\calendar_hours_server\Entity\HoursCalendar;
use Drupal\Core\Datetime\DrupalDateTime;
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