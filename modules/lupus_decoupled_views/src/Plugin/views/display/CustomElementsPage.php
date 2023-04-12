<?php

namespace Drupal\lupus_decoupled_views\Plugin\views\display;

use Drupal\views\Plugin\views\display\Page;

/**
 * The plugin that handles a full page for a custom elements view.
 *
 * @ingroup views_display_plugins
 *
 * @ViewsDisplay(
 *   id = "custom_elements_page",
 *   title = @Translation("Custom Elements Page"),
 *   help = @Translation("Display the view as page rendered with the custom_elements format."),
 *   uses_menu_links = TRUE,
 *   uses_route = TRUE,
 *   contextual_links_locations = {"custom_elements_page"},
 *   theme = "views_view",
 *   admin = @Translation("Custom Elements Page")
 * )
 */
class CustomElementsPage extends Page {

}
