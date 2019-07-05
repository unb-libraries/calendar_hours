<?php

namespace Drupal\calendar_hours_server\Plugin;

use Drupal\calendar_hours_server\Response\Block;

interface FormatterInterface {

  /**
   * Apply the formatter to the array of blocks.
   *
   * @param Block[] $blocks
   *   The blocks to process.
   * @param mixed $value
   *   The value to apply to the input array.
   */
  public function format(array $blocks, $value);

  /**
   * Retrieve a list of plugins this formatter depends on.
   *
   * @return array
   *   An array of formatter plugin instances, keyed by the plugin IDs.
   */
  public function getDependencies();

}