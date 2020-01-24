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
class HoursCalendarAddHoursForm extends EntityForm {

  /**
   * Retrieve calendar.
   *
   * @return \Drupal\calendar_hours_server\Entity\HoursCalendar
   *   An instance of HoursCalendar.
   */
  protected function getCalendar() {
    /** @var \Drupal\calendar_hours_server\Entity\HoursCalendar $calendar */
    $calendar = $this->getEntity();
    return $calendar;
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $timezone = $this->getCalendar()->getTimezone();
    $now = new DrupalDateTime('now', $timezone);
    if ($this->getRequest()->query->has('date')) {
      $date = DrupalDateTime::createFromFormat('Y-m-d',
        $this->getRequest()->query->get('date'));
      $now->setDate(
        intval($date->format('Y')),
        intval($date->format('m')),
        intval($date->format('d'))
      );
    }
    else {
      $date = clone $now;
    }

    $from = clone $now;
    $to = (clone $from)
      ->setTime(0, 0, 0)
      ->add(\DateInterval::createFromDateString('1 day'));

    $form['date'] = [
      '#type' => 'date',
      '#title' => $this->t('Date'),
      '#default_value' => $date->format('Y-m-d'),
    ];

    $form['from'] = [
      '#type' => 'datetime',
      '#title' => $this->t('From'),
      '#date_date_element' => 'none',
      '#default_value' => $from,
      '#date_timezone' => $timezone->getName(),
    ];

    $form['to'] = [
      '#type' => 'datetime',
      '#title' => $this->t('To'),
      '#date_date_element' => 'none',
      '#default_value' => $to,
      '#date_timezone' => $timezone->getName(),
    ];

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    /** @var \Drupal\Core\Datetime\DrupalDateTime $from */
    $from = $form_state->getValue('from');
    /** @var \Drupal\Core\Datetime\DrupalDateTime $to */
    $to = $form_state->getValue('to');

    if ($from->getTimestamp() - $to->getTimestamp() > 0) {
      $to->add(\DateInterval::createFromDateString('1 day'));
    }

    dpm($from);
    dpm($to);
  }

}
