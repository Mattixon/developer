<?php

namespace Drupal\developer_visualization;

use Drupal\developer\Entity\EntityInterface;

/**
 * Defines the interface for visualization service.
 */
interface VisualizationServiceInterface {

  /**
   * Get starting entity.
   */
  public function getStartingEntity(array $configuration): ?EntityInterface;

  /**
   * Get Developer Entity.
   */
  public function getDeveloperEntity(string $entity_name, string $entity_id): EntityInterface;

  /**
   * Get Developer parent Entity.
   */
  public function getParentDeveloperEntity(string $entity_type, string $entity_id): EntityInterface;

  /**
   * Get Developer Entity name.
   */
  public function getEntityName(EntityInterface $entity): string;

  /**
   * Get entity main image data.
   */
  public function getEntityMainImageData(EntityInterface $entity, string $main_image_style = NULL): array;

  /**
   * Get related entity svg data.
   */
  public function getRelatedEntitySvgData(EntityInterface $entity): array;

  /**
   * Get filter target opacity.
   */
  public function getTargetOpacity(int $target_opacity): string;

  /**
   * Get related entities.
   */
  public function getRelatedEntities(EntityInterface $developer_entity, string $related_entity_type): array;

  /**
   * Returns Entity legend.
   */
  public function getLegend(array $related_entities): array;

  /**
   * Convert normal svg path data by adding flat status.
   */
  public function convertToSvgPathsWithStatus(array $svg_paths_data, string $related_entity_type): array;

  /**
   * Get related entities tooltip data.
   */
  public function getRelatedEntitiesTooltipData(EntityInterface $developer_entity, string $related_entity_type): array;

  /**
   * Get Entity description.
   */
  public function getEntityDescription(EntityInterface $developer_entity): array;

  /**
   * Get block webform.
   */
  public function getWebformView(string $webform_id): array;

  /**
   * Get navigation options.
   */
  public function getNavigationOptions(EntityInterface $developer_entity, string $starting_entity_name, string $sell_entity_name): array;

  /**
   * Build block content.
   */
  public function buildContent(
    string $block_id,
    string $entity_name,
    string $entity_id,
    array $main_image_data,
    array $svg_paths_data,
    string $fill,
    string $target_opacity,
    string $starting_entity_type,
    string $sell_building,
    string $webform_id,
  ): array;

}
