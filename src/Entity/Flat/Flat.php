<?php

namespace Drupal\developer\Entity\Flat;

use Drupal\user\EntityOwnerTrait;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\developer\Entity\EntityInterface;
use Drupal\Core\Entity\EditorialContentEntityBase;

/**
 * Defines the Flat entity.
 *
 * @ContentEntityType(
 *   id = "developer_flat",
 *   label = @Translation("Flat"),
 *   label_collection = @Translation("Flats"),
 *   label_singular = @Translation("flat"),
 *   label_plural = @Translation("flats"),
 *   label_count = @PluralTranslation(
 *     singular = "@count flat",
 *     plural = "@count flats",
 *   ),
 *   bundle_label = @Translation("Flat type"),
 *   base_table = "developer_flat",
 *   data_table = "developer_flat_field",
 *   revision_table = "developer_flat_revision",
 *   revision_data_table = "developer_flat_field_revision",
 *   show_revision_ui = TRUE,
 *   admin_permission = "administer flat entity",
 *   permission_granularity = "bundle",
 *   fieldable = TRUE,
 *   field_ui_base_route = "entity.developer_flat_type.edit_form",
 *   translatable = TRUE,
 *   bundle_entity_type = "developer_flat_type",
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
 *     "status" = "status",
 *     "revision" = "vid",
 *     "published" = "published",
 *   },
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_user",
 *     "revision_created" = "revision_created",
 *     "revision_log_message" = "revision_log_message",
 *   },
 *   links = {
 *     "add-page" = "/admin/developer/content/flat/add",
 *     "add-form" = "/admin/developer/content/flat/add/{developer_flat_type}",
 *     "edit-form" = "/admin/developer/content/flat/{developer_flat}/edit",
 *     "delete-form" = "/admin/developer/content/flat/{developer_flat}/delete",
 *     "delete-multiple-form" = "/admin/developer/content/flats/delete",
 *     "collection" = "/admin/developer/content/flats",
 *     "canonical" = "/flat/{developer_flat}",
 *     "version-history" = "/admin/developer/content/flat/{developer_flat}/revisions",
 *     "revision" = "/admin/developer/content/flat/{developer_flat}/revision/{developer_flat_revision}/view",
 *     "revision-revert-form" = "/admin/developer/content/flat/{developer_flat}/revision/{developer_flat_revision}/revert",
 *     "revision-delete-form" = "/admin/developer/content/flat/{developer_flat}/revision/{developer_flat_revision}/delete",
 *   },
 * )
 */
class Flat extends EditorialContentEntityBase implements EntityInterface {

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
      ->setDescription(t('The time when the flat was created.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time when the flat was last edited.'))
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
      ->setDescription(t('The name of the Flat entity.'))
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
      ->setDescription(t('Main flat image for better perspective.'))
      ->setSetting('target_type', 'media')
      ->setSetting('handler', 'default')
      ->setSetting('handler_settings', [
        'target_bundles' => [
          'developer_flat' => 'developer_flat',
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

    $fields['floor_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Floor'))
      ->setDescription(t('The name of the associated floor.'))
      ->setSetting('target_type', 'developer_floor')
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

    $fields['status'] = BaseFieldDefinition::create('list_integer')
      ->setSettings([
        'allowed_values' => [
          1 => t('Available'),
          2 => t('Reserved'),
          3 => t('Sold'),
        ],
      ])
      ->setDefaultValue(1)
      ->setLabel(t('Status'))
      ->setDescription(t('Status of the flat.'))
      ->setRequired(TRUE)
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'options_select',
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }

}
