<?php

namespace Drupal\developer_presentation\Plugin\Developer;

use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines the interface for presentation plugins.
 */
interface PresentationInterface extends ConfigurableInterface, PluginFormInterface {

  /**
   * Return presentation plugin label.
   */
  public function getLabel(): string|TranslatableMarkup;

  /**
   * Return presentation plugin content.
   */
  public function getContent(array $configuration, ?string $block_id): array;

}
