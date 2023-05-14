<?php

namespace Drupal\developer\Entity\Estate;

use Drupal\developer\Entity\EntityTypeInterface;
use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Estate type configuration entity.
 *
 * @ConfigEntityType(
 *   id = "developer_estate_type",
 *   label = @Translation("Estate type"),
 *   label_collection = @Translation("Estate types"),
 *   label_singular = @Translation("estate type"),
 *   label_plural = @Translation("estate types"),
 *   label_count = @PluralTranslation(
 *     singular = "@count estate type",
 *     plural = "@count estate types",
 *   ),
 *   bundle_of = "developer_estate",
 *   config_prefix = "developer_estate",
 *   admin_permission = "administer estate entity",
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
 *     "add-form" = "/admin/developer/config/entities/estate/type/add",
 *     "edit-form" = "/admin/developer/config/entities/estate/type/{developer_estate_type}/edit",
 *     "delete-form" = "/admin/developer/config/entities/estate/type/{developer_estate_type}/delete",
 *     "collection" = "/admin/developer/config/entities/estate/types"
 *   },
 * )
 */
class EstateType extends ConfigEntityBundleBase implements EntityTypeInterface {

  /**
   * The EstateType ID.
   */
  protected string $id;

  /**
   * The EstateType label.
   */
  protected string $label;

  /**
   * The EstateType description.
   */
  protected string $description = '';

  /**
   * Default value of the 'Create new revision' checkbox of Estate type.
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
  public function setDescription(mixed $description): EstateType {
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
  public function setNewRevision(bool $new_revision): EstateType {
    $this->new_revision = $new_revision;
    return $this;
  }

}
