<?php

namespace Drupal\calendar_hours_server\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Class CalendarApi.
 *
 * @package Drupal\calendar_hours_server\Annotation
 *
 * @Annotation
 */
class CalendarApi extends Plugin {

  /**
   * A unique identifier for the API plugin.
   *
   * @var string
   */
  public $id;

  /**
   * A unique key identifying the APIs provider.
   *
   * @var string
   */
  public $vendor;

  /**
   * Map of optional depending services.
   *
   * @var array optional
   */
  public $services = [];

}