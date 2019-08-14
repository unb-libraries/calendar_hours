<?php

namespace Drupal\calendar_hours_server\Entity;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Defines the Alert entity.
 *
 * @ingroup calendar_hours_server
 *
 * @ContentEntityType(
 *   id = "hours_calendar_alert",
 *   label = @Translation("Alert"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\calendar_hours_server\AlertListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "translation" = "Drupal\content_translation\ContentTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\Core\Entity\ContentEntityForm",
 *       "add" = "Drupal\Core\Entity\ContentEntityForm",
 *       "edit" = "Drupal\Core\Entity\ContentEntityForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "access" = "Drupal\Core\Entity\EntityAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "\Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "hours_calendar_alert",
 *   data_table = "alert_field_data",
 *   translatable = TRUE,
 *   admin_permission = "administer alert entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "title",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode",
 *   },
 *   links = {
 *     "add-form" = "/admin/config/services/hours/alerts/create",
 *     "edit-form" = "/admin/config/services/hours/alerts/{hours_calendar_alert}/edit",
 *     "delete-form" = "/admin/config/services/hours/alerts/{hours_calendar_alert}/delete",
 *     "collection" = "/admin/config/services/hours/alerts",
 *   }
 * )
 */
class Alert extends ContentEntityBase {

  use EntityChangedTrait;

  public function getTitle() {
    return $this->get('title')->value;
  }

  public function getMessage() {
    return $this->get('body')->value;
  }

  public function getCalendar() {
    return $this->get('calendar')->entity;
  }

  public function getVisibility() {
    $timezone = \Drupal::currentUser()->getTimeZone();
    $interval = $this->get('visible_interval')->first();
    return [
      'from' => new DrupalDateTime($interval->value, $timezone),
      'to' => new DrupalDateTime($interval->end_value, $timezone),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDescription(t('The title of the Alert.'))
      ->setRequired(TRUE)
      ->setSettings([
        'max_length' => 50,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('form', [
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', FALSE);

    $fields['body'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Alert Message'))
      ->setDescription(t('Content of the alert.'))
      ->setRequired(FALSE)
      ->setSettings([
        'max_length' => 255,
      ])
      ->setDisplayOptions('form', [
        'weight' => 1,
      ]);

    $fields['calendar'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Calendar'))
      ->setDescription(t('Calendar to which the alert applies, or none.'))
      ->setRequired(FALSE)
      ->setCardinality(1)
      ->setSetting('target_type', 'hours_calendar')
      ->setDisplayOptions('form', [
        'type' => 'options_select',
        'weight' => 2,
      ])
      ->setDisplayConfigurable('view', FALSE)
      ->setDisplayConfigurable('form', FALSE);

    $fields['visible_interval'] = BaseFieldDefinition::create('daterange')
      ->setLabel(t('Visibility'))
      ->setDescription(t('Time interval during which the alert will be visible.'))
      ->setDisplayOptions('form', [
        'weight' => 3,
      ]);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
