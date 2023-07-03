<?php

namespace Drupal\developer_visualization;

use Drupal\webform\WebformInterface;
use Drupal\developer\Entity\Flat\Flat;
use Drupal\developer\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;

/**
 * Visualization service.
 */
class VisualizationService implements VisualizationServiceInterface {
  use StringTranslationTrait;

  /**
   * Use to map related entities.
   */
  public const RELATED_ENTITY_MAP = [
    'developer_estate' => 'developer_building',
    'developer_building' => 'developer_floor',
    'developer_floor' => 'developer_flat',
  ];

  /**
   * Use to map parent related entities.
   */
  public const REVERSE_RELATED_ENTITY_MAP = [
    'developer_building' => 'developer_estate',
    'developer_floor' => 'developer_building',
    'developer_flat' => 'developer_floor',
  ];

  /**
   * Contains all flat statuses.
   */
  public const FLAT_STATUSES = [
    1 => 'Available',
    2 => 'Reserved',
    3 => 'Sold',
  ];

  /**
   * Contains statuses related fill.
   */
  public const FLAT_STATUSES_FILL = [
    1 => 'green',
    2 => 'orange',
    3 => 'red',
  ];

  /**
   * Constructs a CoordinatesFieldService object.
   */
  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected EntityDisplayRepositoryInterface $entityDisplayRepository,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function getStartingEntity(array $configuration): ?EntityInterface {
    if ($configuration['start_from_building'] === 0) {
      $entity_type = 'developer_estate';
      $id = $configuration['starting_estate'];
    }
    else {
      $entity_type = 'developer_building';
      $id = $configuration['starting_building'];
    }

    /** @var \Drupal\developer\Entity\EntityInterface */
    $starting_entity = $this->entityTypeManager->getStorage($entity_type)->load($id);

    return $starting_entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getDeveloperEntity(string $entity_name, string $entity_id): EntityInterface {
    /** @var \Drupal\developer\Entity\EntityInterface */
    $developer_entity = $this->entityTypeManager->getStorage('developer_' . $entity_name)->load($entity_id);

    return $developer_entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getParentDeveloperEntity(string $entity_name, string $entity_id): EntityInterface {
    $entity_type = 'developer_' . $entity_name;
    /** @var \Drupal\developer\Entity\EntityInterface */
    $developer_entity = $this->entityTypeManager
      ->getStorage($entity_type)
      ->load($entity_id);

    $parent_developer_entity_type = self::REVERSE_RELATED_ENTITY_MAP[$entity_type];
    $parent_entity_field_name = substr($parent_developer_entity_type, strpos($parent_developer_entity_type, "_") + 1) . '_id';
    $parent_id = $developer_entity->$parent_entity_field_name->target_id;

    /** @var \Drupal\developer\Entity\EntityInterface */
    $parent_developer_entity = $this->entityTypeManager
      ->getStorage($parent_developer_entity_type)
      ->load($parent_id);

    return $parent_developer_entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityName(EntityInterface $entity): string {
    $entity_name = substr($entity->getEntityTypeId(), strpos($entity->getEntityTypeId(), "_") + 1);

    return $entity_name;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityMainImageData(EntityInterface $entity, string $main_image_style = NULL): array {
    $media_image = $entity->main_image->entity;

    if (is_null($media_image)) {
      return [];
    }

    $field_image = $media_image->field_developer_image;
    /** @var array */
    $image_properties = $field_image->getValue();

    /* Apply optional main image style */
    if ($main_image_style) {
      /** @var \Drupal\image\ImageStyleInterface|null */
      $image_style = $this->entityTypeManager->getStorage('image_style')->load($main_image_style);

      if (!empty($image_style)) {
        /** @var \Drupal\file\Entity\File */
        $file = $this->entityTypeManager->getStorage('file')->load($image_properties[0]['target_id']);
        $image_uri = $file->getFileUri();
        $styled_image_url = $image_style->buildUrl($image_uri);
        $image_style->createDerivative($image_uri, $styled_image_url);
        $main_image_url = $styled_image_url;
      }
      else {
        /** @var \Drupal\file\Entity\File */
        $file = $field_image->entity;
        $main_image_url = $file->createFileUrl();
      }
    }
    else {
      /** @var \Drupal\file\Entity\File */
      $file = $field_image->entity;
      $main_image_url = $file->createFileUrl();
    }

    return [
      'url' => $main_image_url,
      'width' => $image_properties[0]['width'],
      'height' => $image_properties[0]['height'],
      'style' => $main_image_style,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getRelatedEntitySvgData(EntityInterface $entity): array {
    if ($entity instanceof Flat) {
      return [];
    }

    $entity_type_id = $entity->getEntityTypeId();
    $related_entity_type_id = self::RELATED_ENTITY_MAP[$entity_type_id];
    $entity_name = substr($entity_type_id, strpos($entity_type_id, "_") + 1);
    $paths = [];

    $related_entities = $this->entityTypeManager
      ->getStorage($related_entity_type_id)
      ->loadByProperties([
        $entity_name . '_id' => $entity->id(),
      ]);

    if (!empty($related_entities)) {
      /** @var \Drupal\developer\Entity\EntityInterface $entity */
      foreach ($related_entities as $entity) {
        $paths[$entity->id()] = $entity->coordinates->value;
      }
    }

    return ['paths' => $paths];
  }

  /**
   * {@inheritdoc}
   */
  public function getTargetOpacity(int $target_opacity): string {
    $filtered_target_opacity = '0.3';

    switch ($target_opacity) {
      case 0:
        $filtered_target_opacity = '0';
        break;

      case 100:
        $filtered_target_opacity = '1';
        break;

      default:
        $filtered_target_opacity = '0.' . $target_opacity;
    }

    return $filtered_target_opacity;
  }

  /**
   * {@inheritdoc}
   */
  public function getRelatedFloorFlats(EntityInterface $floor_entity): array {
    $floor_id = $floor_entity->id();
    $related_floor_flats = $this->entityTypeManager->getStorage('developer_flat')->loadByProperties(['floor_id' => $floor_id]);

    return $related_floor_flats;
  }

  /**
   * {@inheritdoc}
   */
  public function getFloorLegend(array $related_floor_flats): array {
    $floor_legend = [];

    if (!empty($related_floor_flats)) {
      $floor_legend = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['floor_legend'],
        ],
        'statuses' => [],
      ];

      foreach ($related_floor_flats as $flat) {
        $flat_status = $flat->status->value;
        /** @var string */
        $flat_status_name = self::FLAT_STATUSES[$flat_status];
        // @codingStandardsIgnoreStart
        $floor_legend['statuses']['status_' . $flat_status] = [
          '#type' => 'html_tag',
          '#tag' => 'span',
          '#attributes' => [
            'class' => [strtolower(self::FLAT_STATUSES[$flat_status])],
          ],
          '#value' => $this->t($flat_status_name),
        ];
        // @codingStandardsIgnoreEnd
      }
    }

    return $floor_legend;
  }

  /**
   * {@inheritdoc}
   */
  public function convertToFloorRelatedSvgPaths(array $svg_paths_data): array {
    $floor_svg_paths_data = [];

    foreach ($svg_paths_data as $flat_id => $path) {
      $flat = $this->entityTypeManager->getStorage('developer_flat')->load($flat_id);
      $floor_svg_paths_data[$flat_id]['coordinates'] = $path;
      $floor_svg_paths_data[$flat_id]['status'] = strtolower(self::FLAT_STATUSES[$flat->status->value]);
      $floor_svg_paths_data[$flat_id]['fill'] = self::FLAT_STATUSES_FILL[$flat->status->value];
    }

    return $floor_svg_paths_data;
  }

  /**
   * {@inheritdoc}
   */
  public function getRelatedFlatsTooltipData(EntityInterface $floor_entity): array {
    $related_floor_flats = $this->getRelatedFloorFlats($floor_entity);
    $view_builder = $this->entityTypeManager->getViewBuilder('developer_flat');
    $flats_tooltip_data = [];

    foreach ($related_floor_flats as $flat_id => $flat) {
      $flat_status = $flat->status->value;
      $flat_view_modes = $this->entityDisplayRepository->getViewModeOptionsByBundle('developer_flat', $flat->type->target_id);

      if ($flat_status !== '3' && array_key_exists('tooltip', $flat_view_modes)) {
        $flat_view = $view_builder->view($flat, 'tooltip');
        $flats_tooltip_data[$flat_id] = $flat_view;
      }
    }

    return $flats_tooltip_data;
  }

  /**
   * {@inheritdoc}
   */
  public function getFlatDescription(EntityInterface $flat_entity): array {
    $flat_entity_view_modes = $this->entityDisplayRepository->getViewModeOptionsByBundle('developer_flat', $flat_entity->type->target_id);
    $flat_view = [];

    if (array_key_exists('description', $flat_entity_view_modes)) {
      $view_builder = $this->entityTypeManager->getViewBuilder('developer_flat');
      $flat_view = $view_builder->view($flat_entity, 'description');
    }

    return $flat_view;
  }

  /**
   * {@inheritdoc}
   */
  public function getWebformView(string $webform_id): array {
    $view_builder = $this->entityTypeManager->getViewBuilder('webform');
    $webform = $this->entityTypeManager->getStorage('webform')->load($webform_id);
    $webform_view = $webform instanceof WebformInterface ? $view_builder->view($webform) : [];
    return $webform_view;
  }

  /**
   * {@inheritdoc}
   */
  public function buildContent(
    string $block_id,
    string $entity_name,
    string $entity_id,
    array $main_image_data,
    array $svg_paths_data,
    string $path_fill,
    string $path_target_opacity,
    string $starting_entity_name,
    string $sell_entity_name,
    string $webform_id,
  ): array {

    /* Prepare related entity name */
    $related_entity_name = '';

    if ($entity_name !== 'flat') {
      /** @var string|null */
      $related_entity_name = self::RELATED_ENTITY_MAP['developer_' . $entity_name];
    }

    /* Simplify variable */
    if (!empty($svg_paths_data)) {
      $svg_paths_data = $svg_paths_data['paths'];
    }

    /* Clear svg paths if building is for sell */
    if ($entity_name === 'floor' && $sell_entity_name === 'building') {
      $svg_paths_data = [];
    }

    /* Prepare guide */
    $guide = [];

    if (!empty($related_entity_name)) {
      if ($sell_entity_name === 'building' && $entity_name === 'floor') {
        $guide = [];
      }
      else {
        // @codingStandardsIgnoreStart
        $filtered_related_entity_name = $this->t(substr($related_entity_name, strpos($related_entity_name, "_") + 1));
        // @codingStandardsIgnoreEnd
        $guide = [
          '#type' => 'html_tag',
          '#attributes' => [
            'class' => ['description'],
          ],
          '#tag' => 'span',
          '#value' => $this->t('Select @entity by hovering over it with the cursor', ['@entity' => $filtered_related_entity_name]),
        ];
      }
    }

    /* Prepare back button */
    $back_btn = [];

    if ($entity_name !== $starting_entity_name) {
      $back_btn = [
        '#type' => 'html_tag',
        '#attributes' => [
          'class' => ['back-btn', 'developer-visualization-btn'],
        ],
        '#tag' => 'span',
        '#value' => $this->t('Back'),
      ];
    }

    /* Prepare ask for offer button */
    $ask_for_offer_btn = [];

    if ($sell_entity_name === 'building' && $entity_name == 'building') {
      $ask_for_offer_btn = [
        '#type' => 'link',
        '#title' => $this->t('Ask for offer'),
        '#url' => Url::fromRoute('entity.webform.canonical', ['webform' => $webform_id]),
        '#attributes' => [
          'class' => [
            'ask-for-offer-btn',
            'developer-visualization-btn',
            'use-ajax',
          ],
          'data-dialog-options' => '{"height":"80%","width":"90%","max-height":"650","max-width":"750"}',
          'data-dialog-type' => 'modal',
        ],
      ];

      if ($starting_entity_name === 'estate') {
        $ask_for_offer_btn['#attributes']['class'][] = 'bottom-right';
      }
    }

    /* Prepare floor view */
    $floor_legend = [];
    $flats_tooltip_data = [];

    if ($entity_name === 'floor') {
      $floor_entity = $this->getDeveloperEntity($entity_name, $entity_id);
      $related_floor_flats = $this->getRelatedFloorFlats($floor_entity);
      $floor_legend = $this->getFloorLegend($related_floor_flats);
      $svg_paths_data = $this->convertToFloorRelatedSvgPaths($svg_paths_data);
      $flats_tooltip_data = $this->getRelatedFlatsTooltipData($floor_entity);
    }

    /* Prepare flat view */
    $flat_description = [];
    $webform = [];

    if ($entity_name === 'flat') {
      $flat_entity = $this->getDeveloperEntity($entity_name, $entity_id);
      $flat_description = $this->getFlatDescription($flat_entity);
      $webform = $this->getWebformView($webform_id);
    }

    /* Prepare block content */
    $block = [
      '#theme' => 'visualization_presentation',
      '#attached' => [
        'library' => ['developer_visualization/global'],
      ],
      '#block_id' => $block_id,
      '#flat_description' => $flat_description,
      '#guide' => $guide,
      '#image' => [
        'width' => $main_image_data['width'],
        'height' => $main_image_data['height'],
        'url' => $main_image_data['url'],
        'entity_name' => $entity_name,
        'entity_id' => $entity_id,
        'path_fill' => $path_fill,
        'path_target_opacity' => $path_target_opacity,
        'starting_entity_name' => $starting_entity_name,
        'sell_entity_name' => $sell_entity_name,
        'webform_id' => $webform_id,
        'style' => $main_image_data['style'],
      ],
      '#paths' => $svg_paths_data,
      '#back_btn' => $back_btn,
      '#ask_for_offer_btn' => $ask_for_offer_btn,
      '#floor_legend' => $floor_legend,
      '#flats_tooltip_data' => $flats_tooltip_data,
      '#webform' => $webform,
      '#front_url' => Url::fromRoute('<front>'),
    ];

    return $block;
  }

}
