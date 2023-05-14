<?php

namespace Drupal\developer\Entity;

use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;

/**
 * Provides an interface defining a Developer entities.
 */
interface EntityInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface, RevisionLogInterface, EntityPublishedInterface {}
