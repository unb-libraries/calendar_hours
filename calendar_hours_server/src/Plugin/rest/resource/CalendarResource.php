<?php

namespace Drupal\calendar_hours_server\Plugin\rest\resource;

use Drupal\calendar_hours_server\Entity\HoursCalendar;
use Drupal\calendar_hours_server\Entity\HoursCalendarStorage;
use Drupal\calendar_hours_server\Plugin\FormatterPluginManager;
use Drupal\calendar_hours_server\Response\Block;
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
  public function __construct(HoursCalendarStorage $hours_calendar_storage, array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger) {
    $this->calendarStorage = $hours_calendar_storage;
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
      $blocks = $calendar->getHours($params['from'], $params['to']);
      usort($blocks, [$this, 'sortAsc']);

      $hours = [];
      foreach ($blocks as $block) {
        $hours[$block->startDate()][] = [
          'from' => $block->getStart()->format('c'),
          'to' => $block->getEnd()->format('c'),
        ];
      }

      $date = DrupalDateTime::createFromFormat('Y-m-d', $params['from']);
      while (($formatted_date = $date->format('Y-m-d')) !== $params['to']) {
        if (!array_key_exists($formatted_date, $hours)) {
          $hours[$formatted_date] = [];
        }
        $date->add(\DateInterval::createFromDateString('1 day'));
      }

      if (!array_key_exists($params['to'], $hours)) {
        $hours[$params['to']] = [];
      }

      $opens_at = $calendar->getOpensAt();
      $closes_at = $calendar->getClosesAt();

      $response = new ResourceResponse([
        'id' => $calendar->id,
        'title' => $calendar->title,
        'startDate' => $params['from'],
        'endDate' => $params['to'],
        'hours' => $hours,
        'reopensAt' => $opens_at ? $opens_at->format('c') : '',
        'closesAt' => $closes_at ? $closes_at->format('c') : '',
        'lastRefreshed' => (new DrupalDateTime('now', $calendar->getTimezone()))->format('c'),
      ], 200);

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
    }
    catch (\Exception $e) {
      $this->logger->error($e->getMessage());
      $response = new ResourceResponse([
        'error' => $e->getCode(),
        'message' => $e->getMessage(),
      ], 500);
    }

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

  /**
   * Default request parameters.
   */
  protected function defaultParams() {
    return [
      'from' => (new DrupalDateTime())->format('Y-m-d'),
      'to' => (new DrupalDateTime())->format('Y-m-d'),
    ];
  }

  /**
   * Sorts two Blocks based on their "from" or, if equal, their "to" timestamps.
   *
   * @param \Drupal\calendar_hours_server\Response\Block $a
   *   The Block in question.
   * @param \Drupal\calendar_hours_server\Response\Block $b
   *   The Block to compare against.
   *
   * @return int
   *   < 0: $a starts or, if equal, finishes before $b
   *   > 0: $a starts or, if equal, finishes after $b
   *   = 0: $a starts and finishes at the same time as $b
   */
  protected function sortAsc(Block $a, Block $b) {
    if (($diff_from = $a->startsBefore($b->getStart())) != 0) {
      return $diff_from;
    }
    if (($diff_to = $a->endsBefore($b->getEnd())) != 0) {
      return $diff_to;
    }
    return 0;
  }

}
