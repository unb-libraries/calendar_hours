<?php

namespace Drupal\calendar_hours_server;

use Drupal\calendar_hours_server\Plugin\CalendarApiPluginManager;
use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\calendar_hours_server\Entity\HoursCalendar;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the class to build a listing of HoursCalendar entities.
 *
 * @package Drupal\calendar_hours_server
 */
class HoursCalendarListBuilder extends ConfigEntityListBuilder {

  protected $apiManager;

  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, CalendarApiPluginManager $api_manager) {
    $this->apiManager = $api_manager;
    parent::__construct($entity_type, $storage);
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('ID');
    $header['title'] = $this->t('Title');
    $header['vendor'] = $this->t('Vendor');
    $header['foreign_id'] = $this->t('Foreign ID');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    if ($entity instanceof HoursCalendar) {
      $row['id'] = isset($entity->id) ? $entity->id : '';
      $row['title'] = $entity->title;
      $row['vendor'] = $entity->vendor;
      $row['foreign_id'] = $entity->foreign_id;
      return $row + parent::buildRow($entity);
    }
    return parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function load() {
    $entities = parent::load();
    $api_definitions = $this->apiManager->getDefinitions();
    foreach ($api_definitions as $api_definition) {
      $api = $this->apiManager->createInstanceForVendor($api_definition['vendor']);
      try {
        $calendar_ids = $api->getForeignIds();
      } catch (\Exception $e) {
        drupal_set_message(sprintf(
          'An error occurred while fetching calendars from %s. This list may be incomplete.',
          ucfirst($api_definition['vendor'])
        ), 'warning');
        return $entities;
      }
      foreach ($calendar_ids as $foreign_id => $title) {
        if (empty($this->getStorage()->loadByProperties(['foreign_id' => $foreign_id]))) {
          /** @var HoursCalendar $hours_calendar */
          $hours_calendar = $this->getStorage()->create([
            'vendor' => $api_definition['vendor'],
            'foreign_id' => $foreign_id,
          ]);
          $entities[$hours_calendar->suggestId($hours_calendar->title)] = $hours_calendar;
        }
      }
    }
    return $entities;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    /** @var CalendarApiPluginManager $api_manager */
    $api_manager = $container->get('plugin.manager.calendar_hours.calendar_api');
    return new static(
      $entity_type,
      $container->get('entity.manager')->getStorage($entity_type->id()),
      $api_manager
    );
  }

  /**
   * Prevent adding custom operations if $entity is disabled. i.e. it only exists remotely.
   *
   * {@inheritdoc}
   */
  public function getOperations(EntityInterface $entity) {
    if (!$entity->status()) {
      return $this->getDefaultOperations($entity);
    }
    return parent::getOperations($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    if (!$entity->status() && $entity->hasLinkTemplate('enable')) {
      $operations['enable'] = [
        'title' => t('Enable'),
        'weight' => -10,
        'url' => $this->ensureDestination($entity->toUrl('enable')),
      ];
    }
    else {
      if ($entity->hasLinkTemplate('disable')) {
        $operations['disable'] = [
          'title' => t('Disable'),
          'weight' => 40,
          'url' => $this->ensureDestination($entity->toUrl('disable')),
        ];
      }
      if ($entity->hasLinkTemplate('edit-form')) {
        $operations['edit-form'] = [
          'title' => t('Edit'),
          'weight' => 80,
          'url' => $this->ensureDestination($entity->toUrl('edit-form')),
        ];
      }
      if ($entity->hasLinkTemplate('edit-hours-form')) {
        $operations['edit-hours-form'] = [
          'title' => t('Edit Hours'),
          'weight' => 80,
          'url' => $entity->toUrl('edit-hours-form'),
        ];
      }
    }

    return isset($operations) ? $operations : parent::getDefaultOperations($entity);
  }
}
