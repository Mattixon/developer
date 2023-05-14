<?php

namespace Drupal\developer\Entity\Floor;

use Drupal\developer\Entity\EntityTypeInterface;
use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Floor type configuration entity.
 *
 * @ConfigEntityType(
 *   id = "developer_floor_type",
 *   label = @Translation("Floor type"),
 *   label_collection = @Translation("Floor types"),
 *   label_singular = @Translation("floor type"),
 *   label_plural = @Translation("floor types"),
 *   label_count = @PluralTranslation(
 *     singular = "@count floor type",
 *     plural = "@count floor types",
 *   ),
 *   bundle_of = "developer_floor",
 *   config_prefix = "developer_floor",
 *   admin_permission = "administer floor entity",
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
 *     "add-form" = "/admin/developer/config/entities/floor/type/add",
 *     "edit-form" = "/admin/developer/config/entities/floor/type/{developer_floor_type}/edit",
 *     "delete-form" = "/admin/developer/config/entities/floor/type/{developer_floor_type}/delete",
 *     "collection" = "/admin/developer/config/entities/floor/types"
 *   },
 * )
 */
class FloorType extends ConfigEntityBundleBase implements EntityTypeInterface {

  /**
   * The FloorType ID.
   */
  protected string $id;

  /**
   * The FloorType label.
   */
  protected string $label;

  /**
   * The FloorType description.
   */
  protected string $description = '';

  /**
   * Default value of the 'Create new revision' checkbox of Floor type.
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
  public function setDescription(mixed $description): FloorType {
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
  public function setNewRevision(bool $new_revision): FloorType {
    $this->new_revision = $new_revision;
    return $this;
  }

}
