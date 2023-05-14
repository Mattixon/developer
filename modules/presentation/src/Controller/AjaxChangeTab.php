<?php

namespace Drupal\developer_presentation\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\developer_presentation\Manager\PresentationManager;

/**
 * Provides endpoint with ajax presentation change tab.
 */
class AjaxChangeTab extends ControllerBase {

  /**
   * Constructs a AjaxChangeTab object.
   */
  public function __construct(
    protected PresentationManager $presentationPluginManager,
    ConfigFactoryInterface $config_factory,
  ) {
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('plugin.manager.developer_presentation'),
      $container->get('config.factory'),
    );
  }

  /**
   * Ajax change tab function.
   */
  public function changeTab(string $plugin_id, string $block_id): AjaxResponse {
    /** @var array */
    $block_configuration = $this->configFactory->get('block.block.' . $block_id)->get();
    $plugin_configuration = $block_configuration['settings'][$plugin_id];
    /** @var \Drupal\developer_presentation\Plugin\Developer\PresentationInterface */
    $plugin = $this->presentationPluginManager->createInstance($plugin_id);
    $content = $plugin->getContent($plugin_configuration, $block_id);
    $developer_presentation_content = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'developer-presentation-content--' . $block_id,
      ],
      'content' => $content,
    ];

    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand('#developer-presentation-content--' . $block_id, $developer_presentation_content));

    return $response;
  }

}
