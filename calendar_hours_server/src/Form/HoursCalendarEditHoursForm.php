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

  protected const METHOD_DELETE = 0;
  protected const METHOD_EDIT = 1;

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

    $form['method'] = [
      '#type' => 'radios',
      '#options' => [
        self::METHOD_DELETE => $this->t('Close'),
        self::METHOD_EDIT => $this->t('Edit Opens/Closes'),
      ],
      '#default_value' => self::METHOD_EDIT,
    ];

    if (!empty($hours)) {
      $form['block_id'] = [
        '#type' => 'hidden',
        '#default_value' => $hours[0]->getId(),
      ];

      $form['opens'] = [
        '#type' => 'datetime',
        '#title' => $this->t('Opens'),
        '#date_date_element' => 'none',
        '#default_value' => $hours[0]->getStart(),
        '#date_timezone' => $hours[0]->getStart()->getTimezone()->getName(),
      ];

      $form['closes'] = [
        '#type' => 'datetime',
        '#date_date_element' => 'none',
        '#title' => $this->t('Closes'),
        '#default_value' => $hours[0]->getEnd(),
        '#date_timezone' => $hours[0]->getEnd()->getTimezone()->getName(),
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

    if ($event_id = $form_state->getValue('block_id')) {
      $from = $form_state->getValue('opens');
      $to = $form_state->getValue('closes');

      try {
        $calendar->setHours($event_id, $from, $to);
        $this->messenger()->addStatus('Hours updated');
      }
      catch (\Exception $e) {
        $this->messenger()->addError($e->getMessage());
      }
    }
  }

}
