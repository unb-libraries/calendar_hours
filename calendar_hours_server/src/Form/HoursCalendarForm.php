<?php

namespace Drupal\calendar_hours_server\Form;

use Drupal\calendar_hours_server\Entity\HoursCalendarStorage;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Form controller for Hours Calendar edit forms.
 *
 * @ingroup calendar_hours_server
 */
class HoursCalendarForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $form['id'] = [
      '#type' => 'machine_name',
      '#title' => 'ID',
      '#default_value' => isset($this->entity->id) ? $this->entity->id : $this->entity->suggestId($this->entity->title),
      '#machine_name' => [
        'exists' => [$this, 'exist'],
        'label' => 'ID',
        'standalone' => TRUE,
      ],
    ];

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#description' => $this->t('Administrative Title under which Calendar appears throughout the admin interface.'),
      '#default_value' => $this->entity->title,
    ];

    $form['vendor'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Vendor'),
      '#description' => $this->t('External provider of this Calendar.'),
      '#default_value' => $this->entity->vendor,
      '#disabled' => TRUE,
    ];

    $form['foreign_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Foreign ID'),
      '#description' => $this->t('The ID assigned by the remote service, e.g. a Google Calendar ID'),
      '#default_value' => $this->entity->foreign_id,
      '#disabled' => TRUE,
    ];

    return $form;
  }

  /**
   * Helper function to check whether an Example configuration entity exists.
   */
  public function exist($id) {
    $entity = $this->entityTypeManager->getStorage('hours_calendar')->getQuery()
      ->condition('id', $id)
      ->execute();
    return (bool) $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $hours_calendar = $this->entity;
    $status = $hours_calendar->save();

    if ($status) {
      $this->messenger()->addMessage($this->t('Saved the %label Hours Calendar.', [
        '%label' => $hours_calendar->label(),
      ]));
    }
    else {
      $this->messenger()->addMessage($this->t('The %label Hours Calendar was not saved.', [
        '%label' => $hours_calendar->label(),
      ]), MessengerInterface::TYPE_ERROR);
    }

    $form_state->setRedirect('entity.hours_calendar.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityFromRouteMatch(RouteMatchInterface $route_match, $entity_type_id) {
    if (($vendor = $this->getRequest()->query->get('vendor')) && ($foreign_id = $this->getRequest()->query->get('vid'))) {
      /** @var HoursCalendarStorage $storage */
      $storage = $this->entityTypeManager->getStorage($entity_type_id);
      return $storage->create([
        'vendor' => $vendor,
        'foreign_id' => $foreign_id,
      ]);
    }
    return parent::getEntityFromRouteMatch($route_match, $entity_type_id);
  }

  /**
   * Overrides EntityForm::actionsElement. Sets Submit-Button's label to 'Enable'.
   */
  protected function actionsElement(array $form, FormStateInterface $form_state) {
    $element = parent::actionsElement($form, $form_state);
    $element['submit']['#value'] = $this->t('Enable');
    return $element;
  }

}
