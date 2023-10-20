<?php

/**
 * @file
 * Hooks specific to the lupus_decoupled_views module.
 */

use Drupal\custom_elements\CustomElement;

/**
 * Allows altering Views before they are rendered.
 *
 * @param \Drupal\custom_elements\CustomElement $custom_element
 *   The custom element representing the View.
 */
function hook_lupus_decoupled_views_page_alter(CustomElement $custom_element) {
  $custom_element->setAttribute('title', 'My custom title');
}
