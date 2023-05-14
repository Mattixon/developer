<?php

/**
 * @file
 * Hooks and documentation related to Developer.
 */

/**
 * Act on entity_bundle_after_create().
 *
 * This hook is invoked after entity bundle, form & view display save.
 *
 * @param string $entity_type_id
 *   The type of $entity; e.g. 'developer_building' or 'developer_flat'.
 */
function hook_entity_bundle_after_create($entity_type_id) {
  // When a new bundle is created, modules can do actions after
  // form & view display is saved, on it's configuration.
}
