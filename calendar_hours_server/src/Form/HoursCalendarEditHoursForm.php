<?php

namespace Drupal\calendar_hours_server\Form;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form to edit calendar hours.
 *
 * @package Drupal\calendar_hours_server\Form
 */
class HoursCalendarEditHoursForm extends EntityForm {

  protected const ACTION_DELETE = 0;
  protected const ACTION_EDIT = 1;

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    /** @var \Drupal\calendar_hours_server\Entity\HoursCalendar $calendar */
    $calendar = $this->getEntity();

    $now = new DrupalDateTime();
    $today = $now->format('Y-m-d');

    $hours = $calendar->getHours($today, $today);

    $form['date'] = [
      '#type' => 'date',
      '#title' => $this->t('Date'),
      '#default_value' => $today,
    ];

    $form['blocks'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Current Hours'),
    ];

    foreach ($hours as $index => $block) {
      $form['blocks'][$index] = [
        '#type' => count($hours) > 1 ? 'fieldset' : 'container',
        '#title' => sprintf('%s - %s',
          $block->getStart()->format('h:i a'), $block->getEnd()->format('h:i a')),
        "block_id:{$index}" => [
          '#type' => 'hidden',
          '#default_value' => $block->getId(),
        ],
        "opens:{$index}" => [
          '#type' => 'datetime',
          '#date_date_element' => 'none',
          '#default_value' => $block->getStart(),
          '#date_timezone' => $block->getStart()->getTimezone()->getName(),
        ],
        "closes:{$index}" => [
          '#type' => 'datetime',
          '#date_date_element' => 'none',
          '#default_value' => $block->getEnd(),
          '#date_timezone' => $block->getEnd()->getTimezone()->getName(),
        ],
      ];
    }

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    /** @var \Drupal\calendar_hours_server\Entity\HoursCalendar $calendar */
    $calendar = $this->getEntity();

    if ($date = $form_state->getValue('date')) {
      try {
        foreach ($calendar->getHours($date, $date) as $index => $blocks) {
          $event_id = $form_state->getValue("block_id:{$index}");
          $from = $form_state->getValue("opens:{$index}");
          $to = $form_state->getValue("closes:{$index}");
          $calendar->setHours($event_id, $from, $to);
        }
        $this->messenger()->addStatus('Hours updated');
      }
      catch (\Exception $e) {
        $this->messenger()->addError($e->getMessage());
        $this->messenger()->addError('Hours not or only partially updated.');
      }
    }
  }

}
