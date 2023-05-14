<?php

namespace Drupal\developer\Form\Entity;

use Drupal\Core\Url;
use Drupal\developer\Service\EntityService;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityConfirmFormBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Builds the form to delete Developer entity type.
 */
class EntityTypeDeleteForm extends EntityConfirmFormBase {

  /**
   * Constructs a EntityTypeDeleteForm object.
   */
  public function __construct(protected EntityService $entityService) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('developer.entity_service')
    );
  }

  /**
   * {@inheritdoc}
   *
   * Extended function block entity delete, if it has any related entities.
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $entity_type_info = [];

    if ($this->entity->getEntityTypeId() !== 'developer_flat') {
      $entity_type_info = $this->entityService->relatedEntitiesInfo($this->entity);
    }

    if (empty($entity_type_info) || $entity_type_info['ids_number'] === 0) {
      $form = parent::buildForm($form, $form_state);
    }
    else {
      $form = [
        '#markup' => $this->t('%type entity type is used by @count %related. You can not remove this %type entity type until you have removed all of the %related content.', [
          '%type' => $entity_type_info['type'],
          '@count' => $entity_type_info['ids_number'],
          '%related' => $entity_type_info['related'],
        ]),
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete %name?', ['%name' => $this->entity->label()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.developer_' . $this->entityService->getEntityKeyWord() . '_type.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->entity->delete();
    $this->messenger()->addMessage($this->t('Entity type %label has been deleted.', ['%label' => $this->entity->label()]));

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
