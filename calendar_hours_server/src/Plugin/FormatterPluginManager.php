<?php

namespace Drupal\calendar_hours_server\Plugin;

use Drupal\calendar_hours_server\Response\Block;
use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Provides a ParamProcessor plugin manager.
 *
 * @package Drupal\calendar_hours_server\Plugin
 */
class FormatterPluginManager extends DefaultPluginManager {

  /**
   * List of already created formatters.
   *
   * @var FormatterInterface[]
   */
  protected $formatters = [];

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
      'Plugin/calendar_hours/formatter',
      $namespaces,
      $module_handler,
      'Drupal\calendar_hours_server\Plugin\FormatterInterface',
      'Drupal\calendar_hours_server\Annotation\Formatter');
    $this->alterInfo('hours_calendar_formatter_info');
    $this->setCacheBackend($cache_backend, 'hours_calendar_formatter_plugins');
  }

  /**
   * {@inheritdoc}
   */
  public function createInstance($plugin_id, array $configuration = []) {
    foreach ($this->getDefinition($plugin_id)['execute_after'] as $dependency_id) {
      $configuration['execute_after'][] = $this->createInstance($dependency_id);
    }
    return parent::createInstance($plugin_id, $configuration);
  }

  /**
   * @param Block[] $blocks
   *   List of blocks to be formatted.
   * @param $params
   *   Accepted keys are formatter plugin IDs, without the leading 'format_'.
   *   Accepted values are the names of the corresponding plugin's methods starting with 'value', but without such prefix.
   *
   * @return Block[]
   *   The formatted list of blocks.
   *
   * @throws PluginException
   */
  public function format($blocks, $params) {
    $formatters = [];
    foreach ($params as $key => $value) {
      $formatters['format_' . $key] = [
        'value' => $value,
        'has_run' => FALSE,
      ];
    }
    return $this->execute($formatters, $blocks);
  }

  /**
   * @param $formatters
   *   Map of formatter_ids and runtime info, e.g.
   *     format_groupby => [
   *       value => 'start-date',
   *       has_run => FALSE|TRUE
   *     ]
   * @param $blocks
   *   List of blocks to be formatted.
   *
   * @return Block[]
   *   Formatted list of blocks.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  protected function execute(&$formatters, $blocks) {
    foreach ($formatters as $formatter_id => $run_config) {
      $formatter = $this->getFormatter($formatter_id);
      if (!$run_config['has_run']) {
        $dependencies = array_intersect_key($formatters, $formatter->getDependencies());
        $blocks = $this->execute($dependencies, $blocks);
        try {
          $blocks = $formatter->format($blocks, $run_config['value']);
          $formatters[$formatter_id]['has_run'] = TRUE;
        }
        catch (PluginException $pe) {
          continue;
        }
      }
    }
    return $blocks;
  }

  /**
   * Retrieve an instance of the plugin with the corresponding ID.
   *
   * @param $formatter_id
   *   ID of the plugin.
   *
   * @return \Drupal\calendar_hours_server\Plugin\FormatterInterface
   *   The plugin instance.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function getFormatter($formatter_id) {
    if (!isset($this->formatters[$formatter_id])) {
      $this->formatters[$formatter_id] = $this->createInstance($formatter_id);
    }
    return $this->formatters[$formatter_id];
  }

}
