<?php

namespace Drupal\lupus_decoupled_views\Controller;

use drunomics\ServiceUtils\Core\Render\RendererTrait;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\custom_elements\CustomElement;
use Drupal\views\ViewExecutable;
use Drupal\views\Views;

/**
 * Controller for decoupled Views.
 */
class ViewsController {

  use RendererTrait;

  /**
   * Renders Views pages into custom elements.
   *
   * @param string $view_id
   *   The ID of the view
   * @param string $display_id
   *   The ID of the display.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match object.
   *
   * @return \Drupal\custom_elements\CustomElement
   */
  public function viewsView(string $view_id, string $display_id, RouteMatchInterface $route_match) {
    $args = [];
    $route = $route_match->getRouteObject();
    $map = $route->hasOption('_view_argument_map') ? $route->getOption('_view_argument_map') : [];

    foreach ($map as $attribute => $parameter_name) {
      // Allow parameters be pulled from the request.
      // The map stores the actual name of the parameter in the request. Views
      // which override existing controller, use for example 'node' instead of
      // arg_nid as name.
      if (isset($map[$attribute])) {
        $attribute = $map[$attribute];
      }
      if ($arg = $route_match->getRawParameter($attribute)) {
      }
      else {
        $arg = $route_match->getParameter($attribute);
      }

      if (isset($arg)) {
        $args[] = $arg;
      }
    }

    // Build and execute the view.
    $view = Views::getView($view_id);
    $result = $view->executeDisplay($display_id, $args);

    // Build a view as a custom element.
    $custom_element = new CustomElement();
    $custom_element->setTag('drupal-view');
    $custom_element->setAttribute('title', $view->build_info['title']);
    $custom_element->setAttribute('view_id', $view_id);
    $custom_element->setAttribute('display_id', $display_id);
    $custom_element->setAttribute('args', $args);
    $custom_element->setAttribute('pager', $result['#rows']['pager']);
    $custom_element->setSlotFromNestedElements('rows', $result['#rows']['rows']);
    $custom_element->addCacheableDependency(BubbleableMetadata::createFromRenderArray($result));
    return $custom_element;
  }

}
