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
   * Get related flats of given floor.
   */
  public function getRelatedFloorFlats(EntityInterface $floor_entity): array;

  /**
   * Returns Floor Entity legend.
   */
  public function getFloorLegend(array $related_floor_flats): array;

  /**
   * Convert normal svg path data by adding flat status.
   */
  public function convertToFloorRelatedSvgPaths(array $svg_paths_data): array;

  /**
   * Get Floor related flats tooltip data.
   */
  public function getRelatedFlatsTooltipData(EntityInterface $floor_entity): array;

  /**
   * Get Flat description.
   */
  public function getFlatDescription(EntityInterface $flat_entity): array;

  /**
   * Get block webform.
   */
  public function getWebformView(string $webform_id): array;

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
