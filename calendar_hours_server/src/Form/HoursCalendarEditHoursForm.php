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

    $form['date'] = [
      '#type' => 'date',
      '#title' => $this->t('Date'),
      '#default_value' => $this->getDate($form_state)->format('Y-m-d'),
//      '#ajax' => [
//        'callback' => [$this, 'ajaxSelectDate'],
//        'event' => 'change',
//        'wrapper' => 'blocks',
//        'disable-refocus' => TRUE,
//      ],
    ];

    $form['date-select'] = [
      '#type' => 'submit',
      '#submit' => ['::selectDate'],
      '#value' => $this->t('Select'),
    ];


    // This can only be used when the form does not use any AJAX, possibly because
    // Drupal and Google PHP Client API are not fully compatible.
    // Use the commented-out alternative when AJAX is implemented.
    $form['blocks'] = $this->buildHoursSubForm([
      '#type' => 'fieldset',
      '#title' => $this->t('Current Hours'),
    ], $form_state);

//    $form['blocks'] = [
//      '#type' => 'fieldset',
//      '#title' => $this->t('Current Hours'),
//      '#attributes' => [
//        'id' => 'blocks',
//      ],
//    ];

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = $this->t('Update Hours');

    $actions['close'] = [
      '#type' => 'submit',
      '#submit' => ['::close'],
      '#value' => $this->t('Close'),
    ];

    return $actions;
  }

  /**
   * Submit handler for the date 'Select' action.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function selectDate(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('entity.hours_calendar.edit_hours_form', [
      'hours_calendar' => $this->getEntity()->id(),
    ], [
      'query' => [
        'date' => $form_state->getValue('date'),
      ],
    ]);
  }

  /**
   * Callback to receive calls upon changes to the 'date' form field.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   A form element.
   */
  public function ajaxSelectDate(array &$form, FormStateInterface $form_state) {
    return $this->buildHoursSubForm($form['blocks'], $form_state);
  }

  /**
   * Submit handler for the 'Close' action.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function close(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\calendar_hours_server\Entity\HoursCalendar $calendar */
    $calendar = $this->getEntity();

    if ($calendar->close($this->getDate($form_state))) {
      $this->messenger()->addStatus($this->t('@calendar successfully close.', [
        '@calendar' => $calendar->label(),
      ]));
    }
    else {
      $this->messenger()->addError($this->t('@calendar could not be closed.', [
        '@calendar' => $calendar->label(),
      ]));
    }
  }

  /**
   * Build the form elements to edit hours.
   *
   * @param array $container
   *   The form element to act as container.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   A render array containing the generated sub-form.
   */
  protected function buildHoursSubForm(array $container, FormStateInterface $form_state) {
    /** @var \Drupal\calendar_hours_server\Entity\HoursCalendar $calendar */
    $calendar = $this->getEntity();

    if ($date = $this->getDate($form_state)) {
      $hours = $calendar->getHours($date, $date);
      foreach ($hours as $index => $block) {
        $container[$index] = [
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
    }

    return $container;
  }

  /**
   * Retrieve the date which shall be processed.
   *
   * If a date has been entered into a form field,
   * that date will be used. If otherwise the current
   * request contains a date as a query parameter, that
   * date will be used. If no date can be found in
   * either place, today's date will be returned.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime
   *   A date time object.
   */
  protected function getDate(FormStateInterface $form_state) {
    if ($form_state->hasValue('date')) {
      $date = DrupalDateTime::createFromFormat(
        'Y-m-d', $form_state->getValue('date'));
    }
    elseif ($this->getRequest()->query->has('date')) {
      $date = DrupalDateTime::createFromFormat(
        'Y-m-d', $this->getRequest()->query->get('date'));
    }
    else {
      $date = new DrupalDateTime();
    }
    return $date;
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    /** @var \Drupal\calendar_hours_server\Entity\HoursCalendar $calendar */
    $calendar = $this->getEntity();

    if ($date = $this->getDate($form_state)) {
      try {
        foreach ($calendar->getHours($date, $date) as $index => $blocks) {
          $event_id = $form_state->getValue("block_id:{$index}");

          /** @var \Drupal\Core\Datetime\DrupalDateTime $from */
          $from = $form_state->getValue("opens:{$index}");
          $from->setDate(
            intval($date->format('Y')),
            intval($date->format('m')),
            intval($date->format('d'))
          );

          /** @var \Drupal\Core\Datetime\DrupalDateTime $to */
          $to = $form_state->getValue("closes:{$index}");
          $to->setDate(
            intval($date->format('Y')),
            intval($date->format('m')),
            intval($date->format('d'))
          );

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
