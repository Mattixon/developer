<?php

namespace Drupal\developer_presentation\Manager;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Provides an Presentation plugin manager.
 */
class PresentationManager extends DefaultPluginManager {

  /**
   * Constructs a PresentationManager object.
   *
   * @phpstan-ignore-next-line
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/Developer',
      $namespaces,
      $module_handler,
      'Drupal\developer_presentation\Plugin\Developer\PresentationInterface',
      'Drupal\developer_presentation\Annotation\DeveloperPresentation'
    );
    $this->alterInfo('developer_presentation');
    $this->setCacheBackend($cache_backend, 'developer_presentation_plugins');
  }

}
