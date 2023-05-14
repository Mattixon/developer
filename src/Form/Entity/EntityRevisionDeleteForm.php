<?php

namespace Drupal\developer\Form\Entity;

use Drupal\Core\Url;
use Drupal\developer\Service\EntityService;
use Drupal\Core\Database\Connection;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\developer\Entity\EntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting a Developer entities revision.
 */
class EntityRevisionDeleteForm extends ConfirmFormBase {

  /**
   * The entity revision.
   */
  protected EntityInterface $revision;

  /**
   * Constructs a new EntityRevisionDeleteForm.
   */
  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected Connection $connection,
    protected DateFormatterInterface $dateFormatter,
    protected EntityService $entityService
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('database'),
      $container->get('date.formatter'),
      $container->get('developer.entity_service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'developer_' . $this->entityService->getEntityKeyWord() . '_revision_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the revision from %revision-date?', [
      '%revision-date' => $this->dateFormatter->format($this->revision->getRevisionCreationTime()),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.developer_' . $this->entityService->getEntityKeyWord() . '.version_history', ['developer_' . $this->entityService->getEntityKeyWord() => $this->revision->id()]);
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
  public function buildForm(
    array $form,
    FormStateInterface $form_state,
    EntityInterface $developer_estate_revision = NULL,
    EntityInterface $developer_building_revision = NULL,
    EntityInterface $developer_floor_revision = NULL,
    EntityInterface $developer_flat_revision = NULL
  ) {
    $revisions = [
      $developer_estate_revision,
      $developer_building_revision,
      $developer_floor_revision,
      $developer_flat_revision,
    ];

    foreach ($revisions as $revision) {
      if ($revision) {
        $this->revision = $revision;
        break;
      }
    }

    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    if ($this->revision->getRevisionId()) {
      $vid = (int) $this->revision->getRevisionId();
    }
    else {
      throw new \exception('The revision id can\'t be null.');
    }

    $this->entityTypeManager->getStorage('developer_' . $this->entityService->getEntityKeyWord())->deleteRevision($vid);

    $this->messenger()
      ->addStatus($this->t('Revision from %revision-date %title has been deleted.', [
        '%revision-date' => $this->dateFormatter->format($this->revision->getRevisionCreationTime()),
        '%title' => $this->revision->label(),
      ]));
    $form_state->setRedirect(
      'entity.developer_' . $this->entityService->getEntityKeyWord() . '.version_history',
      ['developer_' . $this->entityService->getEntityKeyWord() => $this->revision->id()]
    );
  }

}
