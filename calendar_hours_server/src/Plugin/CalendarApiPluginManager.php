<?php

namespace Drupal\calendar_hours_server\Plugin;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Provides a CalendarApi plugin manager.
 *
 * @package Drupal\calendar_hours_server\Plugin
 */
class CalendarApiPluginManager extends DefaultPluginManager {

  /**
   * Constructs a ParamProcessorPluginManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/calendar_hours/api',
      $namespaces,
      $module_handler,
      'Drupal\calendar_hours_server\Plugin\CalendarApiInterface',
      'Drupal\calendar_hours_server\Annotation\CalendarApi');
    $this->alterInfo('hours_calendar_api_info');
    $this->setCacheBackend($cache_backend, 'hours_calendar_api_plugins');
  }

  /**
   * Create a plugin instance that suits a given HoursCalendar's provider.
   *
   * @param string $vendor_name
   *   Calendar to create for which to create a suitable CalendarAPI plugin.
   *
   * @return CalendarApiInterface
   *   Instance of CalendarAPI plugin.
   *
   * @throws PluginException
   */
  public function createInstanceForVendor($vendor_name) {
    $plugin_id = $vendor_name . '_calendar_api';
    /** @var CalendarApiInterface $plugin */
    $plugin = $this->createInstance($plugin_id);
    $plugin_definition = $this->getDefinition($plugin_id);
    foreach ($plugin_definition['services'] as $key => $service_id) {
      $plugin->injectService($key, $service_id);
    }
    return $plugin;
  }



}
