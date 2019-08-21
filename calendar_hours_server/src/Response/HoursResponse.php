<?php

namespace Drupal\calendar_hours_server\Response;

use Drupal\rest\ResourceResponse;

/**
 * Opening hours during a fixed period of time.
 *
 * @package Drupal\calendar_hours_server\Response
 */
final class HoursResponse extends ResourceResponse implements \Iterator {

  /**
   * ID of the calendar from which this excerpt has been extracted.
   *
   * @var \Drupal\calendar_hours_server\Entity\HoursCalendar
   */
  protected $calendar;

  /**
   * Array of Blocks during which the resource represented by the Calendar is open.
   *
   * @var \Drupal\calendar_hours_server\Response\Block[]
   */
  protected $blocks;

  /**
   * Constructor an excerpt for a calendar for the time period defined by the given Blocks.
   *
   * @param \Drupal\calendar_hours_server\Entity\HoursCalendar $calendar
   *   Calendar from which to extract an excerpt.
   * @param array $hours
   *   Array of Blocks during which the resource represented by the Calendar is open.
   * @param array $format_options
   *   Options to influence the output format.
   * @param int $status
   *   The response status code.
   * @param array $headers
   *   An array of response headers.
   */
  public function __construct($calendar, $hours = [], $format_options = [], $status = 200, array $headers = []) {
    $this->calendar = $calendar;
    usort($hours['blocks'], [$this, 'sortAsc']);
    $this->blocks = $hours['blocks'];

    $start_date = !empty($this->blocks) ? $this->blocks[0]->from->format('Y-m-d') : '';
    $end_date = !empty($this->blocks) ? $this->blocks[count($this->blocks) - 1]->from->format('Y-m-d') : '';

    $this->format($format_options);

    parent::__construct([
      'id' => $calendar->id,
      'title' => $calendar->title,
      'startDate' => $start_date,
      'endDate' => $end_date,
      'hours' => $this->toArray(),
      'reopensAt' => $hours['opensAt'],
      'closesAt' => $hours['closesAt'],
    ], $status, $headers);


  }

  protected function format($options = []) {
    $format_manager = \Drupal::service('plugin.manager.calendar_hours.formatter');
    $this->blocks = $format_manager->format($this->blocks, $options);
  }

  protected function toArray($blocks = NULL) {
    if (!isset($blocks)) {
      $blocks = $this->blocks;
    }
    $array = [];
    foreach ($blocks as $key => $block) {
      if ($block instanceof Block) {
        $array[$key] = [
          'from' => $block->from->format('c'),
          'to' => $block->to->format('c'),
        ];
      }
      elseif (is_array($block)) {
        $array[$key] = $this->toArray($block);
      }
    }
    return $array;
  }

  /**
   * Return the current block.
   *
   * @return Block
   *   The current block.
   */
  public function current() {
    return current($this->blocks);
  }

  /**
   * Move forward to the next block.
   *
   * @return Block|bool
   *   The next block, or false, if there are no further blocks.
   */
  public function next() {
    return next($this->blocks);
  }

  /**
   * Return the key of the current block.
   *
   * @return int|string
   *   Group key as defined by 'group-by' parameter. Array index if no 'group-by' value is set.
   */
  public function key() {
    return key($this->blocks);
  }

  /**
   * Checks if the current position is valid.
   *
   * @return bool
   *   True if the current element is an instance of Block. False otherwise.
   */
  public function valid() {
    return $this->current() instanceof Block;
  }

  /**
   * Rewind the iterator to the first block.
   */
  public function rewind() {
    reset($this->blocks);
  }

  /**
   * Sorts two Blocks based on their "from" or, if equal, their "to" timestamps.
   *
   * @param \Drupal\calendar_hours_server\Response\Block $a
   *   The Block in question.
   * @param \Drupal\calendar_hours_server\Response\Block $b
   *   The Block to compare against.
   *
   * @return int
   *   < 0: $a starts or, if equal, finishes before $b
   *   > 0: $a starts or, if equal, finishes after $b
   *   = 0: $a starts and finishes at the same time as $b
   */
  protected function sortAsc(Block $a, Block $b) {
    if (($diff_from = $a->startsBefore($b->from)) != 0) {
      return $diff_from;
    }
    if (($diff_to = $a->endsBefore($b->to)) != 0) {
      return $diff_to;
    }
    return 0;
  }

}
