<?php

namespace Drupal\calendar_hours_server\Plugin\calendar_hours\formatter;

use Drupal\calendar_hours_server\Plugin\FormatterBase;
use Drupal\calendar_hours_server\Plugin\FormatterInterface;
use Drupal\calendar_hours_server\Response\Block;

/**
 * @Formatter(
 *   id = "format_groupby",
 *   label = @Translation("Group-by Formatter"),
 * )
 */
class GroupBy extends FormatterBase {

  /**
   * Group blocks by start date.
   *
   * @param Block[] $blocks
   *   The blocks to group.
   *
   * @return array
   *   Blocks grouped by start date.
   */
  protected function valueStartDate(array $blocks) {
    $groupedBlocks = [];
    foreach ($blocks as $block) {
      $groupedBlocks[$block->from->format('Y-m-d')][] = $block;
    }
    return $groupedBlocks;
  }

}
