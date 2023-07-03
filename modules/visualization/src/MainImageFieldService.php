<?php

namespace Drupal\developer_visualization;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Entity\Display\EntityFormDisplayInterface;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;

/**
 * Main image field service.
 */
class MainImageFieldService {
  use StringTranslationTrait;

  /**
   * Entities list that should have main image.
   */
  public const ENTITIES_WITH_MAIN_IMAGE = [
    'developer_estate',
    'developer_building',
    'developer_floor',
    'developer_flat',
  ];

  /**
   * Constructs a MainImageFieldService object.
   */
  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected EntityTypeBundleInfoInterface $entityTypeBundleInfo,
    protected EntityDisplayRepositoryInterface $displayRepository,
  ) {}

  /**
   * Create main image field.
   *
   * Create main image field for all bundles of all developer entities.
   */
  public function createMainImageField(): void {
    $field_name = 'main_image';

    foreach (self::ENTITIES_WITH_MAIN_IMAGE as $entity_type_id) {
      if (empty(FieldStorageConfig::loadByName($entity_type_id, $field_name))) {
        $field_storage = FieldStorageConfig::create([
          'field_name'             => $field_name,
          'langcode'               => 'en',
          'entity_type'            => $entity_type_id,
          'type'                   => 'entity_reference',
          'locked'                 => TRUE,
          'required'               => TRUE,
          'cardinality'            => 1,
          'persist_with_no_fields' => FALSE,
          'custom_storage'         => FALSE,
          'settings'               => [
            'target_type' => 'media',
          ],
        ]);
        $field_storage->save();
      }

      $bundles = $this->entityTypeBundleInfo->getBundleInfo($entity_type_id);
      foreach ($bundles as $bundle_id => $bundle) {
        $field = FieldConfig::loadByName($entity_type_id, $bundle_id, $field_name);
        if (empty($field)) {
          FieldConfig::create([
            'field_name'   => $field_name,
            'description'  => $this->t('Main entity image used in visualization plugin.'),
            'entity_type'  => $entity_type_id,
            'bundle'       => $bundle_id,
            'label'        => $this->t('Main image'),
            'required' => TRUE,
            'settings'     => [
              'handler' => 'default',
              'handler_settings' => [
                'target_bundles' => [$entity_type_id],
              ],
            ],
          ])->save();

          /** @var \Drupal\Core\Entity\Display\EntityFormDisplayInterface */
          $entity_bundle_form_display = $this->entityTypeManager
            ->getStorage('entity_form_display')
            ->load($entity_type_id . '.' . $bundle_id . '.default');
          if ($entity_bundle_form_display instanceof EntityFormDisplayInterface) {
            $entity_bundle_form_display
              ->setComponent($field_name, [
                'type' => 'entity_reference_autocomplete',
                'weight' => 86,
              ])
              ->save();
          }

          /** @var \Drupal\Core\Entity\Display\EntityViewDisplayInterface */
          $entity_bundle_view_display = $this->entityTypeManager
            ->getStorage('entity_view_display')
            ->load($entity_type_id . '.' . $bundle_id . '.default');
          if ($entity_bundle_view_display instanceof EntityViewDisplayInterface) {
            $entity_bundle_view_display
              ->setComponent($field_name, [
                'label' => 'hidden',
                'type' => 'entity_reference_entity_view',
                'weight' => 101,
                'region' => 'content',
              ])
              ->save();
          }
        }
      }
    }
  }

  /**
   * Delete main image field.
   *
   * Delete main image field for all bundles of all developer entities.
   */
  public function deleteMainImageField(): void {
    $field_name = 'main_image';
    foreach (self::ENTITIES_WITH_MAIN_IMAGE as $entity_type_id) {
      if (!empty($field_storage = FieldStorageConfig::loadByName($entity_type_id, $field_name))) {
        $field_storage->delete();
      }
    }
  }

}
