<?php

namespace Drupal\developer\Service;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * The entity service.
 */
class EntityService {

  /**
   * Use to map related entities.
   */
  public const RELATED_ENTITY_MAP = [
    'developer_estate' => 'developer_building',
    'developer_building' => 'developer_floor',
    'developer_floor' => 'developer_flat',
    'developer_estate_type' => 'developer_estate',
    'developer_building_type' => 'developer_building',
    'developer_floor_type' => 'developer_floor',
    'developer_flat_type' => 'developer_flat',
  ];

  /**
   * Constructs a EntityService object.
   */
  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected RouteMatchInterface $currentRouteMatch
  ) {}

  /**
   * Get information of related entities.
   */
  public function relatedEntitiesInfo(EntityInterface $entity): array {
    $entity_type_id = $entity->getEntityType()->id();
    $entity_type_split_id = explode("_", $entity_type_id);
    $entity_type_short_name = $entity_type_split_id[1];
    $field_name = $entity_type_short_name . '_id';
    $condition_value = $entity->id();

    if (count($entity_type_split_id) === 3) {
      $field_name = 'type';
    }

    /** @var array */
    $ids = $this->entityTypeManager->getStorage(self::RELATED_ENTITY_MAP[$entity_type_id])
      ->getQuery()
      ->condition($field_name, $condition_value)
      ->execute();

    $related = explode("_", self::RELATED_ENTITY_MAP[$entity_type_id])[1];
    if (count($ids) > 1) {
      $related .= 's';
    }

    $entity_info = [
      'type' => ucfirst($entity_type_short_name),
      'ids_number' => count($ids),
      'related' => $related,
    ];

    return $entity_info;
  }

  /**
   * Get current entity key word.
   */
  public function getEntityKeyWord(): string {
    /** @var string */
    $current_route_name = $this->currentRouteMatch->getRouteName();
    $entity_type_id = explode('.', $current_route_name)[1];
    $entity_key_word = explode('_', $entity_type_id)[1];

    return $entity_key_word;
  }

}
