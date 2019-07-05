<?php

namespace Drupal\calendar_hours_server\Plugin;

use Drupal\calendar_hours_server\Entity\HoursCalendar;
use Drupal\Core\Plugin\PluginBase;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

abstract class CalendarApiBase extends PluginBase implements CalendarApiInterface {

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

  public function __get($name) {
    $service_id = strtolower(preg_replace("/(?<=\\w)(?=[A-Z])/","_$1", $name));
    return $this->services[$service_id];
  }

}