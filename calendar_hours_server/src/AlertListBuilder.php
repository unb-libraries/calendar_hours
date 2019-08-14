<?php

namespace Drupal\calendar_hours_server;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Alert entities.
 *
 * @ingroup calendar_hours_server
 */
class AlertListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['title'] = $this->t('Title');
    $header['unit'] = $this->t('Unit');
    $header['visibility'] = $this->t('Visibility');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\calendar_hours_server\Entity\Alert */
    $row['title'] = $entity->label();

    $calendar = $entity->getCalendar();
    $row['unit'] = isset($calendar) ? $calendar->title : 'Global';
    $row['visibility'] = sprintf('%s - %s', $entity->getVisibility()['from']->format('Y-m-d h:i'), $entity->getVisibility()['to']->format('Y-m-d h:i'));
    return $row + parent::buildRow($entity);
  }

}
