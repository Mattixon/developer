<?php

namespace Drupal\developer\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\Element\EntityAutocomplete;

/**
 * Provides an Developer entity autocomplete form element.
 *
 * Field extend basic entity_autocomplete form element
 * by restrict to not final floor in floor reference and
 * rebuild entity preview by adding extra reference info
 * if they are any.
 *
 * @FormElement("developer_entity_autocomplete")
 */
class DeveloperEntityAutocomplete extends EntityAutocomplete {

  /**
   * Change route serving autocomplete.
   */
  public static function processEntityAutocomplete(array &$element, FormStateInterface $form_state, array &$complete_form) {
    $element = parent::processEntityAutocomplete($element, $form_state, $complete_form);
    $element['#autocomplete_route_name'] = 'developer.entity_autocomplete';

    return $element;
  }

}
