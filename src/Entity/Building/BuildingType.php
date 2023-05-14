<?php

namespace Drupal\developer\Entity\Building;

use Drupal\developer\Entity\EntityTypeInterface;
use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Building type configuration entity.
 *
 * @ConfigEntityType(
 *   id = "developer_building_type",
 *   label = @Translation("Building type"),
 *   label_collection = @Translation("Building types"),
 *   label_singular = @Translation("building type"),
 *   label_plural = @Translation("building types"),
 *   label_count = @PluralTranslation(
 *     singular = "@count building type",
 *     plural = "@count building types",
 *   ),
 *   bundle_of = "developer_building",
 *   config_prefix = "developer_building",
 *   admin_permission = "administer building entity",
 *   handlers = {
 *     "list_builder" = "Drupal\developer\Entity\EntityTypeListBuilder",
 *     "form" = {
 *       "default" = "Drupal\developer\Form\Entity\EntityTypeForm",
 *       "delete" = "Drupal\developer\Form\Entity\EntityTypeDeleteForm",
 *     },
 *     "local_task_provider" = {
 *       "default" = "Drupal\entity\Menu\DefaultEntityLocalTaskProvider",
 *     },
 *     "route_provider" = {
 *       "default" = "Drupal\entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "description" = "description"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "description",
 *     "new_revision",
 *   },
 *   links = {
 *     "add-form" = "/admin/developer/config/entities/building/type/add",
 *     "edit-form" = "/admin/developer/config/entities/building/type/{developer_building_type}/edit",
 *     "delete-form" = "/admin/developer/config/entities/building/type/{developer_building_type}/delete",
 *     "collection" = "/admin/developer/config/entities/building/types"
 *   },
 * )
 */
class BuildingType extends ConfigEntityBundleBase implements EntityTypeInterface {

  /**
   * The BuildingType ID.
   */
  protected string $id;

  /**
   * The BuildingType label.
   */
  protected string $label;

  /**
   * The BuildingType description.
   */
  protected string $description = '';

  /**
   * Default value of the 'Create new revision' checkbox of Building type.
   */
  protected bool $new_revision = TRUE;

  /**
   * {@inheritdoc}
   */
  public function getDescription(): string {
    return $this->description;
  }

  /**
   * {@inheritdoc}
   */
  public function setDescription(mixed $description): BuildingType {
    $this->description = $description;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function shouldCreateNewRevision() {
    return $this->new_revision;
  }

  /**
   * {@inheritdoc}
   */
  public function setNewRevision(bool $new_revision): BuildingType {
    $this->new_revision = $new_revision;
    return $this;
  }

}
