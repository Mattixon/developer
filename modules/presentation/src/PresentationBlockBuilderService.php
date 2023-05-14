<?php

namespace Drupal\developer_presentation;

use Drupal\Core\Url;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Block builder service.
 */
class PresentationBlockBuilderService {
  use StringTranslationTrait;

  /**
   * Returns block active tabs.
   */
  public function getBlockTabs(array $plugins_labels, array $plugins_configuration): array {
    /* If there is one or less label, return empty. */
    if (count($plugins_labels) < 2) {
      return [];
    }

    $tabs = [];

    foreach ($plugins_labels as $plugin_key => $label) {
      $is_plugin_enabled = FALSE;

      if (isset($plugins_configuration[$plugin_key])) {
        $is_plugin_enabled = (bool) $plugins_configuration[$plugin_key]['switch'];
      }

      if ($is_plugin_enabled) {
        $tabs[$plugin_key] = [
          '#type' => 'link',
          '#title' => $label,
          '#url' => Url::fromRoute('developer_presentation.presentation_change_tab', [
            'plugin_id' => $plugin_key,
            'block_id' => $plugins_configuration['block_id'],
          ]),
          '#attributes' => [
            'class' => [
              'developer-tab-btn',
              'use-ajax',
            ],
          ],
        ];

        if ($plugin_key === array_key_first($plugins_labels)) {
          $tabs[$plugin_key]['#attributes']['class'][] = 'active-tab';
        }
      }
    }

    /* If there is one or less active tab, return empty. */
    if (count($tabs) < 2) {
      return [];
    }

    return $tabs;
  }

  /**
   * Returns first active content.
   */
  public function getBlockContent(array $plugins_content, array $plugins_configuration): array {
    foreach ($plugins_content as $plugin_key => $content) {
      $is_plugin_enabled = FALSE;

      if (isset($plugins_configuration[$plugin_key])) {
        $is_plugin_enabled = (bool) $plugins_configuration[$plugin_key]['switch'];
      }

      if ($is_plugin_enabled) {
        return $plugins_content[$plugin_key];
      }
    }

    return [];
  }

}
