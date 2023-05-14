<?php

namespace Drupal\developer\Entity;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler as CoreEntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access controller for the Developer entities.
 */
class EntityAccessControlHandler extends CoreEntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account): AccessResult {
    $entity_key_word = explode("_", $entity->getEntityTypeId())[1];

    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view ' . $entity_key_word . ' entity');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit ' . $entity_key_word . ' entity');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete ' . $entity_key_word . ' entity');
    }
    return AccessResult::allowed();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL): AccessResult {
    $entity_key_word = explode("_", $context['entity_type_id'])[1];

    return AccessResult::allowedIfHasPermission($account, 'add ' . $entity_key_word . ' entity');
  }

}
