<?php

namespace Drupal\calendar_hours_server\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Class Formatter.
 *
 * @package Drupal\calendar_hours_server\Annotation
 *
 * @Annotation
 */
class Formatter extends Plugin {

  /**
   * A unique identifier for the process plugin.
   *
   * @var string
   */
  public $id;

  /**
   * @var string[]
   */
  public $execute_after = [];

}