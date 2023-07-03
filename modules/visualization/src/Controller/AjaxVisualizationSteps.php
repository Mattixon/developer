<?php

namespace Drupal\developer_visualization\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\developer_visualization\VisualizationService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides endpoint with ajax visualization steps.
 */
class AjaxVisualizationSteps extends ControllerBase {

  /**
   * Use to map related entities.
   */
  public const RELATED_ENTITY_NAME_MAP = [
    'estate' => 'building',
    'building' => 'floor',
    'floor' => 'flat',
  ];

  /**
   * Use to map parent related entities.
   */
  public const REVERSE_RELATED_ENTITY_NAME_MAP = [
    'building' => 'estate',
    'floor' => 'building',
    'flat' => 'floor',
  ];

  /**
   * Constructs a AjaxVisualizationSteps object.
   */
  public function __construct(protected VisualizationService $visualizationService) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('developer_visualization.visualization_service'),
    );
  }

  /**
   * Ajax function for visualization block activate on next step.
   */
  public function nextStep(
    string $block_id,
    string $entity_name,
    string $entity_id,
    string $path_fill,
    string $path_target_opacity,
    string $starting_entity_name,
    string $sell_entity_name,
    string $webform_id,
    string $main_image_style = NULL
  ): AjaxResponse {

    /* Prepare block data */
    $related_entity_name = self::RELATED_ENTITY_NAME_MAP[$entity_name];
    $developer_entity = $this->visualizationService->getDeveloperEntity($related_entity_name, $entity_id);
    $main_image_data = $this->visualizationService->getEntityMainImageData($developer_entity, $main_image_style);
    $svg_paths_data = $this->visualizationService->getRelatedEntitySvgData($developer_entity);

    /* Build block */
    $block = $this->visualizationService->buildContent(
      $block_id,
      $related_entity_name,
      $entity_id,
      $main_image_data,
      $svg_paths_data,
      $path_fill,
      $path_target_opacity,
      $starting_entity_name,
      $sell_entity_name,
      $webform_id
    );

    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand('#visualization-svg-container--' . $block_id, $block));

    return $response;
  }

  /**
   * Ajax function for visualization block activate on prev step.
   */
  public function prevStep(
    string $block_id,
    string $entity_name,
    string $entity_id,
    string $path_fill,
    string $path_target_opacity,
    string $starting_entity_name,
    string $sell_entity_name,
    string $webform_id,
    string $main_image_style = NULL
  ): AjaxResponse {

    /* Prepare block data */
    $parent_developer_entity = $this->visualizationService->getParentDeveloperEntity($entity_name, $entity_id);
    $entity_id = (string) $parent_developer_entity->id();
    $main_image_data = $this->visualizationService->getEntityMainImageData($parent_developer_entity, $main_image_style);
    $svg_paths_data = $this->visualizationService->getRelatedEntitySvgData($parent_developer_entity);

    /* Build block */
    $block = $this->visualizationService->buildContent(
      $block_id,
      self::REVERSE_RELATED_ENTITY_NAME_MAP[$entity_name],
      $entity_id,
      $main_image_data,
      $svg_paths_data,
      $path_fill,
      $path_target_opacity,
      $starting_entity_name,
      $sell_entity_name,
      $webform_id
    );

    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand('#visualization-svg-container--' . $block_id, $block));

    return $response;
  }

  /**
   * Ajax function for visualization block activate on navigation.
   */
  public function changeStep(
    string $block_id,
    string $entity_name,
    string $entity_id,
    string $path_fill,
    string $path_target_opacity,
    string $starting_entity_name,
    string $sell_entity_name,
    string $webform_id,
    string $main_image_style = NULL
  ): AjaxResponse {

    /* Prepare block data */
    $developer_entity = $this->visualizationService->getDeveloperEntity($entity_name, $entity_id);
    $entity_id = (string) $developer_entity->id();
    $main_image_data = $this->visualizationService->getEntityMainImageData($developer_entity, $main_image_style);
    $svg_paths_data = $this->visualizationService->getRelatedEntitySvgData($developer_entity);

    /* Build block */
    $block = $this->visualizationService->buildContent(
      $block_id,
      $entity_name,
      $entity_id,
      $main_image_data,
      $svg_paths_data,
      $path_fill,
      $path_target_opacity,
      $starting_entity_name,
      $sell_entity_name,
      $webform_id
    );

    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand('#visualization-svg-container--' . $block_id, $block));

    return $response;
  }

}
