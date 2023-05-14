<?php

namespace Drupal\developer\Plugin\Field\FieldWidget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\EntityReferenceAutocompleteWidget;

/**
 * Plugin of the 'developer_entity_reference_autocomplete' widget.
 *
 * @FieldWidget(
 *   id = "developer_entity_reference_autocomplete",
 *   label = @Translation("Developer autocomplete"),
 *   description = @Translation("An autocomplete text field for Developer module entities."),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class DeveloperEntityReferenceAutocompleteWidget extends EntityReferenceAutocompleteWidget {

  /**
   * {@inheritdoc}
   *
   * @phpstan-ignore-next-line
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $element['target_id']['#type'] = 'developer_entity_autocomplete';
    return $element;
  }

}
