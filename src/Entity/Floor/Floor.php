<?php

namespace Drupal\developer\Entity\Floor;

use Drupal\user\EntityOwnerTrait;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\developer\Entity\EntityInterface;
use Drupal\Core\Entity\EditorialContentEntityBase;

/**
 * Defines the Floor entity.
 *
 * @ContentEntityType(
 *   id = "developer_floor",
 *   label = @Translation("Floor"),
 *   label_collection = @Translation("Floors"),
 *   label_singular = @Translation("floor"),
 *   label_plural = @Translation("floors"),
 *   label_count = @PluralTranslation(
 *     singular = "@count floor",
 *     plural = "@count floors",
 *   ),
 *   bundle_label = @Translation("Floor type"),
 *   base_table = "developer_floor",
 *   data_table = "developer_floor_field",
 *   revision_table = "developer_floor_revision",
 *   revision_data_table = "developer_floor_field_revision",
 *   show_revision_ui = TRUE,
 *   admin_permission = "administer floor entity",
 *   permission_granularity = "bundle",
 *   fieldable = TRUE,
 *   field_ui_base_route = "entity.developer_floor_type.edit_form",
 *   translatable = TRUE,
 *   bundle_entity_type = "developer_floor_type",
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\developer\Entity\EntityListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "access" = "Drupal\developer\Entity\EntityAccessControlHandler",
 *     "form" = {
 *       "default" = "Drupal\developer\Form\Entity\EntityForm",
 *       "delete" = "Drupal\developer\Form\Entity\EntityDeleteForm",
 *       "delete-multiple-confirm" = "Drupal\developer\Form\Entity\EntityDeleteMultipleForm",
 *     },
 *     "route_provider" = {
 *       "default" = "Drupal\entity\Routing\AdminHtmlRouteProvider",
 *       "revisions" = "Drupal\entity\Routing\RevisionRouteProvider",
 *     },
 *     "local_task_provider" = {
 *       "default" = "Drupal\entity\Menu\DefaultEntityLocalTaskProvider",
 *     },
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "type",
 *     "owner" = "uid",
 *     "uuid" = "uuid",
 *     "label" = "name",
 *     "langcode" = "langcode",
 *     "revision" = "vid",
 *     "published" = "published",
 *   },
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_user",
 *     "revision_created" = "revision_created",
 *     "revision_log_message" = "revision_log_message",
 *   },
 *   links = {
 *     "add-page" = "/admin/developer/content/floor/add",
 *     "add-form" = "/admin/developer/content/floor/add/{developer_floor_type}",
 *     "edit-form" = "/admin/developer/content/floor/{developer_floor}/edit",
 *     "delete-form" = "/admin/developer/content/floor/{developer_floor}/delete",
 *     "delete-multiple-form" = "/admin/developer/content/floors/delete",
 *     "collection" = "/admin/developer/content/floors",
 *     "canonical" = "/floor/{developer_floor}",
 *     "version-history" = "/admin/developer/content/floor/{developer_floor}/revisions",
 *     "revision" = "/admin/developer/content/floor/{developer_floor}/revision/{developer_floor_revision}/view",
 *     "revision-revert-form" = "/admin/developer/content/floor/{developer_floor}/revision/{developer_floor_revision}/revert",
 *     "revision-delete-form" = "/admin/developer/content/floor/{developer_floor}/revision/{developer_floor_revision}/delete",
 *   },
 * )
 */
class Floor extends EditorialContentEntityBase implements EntityInterface {

  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);

    /** @var array $fields */
    $fields['uid']
      ->setLabel(t('Author'))
      ->setDescription(t('The Name of the associated user.'))
      ->setSetting('handler', 'default')
      ->setRevisionable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'entity_reference_label',
        'region' => 'hidden',
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => 60,
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time when the floor was created.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time when the floor was last edited.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    $fields['path'] = BaseFieldDefinition::create('path')
      ->setLabel(t('URL alias'))
      ->setDisplayOptions('form', [
        'type' => 'path',
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setComputed(TRUE);

    $fields['published']
      ->setLabel(t('Published'))
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => TRUE,
        ],
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Floor entity.'))
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setRequired(TRUE)
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 1,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['main_image'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Main image'))
      ->setDescription(t('Main floor image where all related flats will be shown.'))
      ->setSetting('target_type', 'media')
      ->setSetting('handler', 'default')
      ->setSetting('handler_settings', [
        'target_bundles' => [
          'developer_floor' => 'developer_floor',
        ],
      ])
      ->setRevisionable(TRUE)
      ->setRequired(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'entity_reference_entity_view',
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['building_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Building'))
      ->setDescription(t('The name of the associated building.'))
      ->setSetting('target_type', 'developer_building')
      ->setSetting('handler', 'default')
      ->setRevisionable(TRUE)
      ->setRequired(TRUE)
      ->setDisplayOptions('view', [
        'region' => 'hidden',
      ])
      ->setDisplayOptions('form', [
        'type' => 'developer_entity_reference_autocomplete',
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => 60,
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['is_final'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Final'))
      ->setDescription(t('Determines that the floor is final and no flats can be added to it.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
      ])
      ->setDisplayConfigurable('form', TRUE);

    return $fields;
  }

}
