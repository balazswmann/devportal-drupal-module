<?php

namespace Drupal\devportal_api_entities;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

class APITagListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = t('Label');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = Link::createFromRoute($entity->label(), 'entity.api_tag.canonical', ['api_tag' => $entity->id()]);
    return $row + parent::buildRow($entity);
  }

}
