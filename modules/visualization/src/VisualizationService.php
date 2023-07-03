<?php

namespace Drupal\developer_visualization;

use Drupal\Core\Url;
use Drupal\webform\WebformInterface;
use Drupal\developer\Entity\Flat\Flat;
use Drupal\developer\Entity\EntityInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

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
  public const ENTITIES_STATUSES = [
    1 => 'Available',
    2 => 'Reserved',
    3 => 'Sold',
  ];

  /**
   * Contains statuses related fill.
   */
  public const STATUSES_FILL = [
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
    protected ConfigFactoryInterface $configFactory,
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
  public function getRelatedEntities(EntityInterface $developer_entity, string $related_entity_type): array {
    $related_entity_field_name = $related_entity_type === 'developer_flat' ? 'floor_id' : 'estate_id';
    $related_entities = $this->entityTypeManager
      ->getStorage($related_entity_type)
      ->loadByProperties([$related_entity_field_name => $developer_entity->id()]);

    return $related_entities;
  }

  /**
   * {@inheritdoc}
   */
  public function getLegend(array $related_entities): array {
    $legend = [];

    if (!empty($related_entities)) {
      $legend = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['legend'],
        ],
        'statuses' => [],
      ];

      foreach ($related_entities as $entity) {
        $entity_status = $entity->status->value;

        if (empty($entity->status->value)) {
          $entity_status = '1';
        }

        /** @var string */
        $status_name = self::ENTITIES_STATUSES[$entity_status];

        // @codingStandardsIgnoreStart
        $legend['statuses']['status_' . $entity_status] = [
          '#type' => 'html_tag',
          '#tag' => 'span',
          '#attributes' => [
            'class' => [strtolower(self::ENTITIES_STATUSES[$entity_status])],
          ],
          '#value' => $this->t($status_name),
        ];
        // @codingStandardsIgnoreEnd
      }
    }

    return $legend;
  }

  /**
   * {@inheritdoc}
   */
  public function convertToSvgPathsWithStatus(array $svg_paths_data, string $related_entity_type): array {
    $svg_paths_data_with_status = [];

    foreach ($svg_paths_data as $entity_id => $path) {
      $developer_entity = $this->entityTypeManager->getStorage($related_entity_type)->load($entity_id);
      $entity_status = $developer_entity->status->value ?: '1';
      $svg_paths_data_with_status[$entity_id]['coordinates'] = $path;
      $svg_paths_data_with_status[$entity_id]['status'] = strtolower(self::ENTITIES_STATUSES[$entity_status]);
      $svg_paths_data_with_status[$entity_id]['fill'] = self::STATUSES_FILL[$entity_status];
    }

    return $svg_paths_data_with_status;
  }

  /**
   * {@inheritdoc}
   */
  public function getRelatedEntitiesTooltipData(EntityInterface $developer_entity, string $related_entity_type): array {
    $related_entities = $this->getRelatedEntities($developer_entity, $related_entity_type);
    $view_builder = $this->entityTypeManager->getViewBuilder($related_entity_type);
    $tooltip_data = [];

    foreach ($related_entities as $entity_id => $entity) {
      $entity_status = $entity->status->value ?? '1';
      $entity_view_modes = $this->entityDisplayRepository->getViewModeOptionsByBundle($related_entity_type, $entity->type->target_id);

      if ($entity_status !== '3' && array_key_exists('tooltip', $entity_view_modes)) {
        $entity_view = $view_builder->view($entity, 'tooltip');
        $tooltip_data[$entity_id] = $entity_view;
      }
    }

    return $tooltip_data;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityDescription(EntityInterface $developer_entity): array {
    $entity_type_id = $developer_entity->getEntityTypeId();
    $entity_view_modes = $this->entityDisplayRepository->getViewModeOptionsByBundle($entity_type_id, $developer_entity->type->target_id);
    $entity_description = [];

    if (array_key_exists('description', $entity_view_modes)) {
      $view_builder = $this->entityTypeManager->getViewBuilder($entity_type_id);
      $entity_description = $view_builder->view($developer_entity, 'description');
    }

    return $entity_description;
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
  public function getNavigationOptions(EntityInterface $developer_entity, string $starting_entity_name): array {
    $navigation_options = [
      'buildings' => [],
      'default_building' => NULL,
      'floors' => [],
      'default_floor' => NULL,
      'flats' => [],
      'default_flat' => NULL,
    ];
    $is_create_buildings_options = $starting_entity_name === 'estate' ? TRUE : FALSE;
    $entity_type_id = $developer_entity->getEntityType()->id();

    /* Estate stage */
    if ($entity_type_id === 'developer_estate' && $is_create_buildings_options) {

      /* Set building options */
      $building_storage = $this->entityTypeManager->getStorage('developer_building');
      $related_buildings_ids = $building_storage
        ->getQuery()
        ->condition('estate_id', $developer_entity->id())
        ->execute();
      $related_buildings = $building_storage->loadMultiple($related_buildings_ids);

      $navigation_options['buildings']['null'] = '-';
      $navigation_options['floors']['null'] = '-';
      $navigation_options['flats']['null'] = '-';

      foreach ($related_buildings as $id => $building) {
        $navigation_options['buildings'][$id] = $building->label();
      }
    }

    /* Building stage */
    if ($entity_type_id === 'developer_building') {

      if ($is_create_buildings_options) {
        /* Set default building option */
        $navigation_options['default_building'] = $developer_entity->id();

        /* Set building options */
        $building_storage = $this->entityTypeManager->getStorage('developer_building');
        $sibling_buildings_ids = $building_storage
          ->getQuery()
          ->condition('estate_id', $developer_entity->estate_id->target_id)
          ->execute();
        $sibling_buildings = $building_storage->loadMultiple($sibling_buildings_ids);

        foreach ($sibling_buildings as $id => $building) {
          $navigation_options['buildings'][$id] = $building->label();
        }
      }

      /* Set floor options */
      $navigation_options['floors']['null'] = '-';
      $building_storage = $this->entityTypeManager->getStorage('developer_floor');
      $related_floors_ids = $building_storage
        ->getQuery()
        ->condition('building_id', $developer_entity->id())
        ->execute();
      $related_floors = $building_storage->loadMultiple($related_floors_ids);

      foreach ($related_floors as $id => $floor) {
        $navigation_options['floors'][$id] = $floor->label();
      }

      /* Set flat options */
      $navigation_options['flats']['null'] = '-';
    }

    /* Floor stage */
    if ($entity_type_id === 'developer_floor') {

      if ($is_create_buildings_options) {
        /* Set default building option */
        $navigation_options['default_building'] = $developer_entity->building_id->target_id;
        $parent_estate_id = $developer_entity->building_id->entity->estate_id->target_id;

        /* Set building options */
        $building_storage = $this->entityTypeManager->getStorage('developer_building');
        $parent_buildings_ids = $building_storage
          ->getQuery()
          ->condition('estate_id', $parent_estate_id)
          ->execute();
        $parent_buildings = $building_storage->loadMultiple($parent_buildings_ids);

        foreach ($parent_buildings as $id => $building) {
          $navigation_options['buildings'][$id] = $building->label();
        }
      }

      /* Set default floor option */
      $navigation_options['default_floor'] = $developer_entity->id();

      /* Set floor options */
      $floor_storage = $this->entityTypeManager->getStorage('developer_floor');
      $sibling_floors_ids = $floor_storage
        ->getQuery()
        ->condition('building_id', $developer_entity->building_id->target_id)
        ->execute();
      $sibling_floors = $floor_storage->loadMultiple($sibling_floors_ids);

      foreach ($sibling_floors as $id => $floor) {
        $navigation_options['floors'][$id] = $floor->label();
      }

      /* Set flat options */
      $navigation_options['flats']['null'] = '-';
      $flat_storage = $this->entityTypeManager->getStorage('developer_flat');
      $related_flats_ids = $flat_storage
        ->getQuery()
        ->condition('floor_id', $developer_entity->id())
        ->condition('status', 3, 'NOT IN')
        ->execute();
      $related_flats = $flat_storage->loadMultiple($related_flats_ids);

      foreach ($related_flats as $id => $flat) {
        $navigation_options['flats'][$id] = $flat->label();
      }
    }

    /* Flat stage */
    if ($entity_type_id === 'developer_flat') {

      if ($is_create_buildings_options) {
        /* Set default building option */
        $navigation_options['default_building'] = $developer_entity->floor_id->entity->building_id->target_id;
        $parent_estate_id = $developer_entity->floor_id->entity->building_id->entity->estate_id->target_id;

        /* Set building options */
        $building_storage = $this->entityTypeManager->getStorage('developer_building');
        $parent_buildings_ids = $building_storage
          ->getQuery()
          ->condition('estate_id', $parent_estate_id)
          ->execute();
        $parent_buildings = $building_storage->loadMultiple($parent_buildings_ids);

        foreach ($parent_buildings as $id => $building) {
          $navigation_options['buildings'][$id] = $building->label();
        }
      }

      /* Set default floor option */
      $navigation_options['default_floor'] = $developer_entity->floor_id->target_id;

      /* Set floor options */
      $floor_storage = $this->entityTypeManager->getStorage('developer_floor');
      $parent_floors_ids = $floor_storage
        ->getQuery()
        ->condition('building_id', $developer_entity->floor_id->entity->building_id->target_id)
        ->execute();
      $parent_floors = $floor_storage->loadMultiple($parent_floors_ids);

      foreach ($parent_floors as $id => $floor) {
        $navigation_options['floors'][$id] = $floor->label();
      }

      /* Set default flat option */
      $navigation_options['default_flat'] = $developer_entity->id();

      /* Set flat options */
      $flat_storage = $this->entityTypeManager->getStorage('developer_flat');
      $sibling_flats_ids = $flat_storage
        ->getQuery()
        ->condition('floor_id', $developer_entity->floor_id->target_id)
        ->condition('status', 3, 'NOT IN')
        ->execute();
      $sibling_flats = $flat_storage->loadMultiple($sibling_flats_ids);

      foreach ($sibling_flats as $id => $flat) {
        $navigation_options['flats'][$id] = $flat->label();
      }
    }

    return $navigation_options;
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

    /* Prepare navigation */
    $navigation = [];
    $is_use_navigation = $this->configFactory->get('block.block.' . $block_id)->get('settings.visualization.settings.use_navigation');

    if ($is_use_navigation) {
      $navigation_options = $this->getNavigationOptions($this->getDeveloperEntity($entity_name, $entity_id), $starting_entity_name);
      $selects_count = 0;
      $navigation = [
        '#type' => 'container',
        'navigation' => [],
      ];

      if ($starting_entity_name === 'estate') {
        $navigation['navigation']['select_building'] = [
          '#type' => 'select',
          '#title' => $this->t('Building'),
          '#options' => $navigation_options['buildings'],
          '#value' => $navigation_options['default_building'],
        ];
        $selects_count++;
      }

      $navigation['navigation']['select_floor'] = [
        '#type' => 'select',
        '#title' => $this->t('Floor'),
        '#options' => $navigation_options['floors'],
        '#value' => $navigation_options['default_floor'],
      ];
      $selects_count++;

      if ($sell_entity_name === 'flat') {
        $navigation['navigation']['select_flat'] = [
          '#type' => 'select',
          '#title' => $this->t('Flat'),
          '#options' => $navigation_options['flats'],
          '#value' => $navigation_options['default_flat'],
        ];
        $selects_count++;
      }

      switch ($selects_count) {
        case 2:
          $navigation['#attributes']['class'] = [
            'navigation-container',
            'two-selects',
          ];
          break;

        case 3:
          $navigation['#attributes']['class'] = [
            'navigation-container',
            'three-selects',
          ];
          break;

        default:
          $navigation['#attributes']['class'] = [
            'navigation-container',
            'one-select',
          ];
      }

      if ($starting_entity_name === 'building') {
        $navigation_container_classes[] = 'two-selects';
      }
    }

    /* Prepare building description button */
    $description_btn = [];

    if ($sell_entity_name === 'building' && $entity_name == 'building') {
      $building_entity_id = $this->getDeveloperEntity($entity_name, $entity_id)->id();

      $description_btn = [
        '#type' => 'link',
        '#title' => $this->t('Description'),
        '#url' => Url::fromRoute('entity.developer_building.canonical_description', ['developer_building' => $building_entity_id]),
        '#attributes' => [
          'class' => [
            'description-btn',
            'developer-visualization-btn',
            'use-ajax',
          ],
          'data-dialog-options' => '{"height":"80%","width":"90%","max-height":"650","max-width":"750"}',
          'data-dialog-type' => 'modal',
        ],
      ];
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
    }

    /* Prepare floor view or estate view if building are for sale */
    $legend = [];
    $tooltip_data = [];

    if (
      ($entity_name === 'floor' && $sell_entity_name === 'flat') ||
      ($entity_name === 'estate' && $sell_entity_name === 'building')
    ) {
      $related_entity_type = $entity_name === 'floor' ? 'developer_flat' : 'developer_building';
      $developer_entity = $this->getDeveloperEntity($entity_name, $entity_id);
      $related_entities = $this->getRelatedEntities($developer_entity, $related_entity_type);
      $legend = $this->getLegend($related_entities);
      $svg_paths_data = $this->convertToSvgPathsWithStatus($svg_paths_data, $related_entity_type);
      $tooltip_data = $this->getRelatedEntitiesTooltipData($developer_entity, $related_entity_type);
    }

    $entity_description = [];
    $webform = [];

    /* Prepare flat view */
    if ($entity_name === 'flat') {
      $flat_entity = $this->getDeveloperEntity($entity_name, $entity_id);
      $entity_description = $this->getEntityDescription($flat_entity);
      $webform = $this->getWebformView($webform_id);
    }

    /* Prepare block content */
    $block = [
      '#theme' => 'visualization_presentation',
      '#attached' => [
        'library' => ['developer_visualization/global'],
      ],
      '#block_id' => $block_id,
      '#navigation' => $navigation,
      '#entity_description' => $entity_description,
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
      '#description_btn' => $description_btn,
      '#back_btn' => $back_btn,
      '#ask_for_offer_btn' => $ask_for_offer_btn,
      '#legend' => $legend,
      '#tooltip_data' => $tooltip_data,
      '#webform' => $webform,
      '#front_url' => Url::fromRoute('<front>'),
    ];

    return $block;
  }

}
