<?php

namespace Drupal\calendar_hours_server\Plugin\rest\resource;

use Drupal\calendar_hours_server\Entity\HoursCalendar;
use Drupal\calendar_hours_server\Entity\HoursCalendarStorage;
use Drupal\calendar_hours_server\Plugin\FormatterPluginManager;
use Drupal\calendar_hours_server\Response\HoursResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\Entity\ConfigEntityStorage;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides access to query calendar based hours via REST.
 *
 * @RestResource(
 *   id = "calendar_hours",
 *   label = @Translation("Calendar Hours"),
 *   uri_paths = {
 *     "canonical" = "/api/hours/{calendar_id}",
 *   }
 * )
 */
class CalendarResource extends ResourceBase {

  /**
   * The Storage manager for HoursCalendar entities.
   *
   * @var \Drupal\calendar_hours_server\Entity\HoursCalendarStorage
   */
  protected $calendarStorage;


  /**
   * @var \Drupal\calendar_hours_server\Plugin\FormatterPluginManager
   */
  protected $formatManager;

  /**
   * GoogleCalendarApiController constructor.
   *
   * @param \Drupal\calendar_hours_server\Entity\HoursCalendarStorage $hours_calendar_storage
   *   The Storage manager for HoursCalendar entities.
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   */
  public function __construct(HoursCalendarStorage $hours_calendar_storage, FormatterPluginManager $format_manager, array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger) {
    $this->calendarStorage = $hours_calendar_storage;
    $this->formatManager = $format_manager;
    parent::__construct(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $serializer_formats,
      $logger
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('entity_type.manager')->getStorage('hours_calendar'),
      $container->get('plugin.manager.calendar_hours.formatter'),
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('rest')
    );
  }

  /**
   * Responds to a GET request.
   *
   * @param string $calendar_id
   *   The ID of the requested Calendar.
   *
   * @return \Drupal\rest\ResourceResponse
   *   ResourceResponse instance containing the requested hours.
   */
  public function get($calendar_id) {
    // TODO: Make cache-age configurable. Maybe even via a request parameter!?

    /** @var HoursCalendar $calendar */
    $calendar = $this->calendarStorage->load($calendar_id);
    $params = $this->getParams();

    try {
      $hours = $calendar->getHours($params['from'], $params['to']);
      $opensNext = $calendar->getOpensAt();
      $closesNext = $calendar->getClosesAt();
      $status = 200;
    }
    catch (\Exception $e) {
      drupal_set_message('An error occurred while fetching hours. An empty result has been returned.');
      $hours = [];
      $status = 500;
    }

    $response = new HoursResponse(
      $calendar,
      [
        'blocks' => $hours,
        'opensAt' => isset($opensNext) ? $opensNext->format('c') : '',
        'closesAt' => isset($closesNext) ? $closesNext->format('c') : '',
      ],
      $params['format'],
      $status
    );

    $response->setMaxAge(900);
    $response->addCacheableDependency(CacheableMetadata::createFromRenderArray([
      '#cache' => [
        'contexts' => [
          'url.query_args'
        ],
        'tags' => [
          'calendar_hours_' . $calendar_id,
        ]
      ],
    ]));

    return $response;
  }

  /**
   * Request parameters. For accepted keys @see defaultParams()
   */
  protected function getParams() {
    /** @var Request $request */
    $request = \Drupal::service('request_stack')->getCurrentRequest();
    $params = $this->defaultParams();
    foreach ($request->query->all() as $key => $value) {
      if (array_key_exists($key, $params)) {
        if (method_exists($this, $key)) {
          $params[$key] = $this->$key($value, $params[$key]);
        }
        else {
          $params[$key] = $value;
        }
      }
    }
    return $params;
  }

  protected function format($sub_query, $default_value = []) {
    $format_params = $default_value;
    foreach (explode(',', $sub_query) as $key_value_string) {
      $key_value_pair = explode(':', $key_value_string);
      $format_params[$key_value_pair[0]] = $key_value_pair[1];
    }
    return $format_params;
  }

  /**
   * Default request parameters.
   */
  protected function defaultParams() {
    return [
      'from' => (new DrupalDateTime())->format('Y-m-d'),
      'to' => (new DrupalDateTime())->format('Y-m-d'),
      'format' => [],
    ];
  }

}
