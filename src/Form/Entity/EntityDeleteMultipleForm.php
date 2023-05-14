<?php

namespace Drupal\developer\Form\Entity;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Entity\Form\DeleteMultipleForm;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\developer\Entity\Flat\Flat;
use Drupal\developer\Service\EntityService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an entities deletion confirmation form.
 */
class EntityDeleteMultipleForm extends DeleteMultipleForm {

  /**
   * Constructs a new EntityDeleteMultipleForm object.
   */
  public function __construct(
    AccountInterface $current_user,
    EntityTypeManagerInterface $entity_type_manager,
    PrivateTempStoreFactory $temp_store_factory,
    MessengerInterface $messenger,
    protected EntityService $entityService
  ) {
    parent::__construct($current_user, $entity_type_manager, $temp_store_factory, $messenger);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('current_user'),
      $container->get('entity_type.manager'),
      $container->get('tempstore.private'),
      $container->get('messenger'),
      $container->get('developer.entity_service')
    );
  }

  /**
   * {@inheritdoc}
   *
   * @param array $form
   *   The form render array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   * @param string $entity_type_id
   *   The entity type id.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $entity_type_id = NULL): array {
    /** @var array */
    $selection = $this->tempStore->get($this->currentUser->id() . ':' . $entity_type_id);
    /** @var string $entity_type_id */
    $entities = $this->entityTypeManager->getStorage($entity_type_id)->loadMultiple(array_keys($selection));
    $any_related_entities = FALSE;
    $type = '';

    foreach ($entities as $entity) {
      if ($entity instanceof Flat) {
        break;
      }

      $entity_info = $this->entityService->relatedEntitiesInfo($entity);

      if ($entity_info['ids_number']) {
        $any_related_entities = TRUE;
        $type = $entity_info['type'];
        break;
      }
    }

    if (!$any_related_entities) {
      $form = parent::buildForm($form, $form_state, $entity_type_id);
    }
    else {
      $form = [
        '#markup' => $this->t('At least one selected %type entity is used. You need to delete related content first.', [
          '%type' => $type,
        ]),
      ];
    }

    return $form;
  }

}
