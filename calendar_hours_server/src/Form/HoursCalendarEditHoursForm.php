<?php

namespace Drupal\calendar_hours_server\Form;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

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
        '#value' => [
          'object' => $hours[0]->getStart(),
        ],
      ];

      $form['closes'] = [
        '#type' => 'datetime',
        '#date_date_element' => 'none',
        '#title' => $this->t('Closes'),
        '#value' => [
          'object' => $hours[0]->getEnd(),
        ],
      ];
    }

    return $form;
  }

}
