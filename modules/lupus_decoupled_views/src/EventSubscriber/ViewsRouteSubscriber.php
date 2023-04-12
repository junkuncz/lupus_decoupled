<?php

namespace Drupal\lupus_decoupled_views\EventSubscriber;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\Routing\RouteCollection;

/**
 * Alters routes.
 */
class ViewsRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Mark the provided route to render into custom_elements.
    foreach ($collection as $name => $route) {
      if (mb_substr($name, 0, 5) === 'view.') {
        $ce_route_display_id = $route->getOption('_view_display_plugin_id');
        if ($ce_route_display_id === 'custom_elements_page') {
          $route->setRequirement('_format', 'custom_elements');
          $route->setDefault('_controller', '\Drupal\lupus_decoupled_views\Controller\ViewsController::viewsView');
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events[RoutingEvents::ALTER] = ['onAlterRoutes', 0];
    return $events;
  }

}
