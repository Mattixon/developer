<?php

namespace Drupal\developer\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityInterface as CoreEntityInterface;
use Drupal\Core\Entity\EntityListBuilder as CoreEntityListBuilder;

/**
 * Provides a list controller for Developer entities.
 */
class EntityListBuilder extends CoreEntityListBuilder {

  /**
   * The date formatter service.
   */
  protected DateFormatterInterface $dateFormatter;

  /**
   * The language manager service.
   */
  protected LanguageManagerInterface $languageManager;

  /**
   * Constructs a new EntityListBuilder object.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, DateFormatterInterface $date_formatter, LanguageManagerInterface $language_manager) {
    parent::__construct($entity_type, $storage);
    $this->dateFormatter = $date_formatter;
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('date.formatter'),
      $container->get('language_manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader(): array {
    $header['name'] = $this->t('Name');

    if ($this->entityTypeId === 'developer_flat') {
      $header['status'] = $this->t('Status');
    }

    if ($this->entityTypeId !== 'developer_estate') {
      $entity_map = [
        'developer_flat' => $this->t('Floor'),
        'developer_floor' => $this->t('Building'),
        'developer_building' => $this->t('Estate'),
      ];
      $header['related'] = $entity_map[$this->entityTypeId];
    }

    $header['type'] = $this->t('Type');
    $header['author'] = $this->t('Author');
    $header['published'] = $this->t('Publication');
    $header['changed'] = $this->t('Updated');

    if ($this->languageManager->isMultilingual()) {
      $header['language_name'] = $this->t('Language');
    }

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(CoreEntityInterface $entity): array {
    /** @var \Drupal\developer\Entity\EntityInterface $entity */
    $row['name'] = $entity->toLink();

    if ($this->entityTypeId === 'developer_flat') {
      $status_definition = $entity->getFieldDefinition('status');
      /** @var array */
      $status_values = $status_definition ? $status_definition->getSetting('allowed_values') : NULL;

      $row['status'] = $status_values[$entity->status->value];
    }

    if ($this->entityTypeId !== 'developer_estate') {
      $entity_map = [
        'developer_flat' => 'floor_id',
        'developer_floor' => 'building_id',
        'developer_building' => 'estate_id',
      ];

      $related_entity = $entity->get($entity_map[$this->entityTypeId])->entity;
      $row['related'] = $related_entity instanceof EntityInterface ? $related_entity->toLink() : NULL;
    }

    $row['type'] = $entity->bundle();
    $row['author']['data'] = [
      '#theme' => 'username',
      '#account' => $entity->getOwner(),
    ];
    $row['published'] = $entity->isPublished() ? $this->t('published') : $this->t('not published');
    $row['changed'] = $this->dateFormatter->format($entity->getChangedTime(), 'short');

    if ($this->languageManager->isMultilingual()) {
      $row['language_name'] = $this->languageManager->getLanguageName($entity->language()->getId());
    }

    return $row + parent::buildRow($entity);
  }

}
