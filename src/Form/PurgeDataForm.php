<?php

namespace Drupal\developer\Form;

use Drupal\Core\Url;
use Drupal\developer\Service\EntityService;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form used to delete all module data.
 */
class PurgeDataForm extends ConfirmFormBase {

  /**
   * Entity list.
   */
  public const ENTITY_TYPES = [
    'developer_flat',
    'developer_floor',
    'developer_building',
    'developer_estate',
  ];

  /**
   * Constructs a new PurgeDataForm.
   */
  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected DateFormatterInterface $dateFormatter,
    protected EntityService $entityService
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('date.formatter'),
      $container->get('developer.entity_service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'developer_data_purge';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t("Are you sure you want purge all module's data?");
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t("Purge all entities and related media in preparation for uninstalling the <i>Developer</i> module. <strong>Remember! This action cannot be undone.</strong>");
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('developer.admin');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    foreach (self::ENTITY_TYPES as $type) {
      /* Delete entities of given type */
      $entity_storage = $this->entityTypeManager->getStorage($type);
      $entities = $entity_storage->loadMultiple();
      $entity_storage->delete($entities);

      /* Delete media of given type */
      $media_storage = $this->entityTypeManager->getStorage('media');
      $result = $media_storage
        ->getQuery()
        ->condition('bundle', $type, '=')
        ->execute();
      $entity_all_media = $media_storage->loadMultiple($result);
      $media_storage->delete($entity_all_media);
    }

    $this->messenger()->addMessage($this->t("All module's data has been purged. You're now free to uninstall it."));
    $form_state->setRedirect('developer.admin');
  }

}
