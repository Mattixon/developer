<?php

namespace Drupal\developer\Entity\Flat;

use Drupal\developer\Entity\EntityTypeInterface;
use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Flat type configuration entity.
 *
 * @ConfigEntityType(
 *   id = "developer_flat_type",
 *   label = @Translation("Flat type"),
 *   label_collection = @Translation("Flat types"),
 *   label_singular = @Translation("flat type"),
 *   label_plural = @Translation("flat types"),
 *   label_count = @PluralTranslation(
 *     singular = "@count flat type",
 *     plural = "@count flat types",
 *   ),
 *   bundle_of = "developer_flat",
 *   config_prefix = "developer_flat",
 *   admin_permission = "administer flat entity",
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
 *     "add-form" = "/admin/developer/config/entities/flat/type/add",
 *     "edit-form" = "/admin/developer/config/entities/flat/type/{developer_flat_type}/edit",
 *     "delete-form" = "/admin/developer/config/entities/flat/type/{developer_flat_type}/delete",
 *     "collection" = "/admin/developer/config/entities/flat/types"
 *   },
 * )
 */
class FlatType extends ConfigEntityBundleBase implements EntityTypeInterface {

  /**
   * The FlatType ID.
   */
  protected string $id;

  /**
   * The FlatType label.
   */
  protected string $label;

  /**
   * The FlatType description.
   */
  protected string $description = '';

  /**
   * Default value of the 'Create new revision' checkbox of Flat type.
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
  public function setDescription(mixed $description): FlatType {
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
  public function setNewRevision(bool $new_revision): FlatType {
    $this->new_revision = $new_revision;
    return $this;
  }

}
