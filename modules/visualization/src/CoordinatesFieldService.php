<?php

namespace Drupal\developer_visualization;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Entity\Display\EntityFormDisplayInterface;

/**
 * Coordinates field service.
 */
class CoordinatesFieldService {
  use StringTranslationTrait;

  /**
   * Entities list that should have coordinates.
   */
  public const COORDINATED_ENTITIES = [
    'developer_building',
    'developer_floor',
    'developer_flat',
  ];

  /**
   * Constructs a CoordinatesFieldService object.
   */
  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected EntityTypeBundleInfoInterface $entityTypeBundleInfo,
    protected EntityDisplayRepositoryInterface $displayRepository,
  ) {}

  /**
   * Create coordinates field.
   *
   * Create coordinates field for all bundles of all developer
   * entities except Estate.
   */
  public function createCoordinatesField(): void {
    $field_name = 'coordinates';

    foreach (self::COORDINATED_ENTITIES as $entity_type_id) {
      if (empty(FieldStorageConfig::loadByName($entity_type_id, $field_name))) {
        $field_storage = FieldStorageConfig::create([
          'field_name'             => $field_name,
          'langcode'               => 'en',
          'entity_type'            => $entity_type_id,
          'type'                   => 'string_long',
          'module'                 => 'text',
          'locked'                 => TRUE,
          'cardinality'            => 1,
          'translatable'           => TRUE,
          'persist_with_no_fields' => FALSE,
          'custom_storage'         => FALSE,
        ]);
        $field_storage->save();
      }

      $bundles = $this->entityTypeBundleInfo->getBundleInfo($entity_type_id);

      foreach ($bundles as $bundle_id => $bundle) {
        $field = FieldConfig::loadByName($entity_type_id, $bundle_id, $field_name);

        if (empty($field)) {
          FieldConfig::create([
            'field_name'   => $field_name,
            'entity_type'  => $entity_type_id,
            'bundle'       => $bundle_id,
            'label'        => $this->t('Coordinates'),
            'translatable' => TRUE,
          ])->save();

          /** @var \Drupal\Core\Entity\Display\EntityFormDisplayInterface */
          $entity_bundle_display = $this->entityTypeManager
            ->getStorage('entity_form_display')
            ->load($entity_type_id . '.' . $bundle_id . '.default');

          if ($entity_bundle_display instanceof EntityFormDisplayInterface) {
            $entity_bundle_display
              ->setComponent($field_name, [
                'type' => 'string_textarea',
                'weight' => 87,
              ])
              ->save();
          }
        }
      }
    }
  }

  /**
   * Delete coordinates field.
   *
   * Delete coordinates field for all bundles of all developer
   * entities.
   */
  public function deleteCoordinatesField(): void {
    $field_name = 'coordinates';

    foreach (self::COORDINATED_ENTITIES as $entity_type_id) {
      if (!empty($field_storage = FieldStorageConfig::loadByName($entity_type_id, $field_name))) {
        $field_storage->delete();
      }
    }
  }

}
