<?php

namespace Drupal\calendar_hours_client\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class HoursCalendarClientSettingsForm extends ConfigFormBase {

  public function getFormId() {
    return 'calendar_hours_client_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return [
      CALENDAR_HOURS__SETTINGS_CLIENT,
    ];
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(CALENDAR_HOURS__SETTINGS_CLIENT);

    $base_url = $config->get('base_url');
    $default_base_url = \Drupal::request()->getSchemeAndHttpHost() . '/api/hours/';
    $form['base_url'] = [
      '#type' => 'textfield',
      '#title' => 'Request URL',
      '#description' => 'Base URL of the REST Resource from which to request calendar hours.',
      '#default_value' => isset($base_url) ? $base_url : $default_base_url,
    ];

    $refresh_interval = $config->get('refresh_interval');
    $default_refresh_interval = 900000;
    $form['refresh_interval'] = [
      '#type' => 'number',
      '#title' => 'Refresh Interval',
      '#description' => 'Time between two hours request (in ms).',
      '#default_value' => isset($refresh_interval) ? $refresh_interval : $default_refresh_interval,
    ];

    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $base_url = $form_state->getValue('base_url');
    $refresh_interval = $form_state->getValue('refresh_interval');
    $this->configFactory->getEditable(CALENDAR_HOURS__SETTINGS_CLIENT)
      ->set('base_url', $base_url)
      ->set('refresh_interval', $refresh_interval)
      ->save();
    parent::submitForm($form, $form_state);
  }
}