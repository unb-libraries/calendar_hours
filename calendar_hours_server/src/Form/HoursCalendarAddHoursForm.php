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
      '#type' => 'datetime',
      '#title' => $this->t('Date'),
      '#date_time_element' => 'none',
      '#default_value' => $date,
      '#date_timezone' => $timezone->getName(),
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

    /** @var \Drupal\Core\Datetime\DrupalDateTime $date */
    $date = $form_state->getValue('date');
    /** @var \Drupal\Core\Datetime\DrupalDateTime $from */
    $from = $form_state->getValue('from');
    $from->setDate(
      intval($date->format('Y')),
      intval($date->format('m')),
      intval($date->format('d'))
    );
    /** @var \Drupal\Core\Datetime\DrupalDateTime $to */
    $to = $form_state->getValue('to');
    $to->setDate(
      intval($date->format('Y')),
      intval($date->format('m')),
      intval($date->format('d'))
    );

    if ($from->getTimestamp() - $to->getTimestamp() > 0) {
      $to->add(\DateInterval::createFromDateString('1 day'));
    }

    $this->tryCreatingHours($from, $to);
  }

  /**
   * Try creating hours. Display a message on success or failure.
   *
   * @param \Drupal\Core\Datetime\DrupalDateTime $from
   *   When hours should begin.
   * @param \Drupal\Core\Datetime\DrupalDateTime $to
   *   When hours should end.
   */
  protected function tryCreatingHours(DrupalDateTime $from, DrupalDateTime $to) {
    try {
      $this->getCalendar()->createHours($from, $to);
      $this->messenger()->addStatus($this->t('Hours successfully created.'));
    }
    catch (\Exception $e) {
      $this->messenger()->addError($e->getMessage());
      $this->messenger()->addError($this->t('Hours could not be created.'));
    }
  }

}
