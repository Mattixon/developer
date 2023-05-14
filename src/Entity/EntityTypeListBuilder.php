<?php

namespace Drupal\developer\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Config\Entity\ConfigEntityListBuilder;

/**
 * Provides a listing of Developer Estate types.
 */
class EntityTypeListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['name'] = $this->t('Type');
    $header['description'] = $this->t('Description');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\developer\Entity\EntityTypeInterface $entity */
    $row['name'] = $entity->label();
    $row['description'] = $entity->getDescription();

    return $row + parent::buildRow($entity);
  }

}
