<?php

namespace Drupal\developer_demo;

use Drupal\media\Entity\Media;
use Drupal\developer\Entity\Flat\Flat;
use Drupal\developer\Entity\Floor\Floor;
use Drupal\file\FileRepositoryInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\developer\Entity\Estate\Estate;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\developer\Entity\Building\Building;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Creator of demo data.
 */
class CreatorService {
  use StringTranslationTrait;

  /**
   * Developer images name.
   */
  const IMAGES_NAME = [
    'estate',
    'estate_building_a',
    'estate_building_b',
    'estate_building_c',
    'estate_building_d',
    'estate_floor',
    'flat',
    'house',
    'house_floor_0',
    'house_floor_1',
  ];

  /**
   * Developer buildings image name.
   */
  const BUILDINGS_IMAGE_NAME = [
    'estate_building_a',
    'estate_building_b',
    'estate_building_c',
    'estate_building_d',
    'house',
  ];

  /**
 * Developer buildings image name.
 */
  const BUILDINGS_STATUS_MAP = [
    'estate_building_a' => 1,
    'estate_building_b' => 3,
    'estate_building_c' => 1,
    'estate_building_d' => 2,
    'house' => 1,
  ];

  /**
   * Contains id's of created media.
   */
  protected array $mediaIds = [];

  /**
   * Contains ids of created entities in multilevel array.
   */
  protected array $entitiesIdsMap;

  /**
   * Constructs a CreatorService object.
   */
  public function __construct(
    protected FileRepositoryInterface $fileRepository,
    protected FileSystemInterface $fileSystem,
    protected EntityTypeManagerInterface $entityTypeManager,
    protected ModuleExtensionList $moduleExtensionList,
  ) {}

  /**
   * Initialize base data creator.
   */
  public function initBaseCreator(): void {
    $this->createEstate();
  }

  /**
   * Create developer estate.
   */
  private function createEstate(): void {
    $entity_data = [
      'name' => $this->t('Paradise'),
      'type' => 'demo',
      'uid' => 1,
    ];

    $estate = Estate::create($entity_data);
    $estate->save();

    $this->entitiesIdsMap['estate'] = [
      'estate_id' => (int) $estate->id(),
    ];
    $this->createBuildings();
  }

  /**
   * Create developer buildings.
   */
  private function createBuildings(): void {

    foreach (self::BUILDINGS_IMAGE_NAME as $building_image_name) {

      if (str_starts_with($building_image_name, 'estate')) {
        $name = strtoupper(substr($building_image_name, -1));
        $estate_id = [
          'target_id' => $this->entitiesIdsMap['estate']['estate_id'],
        ];
        $status = self::BUILDINGS_STATUS_MAP[$building_image_name];
      }
      else {
        $name = $this->t('Cool house');
        $estate_id = [];
        $status = NULL;
      }

      $entity_data = [
        'name' => $name,
        'type' => 'demo',
        'uid' => 1,
        'estate_id' => $estate_id,
        'status' => $status,
      ];

      $building = Building::create($entity_data);
      $building->save();

      if (str_starts_with($building_image_name, 'estate')) {
        $this->entitiesIdsMap['estate'][$building_image_name] = [
          'building_id' => (int) $building->id(),
        ];
      }
      else {
        $this->entitiesIdsMap[$building_image_name] = [
          'building_id' => (int) $building->id(),
        ];
      }
    }

    $this->createFloors();
  }

  /**
   * Create developer floors.
   */
  private function createFloors(): void {

    foreach (self::BUILDINGS_IMAGE_NAME as $building_image_name) {
      $entity_data = [
        'type' => 'demo',
        'uid' => 1,
      ];

      if (str_starts_with($building_image_name, 'estate')) {
        $entity_data['building_id'] = [
          'target_id' => $this->entitiesIdsMap['estate'][$building_image_name]['building_id'],
        ];
        $entity_data['is_final'] = 0;

        for ($i = 0; $i < 9; $i++) {
          $entity_data['name'] = $i;
          $floor = Floor::create($entity_data);
          $floor->save();

          $this->entitiesIdsMap['estate'][$building_image_name]['estate_floor_' . $i] = [
            'floor_id' => (int) $floor->id(),
          ];
        }
      }
      else {
        $entity_data['building_id'] = [
          'target_id' => $this->entitiesIdsMap[$building_image_name]['building_id'],
        ];
        $entity_data['is_final'] = 1;

        for ($i = 0; $i < 2; $i++) {
          $entity_data['name'] = $i;

          $floor = Floor::create($entity_data);
          $floor->save();

          $this->entitiesIdsMap[$building_image_name]['house_floor_' . $i] = [
            'floor_id' => (int) $floor->id(),
          ];
        }
      }
    }

    $this->createFlats();
  }

  /**
   * Create developer flats.
   */
  private function createFlats(): void {
    $estate_buildings_images = self::BUILDINGS_IMAGE_NAME;
    unset($estate_buildings_images[4]);

    foreach ($estate_buildings_images as $estate_building_name) {
      $entity_data = [
        'type' => 'demo',
        'uid' => 1,
      ];
      $flat_number = 1;

      foreach ($this->entitiesIdsMap['estate'][$estate_building_name] as $floor) {
        if (!is_int($floor)) {
          $entity_data['floor_id'] = [
            'target_id' => $floor['floor_id'],
          ];

          for ($i = 0; $i < 4; $i++) {
            $entity_data['name'] = $flat_number;
            $flat_number++;
            $entity_data['status'] = rand(1, 3);

            $flat = Flat::create($entity_data);
            $flat->save();
          }
        }
      }

      $flat_number = 1;
    }
  }

  /**
   * Initialize visualization data creator.
   */
  public function initVisualizationCreator(): void {
    $this->createMedia();
    $this->setCoordinatesAndMainImages();
  }

  /**
   * Create media entities.
   */
  private function createMedia(): void {
    $directory = 'public://developer_demo';
    $this->fileSystem->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY);

    $this->removeAllDemoMedia();

    foreach (self::IMAGES_NAME as $image_name) {
      /** @var string */
      $image_data = file_get_contents($this->moduleExtensionList->getPath('developer_demo') . '/images/' . $image_name . '.jpg');
      $image = $this->fileRepository->writeData($image_data, 'public://developer_demo/' . $image_name . '.jpg', FileSystemInterface::EXISTS_REPLACE);
      $image_label = $this->t('Demo @entity', ['@entity' => str_replace('_', ' ', $image_name)]);
      $bundle = '';

      switch ($image_name) {
        case 'estate':
          $bundle = 'developer_estate';
          break;

        case 'estate_building_a':
        case 'estate_building_b':
        case 'estate_building_c':
        case 'estate_building_d':
        case 'house':
          $bundle = 'developer_building';
          break;

        case 'estate_floor':
        case 'house_floor_0':
        case 'house_floor_1':
          $bundle = 'developer_floor';
          break;

        case 'flat':
          $bundle = 'developer_flat';
          break;
      }

      $media = Media::create([
        'name' => $image_label,
        'bundle' => $bundle,
        'uid' => 1,
        'status' => 1,
        'field_developer_image' => [
          'target_id' => $image->id(),
          'alt' => $image_label,
          'title' => $image_label,
        ],
      ]);
      $media->save();

      $this->mediaIds[$image_name] = (int) $media->id();
    }
  }

  /**
   * Set developer entities coordinates and main image fields.
   */
  private function setCoordinatesAndMainImages() {

    /* Prepare coordinates data. */
    /** @var string */
    $coordinates_file = file_get_contents($this->moduleExtensionList->getPath('developer_demo') . '/data/coordinates.json');
    $coordinates = json_decode($coordinates_file);

    /* Set solo Cool House coordinates and main image. */
    $cool_house = $this->entityTypeManager
      ->getStorage('developer_building')
      ->loadByProperties([
        'name' => 'Cool house',
        'type' => 'demo',
      ]);
    /** @var \Drupal\Core\Entity\ContentEntityInterface */
    $cool_house = array_shift($cool_house);
    $cool_house_floors = $this->entityTypeManager
      ->getStorage('developer_floor')
      ->loadByProperties(['building_id' => $cool_house->id()]);

    $cool_house
      ->set('main_image', $this->mediaIds['house'])
      ->save();

    $floor_i = 0;

    /** @var \Drupal\developer\Entity\Floor\Floor $floor */
    foreach ($cool_house_floors as $floor) {
      /* @phpstan-ignore-next-line */
      $floor_coordinates = $coordinates->cool_house->{$floor->name->value};
      $floor
        ->set('coordinates', $floor_coordinates)
        ->set('main_image', $this->mediaIds['house_floor_' . $floor_i])
        ->save();

      $floor_i++;
    }

    /* Set Paradise building's coordinates and main image. */
    $estate_paradise = $this->entityTypeManager
      ->getStorage('developer_estate')
      ->loadByProperties([
        'name' => 'Paradise',
        'type' => 'demo',
      ]);
    /** @var \Drupal\Core\Entity\ContentEntityInterface */
    $estate_paradise = array_shift($estate_paradise);
    $estate_paradise
      ->set('main_image', $this->mediaIds['estate'])
      ->save();

    $paradise_buildings = $this->entityTypeManager
      ->getStorage('developer_building')
      ->loadByProperties(['estate_id' => $estate_paradise->id()]);

    /** @var \Drupal\developer\Entity\Building\Building $building */
    foreach ($paradise_buildings as $building) {
      /* @phpstan-ignore-next-line */
      $building_coordinates = $coordinates->paradise->{$building->name->value}->coordinates;
      $building
        ->set('coordinates', $building_coordinates)
        ->set('main_image', $this->mediaIds['estate_building_' . strtolower($building->name->value)])
        ->save();

      $building_floors = $this->entityTypeManager
        ->getStorage('developer_floor')
        ->loadByProperties(['building_id' => $building->id()]);

      /** @var \Drupal\developer\Entity\Floor\Floor $floor */
      foreach ($building_floors as $floor) {
        /* @phpstan-ignore-next-line */
        $floor_coordinates = $coordinates->paradise->{$building->name->value}->{$floor->name->value};
        $floor
          ->set('coordinates', $floor_coordinates)
          ->set('main_image', $this->mediaIds['estate_floor'])
          ->save();

        $floor_flats = $this->entityTypeManager
          ->getStorage('developer_flat')
          ->loadByProperties(['floor_id' => $floor->id()]);

        $flat_i = 1;
        /** @var \Drupal\developer\Entity\Flat\Flat $flat */
        foreach ($floor_flats as $flat) {
          /* @phpstan-ignore-next-line */
          $flat_coordinates = $coordinates->flats->{$flat_i};
          $flat
            ->set('coordinates', $flat_coordinates)
            ->set('main_image', $this->mediaIds['flat'])
            ->save();
          $flat_i++;
        }
      }
    }
  }

  /**
   * Remove all demo media entities.
   */
  public function removeAllDemoMedia() {
    $result = $this->entityTypeManager
      ->getStorage('media')
      ->getQuery()
      ->condition('bundle', [
        'developer_estate',
        'developer_building',
        'developer_floor',
        'developer_flat',
      ], 'IN')
      ->condition('name', 'demo', 'STARTS_WITH')
      ->execute();

    $storage_handler = $this->entityTypeManager->getStorage('media');
    $media = $storage_handler->loadMultiple($result);
    $storage_handler->delete($media);
  }

}
