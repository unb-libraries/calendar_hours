<?php

namespace Drupal\calendar_hours_server\Plugin;

use Drupal\Core\Plugin\PluginBase;

class FormatterBase extends PluginBase implements FormatterInterface {

  /**
   * {@inheritdoc}
   */
  public function format(array $blocks, $value) {
    $method_name = 'value' . str_replace('-', '', ucwords($value, '-'));
    if (method_exists($this, $method_name)) {
      $blocks = $this->$method_name($blocks);
    }
    return $blocks;
  }

  public function getDependencies() {
    if (array_key_exists('execute_after', $this->configuration)) {
      return $this->configuration['execute_after'];
    }
    return [];
  }

}