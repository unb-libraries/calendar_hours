<?php

namespace Drupal\calendar_hours_client\Controller;

use Drupal\Core\Controller\ControllerBase;

class HoursCalendarClientDemoController extends ControllerBase {

  public function Demo() {
    return [
      '#theme' => 'calendar_hours_demo',
    ];
  }

}