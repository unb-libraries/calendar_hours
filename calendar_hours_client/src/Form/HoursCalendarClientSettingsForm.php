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
    $default_refresh_interval = 60;
    $form['refresh_interval'] = [
      '#type' => 'number',
      '#title' => 'Refresh Interval',
      '#description' => 'Time interval between refreshing an hours view (in seconds).',
      '#default_value' => isset($refresh_interval) ? $refresh_interval / 1000 : $default_refresh_interval,
    ];

    $max_age = $config->get('max_age');
    $default_max_age = 900;
    $form['max_age'] = [
      '#type' => 'number',
      '#title' => 'Max-Age',
      '#description' => 'Maximum age (in seconds) stored hours may reach before being synced with remote source.',
      '#default_value' => isset($max_age) ? $max_age : $default_max_age,
    ];

    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $base_url = $form_state->getValue('base_url');
    $refresh_interval = $form_state->getValue('refresh_interval');
    $max_age = $form_state->getValue('max_age');
    $this->configFactory->getEditable(CALENDAR_HOURS__SETTINGS_CLIENT)
      ->set('base_url', $base_url)
      ->set('refresh_interval', $refresh_interval * 1000)
      ->set('max_age', $max_age)
      ->save();
    parent::submitForm($form, $form_state);
  }
}