<?php

namespace Drupal\developer\Form\Entity;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for adding and editing a developer entities.
 */
class EntityForm extends ContentEntityForm {

  /**
   * Constructs a new EntityForm object.
   */
  public function __construct(
    EntityRepositoryInterface $entity_repository,
    EntityTypeBundleInfoInterface $entity_type_bundle_info,
    TimeInterface $time,
    protected DateFormatterInterface $dateFormatter
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
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state): array {
    $form = parent::form($form, $form_state);

    $form['#theme'] = ['entity_form'];
    $form['#tree'] = TRUE;
    $form['#attached']['library'][] = 'developer/entity-form';

    /* Advanced sidebar. */
    $form['advanced'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['entity-meta']],
      '#weight' => 99,
    ];

    /* Entity meta data */
    /** @var \Drupal\developer\Entity\EntityInterface */
    $entity = $this->entity;
    $form['meta'] = [
      '#attributes' => ['class' => ['entity-meta__header']],
      '#type' => 'container',
      '#group' => 'advanced',
      '#weight' => -100,
    ];
    if (isset($entity->status->value)) {
      $status = $form['status']['widget']['#options'][$entity->status->value];
      $form['meta']['published'] = [
        '#type' => 'html_tag',
        '#tag' => 'h3',
        '#value' => $status,
        '#attributes' => [
          'class' => ['entity-meta__title'],
        ],
      ];
    }
    $last_saved = $this->t('Not saved yet');
    if (!$entity->isNew()) {
      $last_saved = $this->dateFormatter->format($entity->getChangedTime(), 'short');
    }
    $form['meta']['changed'] = [
      '#type' => 'item',
      '#wrapper_attributes' => [
        'class' => ['entity-meta__last-saved', 'container-inline'],
      ],
      '#markup' => '<h4 class="label inline">' . $this->t('Last saved') . '</h4> ' . $last_saved,
    ];
    $form['meta']['author'] = [
      'author' => [
        '#type' => 'item',
        '#wrapper_attributes' => [
          'class' => ['author', 'container-inline'],
        ],
        '#markup' => '<h4 class="label inline">' . $this->t('Author') . '</h4> ' . $entity->getOwner()->getDisplayName(),
      ],
    ];

    /* Revision settings. */
    $form['revision_information']['#open'] = TRUE;

    /* Entity author information for administrators. */
    $form['authoring_information'] = [
      '#type' => 'details',
      '#title' => $this->t('Authoring information'),
      '#group' => 'advanced',
      '#weight' => 90,
      '#optional' => TRUE,
    ];
    if (isset($form['uid'])) {
      $form['uid']['#group'] = 'authoring_information';
    }
    if (isset($form['created'])) {
      $form['created']['#group'] = 'authoring_information';
    }

    /* Footer section. */
    $form['published']['#group'] = 'footer';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $status = parent::save($form, $form_state);

    $entity_type_id = $this->entity->getEntityTypeId();
    $entity_name = substr($entity_type_id, strpos($entity_type_id, "_") + 1);
    $redirect_route = "entity.{$entity_type_id}.collection";

    if ($status == SAVED_UPDATED) {
      $this->messenger()->addMessage($this->t('The @entity %feed has been updated.', [
        '@entity' => $entity_name,
        '%feed' => $this->entity->toLink()->toString(),
      ]));
    }
    else {
      $this->messenger()->addMessage($this->t('The @entity %feed has been added.', [
        '@entity' => $entity_name,
        '%feed' => $this->entity->toLink()->toString(),
      ]));
    }

    $form_state->setRedirect($redirect_route);

    return $status;
  }

}
