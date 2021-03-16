<?php

namespace Drupal\calendar_hours_server\Entity;

use Drupal\calendar_hours_server\Plugin\CalendarApiPluginManager;
use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Cache\MemoryCache\MemoryCacheInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\Entity\ConfigEntityStorage;
use Drupal\Core\Entity\EntityMalformedException;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Storage handler for "hours_calendar" config entities.
 *
 * @package Drupal\calendar_hours_server\Entity
 */
class HoursCalendarStorage extends ConfigEntityStorage implements HoursCalendarStorageInterface {

  use MessengerTrait;
  use StringTranslationTrait;

  protected $apiManager;

  public function __construct(EntityTypeInterface $entity_type, ConfigFactoryInterface $config_factory, CalendarApiPluginManager $api_manager, UuidInterface $uuid_service, LanguageManagerInterface $language_manager, MemoryCacheInterface $memory_cache = NULL) {
    $this->apiManager = $api_manager;
    parent::__construct($entity_type, $config_factory, $uuid_service, $language_manager, $memory_cache);
  }

  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('config.factory'),
      $container->get('plugin.manager.calendar_hours.calendar_api'),
      $container->get('uuid'),
      $container->get('language_manager'),
      $container->get('entity.memory_cache')
    );
  }

  public function loadMultiple(array $ids = NULL) {
    $hours_calendars = parent::loadMultiple($ids);
    /** @var HoursCalendar $hours_calendar */
    foreach ($hours_calendars as $hours_calendar) {
      $api = $this->apiManager->createInstanceForVendor($hours_calendar->vendor);
      $hours_calendar->setCalendarApi($api);
    }
    return $hours_calendars;
  }

  /**
   * {@inheritdoc}
   */
  public function create(array $values = []) {
    if (!array_key_exists('vendor', $values)) {
      throw new EntityMalformedException("HoursCalendar cannot be created without specifying a vendor.");
    }
    if (!array_key_exists('foreign_id', $values)) {
      throw new EntityMalformedException("HoursCalendar cannot be created without specifying a foreign ID.");
    }

    /** @var HoursCalendar $hours_calendar */
    $hours_calendar = parent::create($values);
    try {
      $api = $this->apiManager->createInstanceForVendor($values['vendor']);
      $hours_calendar->setCalendarApi($api);
    }
    catch (PluginException $e) {
      echo $e->getMessage();
    }

    if ($hours_calendar->isNew() && empty($hours_calendar->title)) {
      try {
        $hours_calendar->title = $api->getCalendarTitle($values['foreign_id']);
      }
      catch (\Exception $e) {
        $this->messenger()
          ->addWarning($this->t('Could not retrieve calendar title from @vendor.', [
            '@vendor' => ucfirst($values['vendor']),
          ]));
        $hours_calendar->title = 'Unknown';
      }
    }

    return $hours_calendar;
  }
}