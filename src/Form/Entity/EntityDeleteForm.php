<?php

namespace Drupal\developer\Form\Entity;

use Drupal\Core\Form\FormStateInterface;
use Drupal\developer\Service\EntityService;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\ContentEntityDeleteForm;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting a developer entities.
 */
class EntityDeleteForm extends ContentEntityDeleteForm {

  /**
   * Constructs a EntityDeleteForm object.
   */
  public function __construct(
    EntityRepositoryInterface $entity_repository,
    EntityTypeBundleInfoInterface $entity_type_bundle_info,
    TimeInterface $time,
    protected EntityService $entityService
  ) {
    parent::__construct($entity_repository, $entity_type_bundle_info, $time);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('entity.repository'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time'),
      $container->get('developer.entity_service')
    );
  }

  /**
   * {@inheritdoc}
   *
   * Extended function entity delete, if it has any related entities.
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $entity_info = [];

    if ($this->entity->getEntityTypeId() !== 'developer_flat') {
      $entity_info = $this->entityService->relatedEntitiesInfo($this->entity);
    }

    /* Check if there is any related entities */
    if (isset($entity_info['ids_number']) && $entity_info['ids_number'] !== 0) {
      $form = [
        'error_related_entities' => [
          '#type' => 'html_tag',
          '#tag' => 'p',
          '#value' => $this->t('%type entity is used by @count %related. You can not remove this %type entity until you have removed all of the %related content.', [
            '%type' => $entity_info['type'],
            '@count' => $entity_info['ids_number'],
            '%related' => $entity_info['related'],
          ]),
        ],
      ];
    }

    if (empty($entity_info) || $entity_info['ids_number'] === 0) {
      $form = parent::buildForm($form, $form_state);
    }

    return $form;
  }

}
