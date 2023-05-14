<?php

namespace Drupal\developer\Form\Entity;

use Drupal\Core\Form\FormStateInterface;
use Drupal\developer\Service\EntityService;
use Drupal\Core\Entity\BundleEntityFormBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Builds the form to create Developer entity type.
 */
class EntityTypeForm extends BundleEntityFormBase {

  /**
   * Constructs a EntityTypeForm object.
   */
  public function __construct(
    protected EntityService $entityService,
    protected EntityDisplayRepositoryInterface $displayRepository,
    ModuleHandlerInterface $module_handler,
  ) {
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('developer.entity_service'),
      $container->get('entity_display.repository'),
      $container->get('module_handler'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state): array {
    $form = parent::form($form, $form_state);
    /** @var \Drupal\developer\Entity\EntityTypeInterface */
    $entity = $this->entity;

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#maxlength' => 255,
      '#default_value' => $entity->label(),
      '#required' => TRUE,
    ];
    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $entity->id(),
      '#disabled' => !$this->entity->isNew(),
      '#machine_name' => [
        'exists' => [$this, 'exist'],
      ],
    ];
    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#description' => $this->t('This text will be displayed on the <em>Add %label</em> page.', ['%label' => $this->entityService->getEntityKeyWord()]),
      '#default_value' => $entity->getDescription(),
    ];
    $form['new_revision'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Create new revision'),
      '#default_value' => $entity->shouldCreateNewRevision(),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $status = $this->entity->save();
    /** @var string */
    $bundle = $this->entity->id();
    $this->displayRepository->getFormDisplay('developer_' . $this->entityService->getEntityKeyWord(), $bundle)->save();
    $this->displayRepository->getViewDisplay('developer_' . $this->entityService->getEntityKeyWord(), $bundle)->save();
    $this->moduleHandler->invokeAll('entity_bundle_after_create', ['developer_' . $this->entityService->getEntityKeyWord()]);

    if ($status === SAVED_NEW) {
      $this->messenger()->addMessage($this->t('The %label %type type created.', [
        '%label' => $this->entity->label(),
        '%type' => $this->entityService->getEntityKeyWord(),
      ]));
    }
    else {
      $this->messenger()->addMessage($this->t('The %label %type type updated.', [
        '%label' => $this->entity->label(),
        '%type' => $this->entityService->getEntityKeyWord(),
      ]));
    }

    $form_state->setRedirect('entity.developer_' . $this->entityService->getEntityKeyWord() . '_type.collection');

    return $status;
  }

  /**
   * Check whether an entity type configuration exists.
   */
  public function exist(string $id): bool {
    $entity = $this->entityTypeManager->getStorage('developer_' . $this->entityService->getEntityKeyWord() . '_type')->getQuery()
      ->condition('id', $id)
      ->execute();
    return (bool) $entity;
  }

}
