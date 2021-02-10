<?php

namespace Drupal\calendar_hours_server\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Form controller for Hours Calendar refresh forms.
 *
 * @package Drupal\calendar_hours_server\Form
 */
class HoursCalendarRefreshForm extends EntityConfirmFormBase {

  /**
   * @inheritDoc
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to refresh @calendar?', [
      '@calendar' => $this->entity->label()
    ]);
  }

  /**
   * {@inheritDoc}
   */
  public function getDescription() {
    return $this->t('Any cached responses from this calendar will be removed.');
  }

  /**
   * @inheritDoc
   */
  public function getCancelUrl() {
    return new Url('entity.hours_calendar.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\calendar_hours_server\Entity\HoursCalendar $calendar */
    $calendar = $this->entity;

    $calendar->refresh();
    $this->messenger()
      ->addMessage($this->t('@calendar has been refreshed.', [
        '@calendar' => $this->entity->label()
      ]));

    $form_state->setRedirectUrl($this->getCancelUrl());
  }


}
