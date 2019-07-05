<?php

namespace Drupal\calendar_hours_server\Response;

use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Fixed period of time during which a resource is open.
 */
final class Block {

  /**
   * ID of the calendar this block belongs to.
   *
   * @var string
   */
  public $calendarId;

  /**
   * When the block starts.
   *
   * @var \Drupal\Core\Datetime\DrupalDateTime
   */
  public $from;

  /**
   * When the block ends.
   *
   * @var \Drupal\Core\Datetime\DrupalDateTime
   */
  public $to;

  /**
   * Block constructor.
   *
   * @param string $calendarId
   *   ID of the calendar this block belongs to.
   * @param \Drupal\Core\Datetime\DrupalDateTime $from
   *   Timestamp at which the block opens.
   * @param \Drupal\Core\Datetime\DrupalDateTime $to
   *   Timestamp at which the block closes.
   */
  public function __construct($calendarId, DrupalDateTime $from, DrupalDateTime $to) {
    $this->calendarId = $calendarId;
    $this->from = $from;
    $this->to = $to;
  }

  /**
   * Determines whether this block begins before a given time.
   *
   * @param \Drupal\Core\Datetime\DrupalDateTime $dateTime
   *   The timestamp to compare against.
   *
   * @return int
   *   -1: this block starts after $dateTime
   *    1: this block starts before $dateTime
   *    0: this block starts exactly at $dateTime
   */
  public function startsBefore(DrupalDateTime $dateTime) {
    if (($diff = $this->from->diff($dateTime)->invert) > 0) {
      return 1;
    }
    elseif (($diff = $dateTime->diff($this->from)->invert) > 0) {
      return -1;
    }
    return 0;
  }

  /**
   * Determines whether this block ends before a given time.
   *
   * @param \Drupal\Core\Datetime\DrupalDateTime $dateTime
   *   The timestamp to compare against.
   *
   * @return int
   *   -1: this block ends before $dateTime
   *    1: this block ends after $dateTime
   *    0: this block ends exactly at $dateTime
   */
  public function endsBefore(DrupalDateTime $dateTime) {
    if (($diff = $this->to->diff($dateTime)->invert) > 0) {
      return -1;
    }
    elseif (($diff = $dateTime->diff($this->to)->invert) > 0) {
      return 1;
    }
    return 0;
  }

  /**
   * String formatted start date of this block. Used for processing of 'group-by' parameter.
   *
   * @return string
   *   The start date of this block, formatted as string.
   */
  public function startDate() {
    return $this->from->format('Y-m-d');
  }

  /**
   * String formatted end date of this block. Used for processing of 'group-by' parameter.
   *
   * @return string
   *   The end date of this block, formatted as string.
   */
  public function endDate() {
    return $this->to->format('Y-m-d');
  }

}
