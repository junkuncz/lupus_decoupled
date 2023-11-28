<?php

namespace Drupal\lupus_decoupled_layout_builder\EventSubscriber;

use drunomics\ServiceUtils\Core\Entity\EntityTypeManagerTrait;
use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\Routing\RouteCollection;

/**
 * Registers layout-preview routes.
 */
class LupusDecoupledLayoutBuilderRouteSubscriber extends RouteSubscriberBase {

  use EntityTypeManagerTrait;

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Register layout-preview routes (returning custom elements) for every
    // layout route.
    foreach ($this->getEntityTypeManager()->getDefinitions() as $entityTypeId => $entityType) {
      if ($layout_route = $collection->get("layout_builder.overrides.$entityTypeId.view")) {
        // Take the layout route, so we inherit all the temp store logic for
        // section storage. However, switch out the controller from the layout
        // builder form to a regular entity view and disable caching.
        // @see \Drupal\lupus_decoupled_layout_builder\CustomElementsLayoutBuilderPreviewEntityViewDisplay
        $layout_preview_route = clone $layout_route;
        $defaults = $layout_route->getDefaults();
        unset($defaults['_entity_form']);
        $defaults['_controller'] = '\Drupal\lupus_ce_renderer\Controller\CustomElementsController::entityView';
        $layout_preview_route->setDefaults($defaults);
        $layout_preview_route->setPath($layout_route->getPath() . '-preview');
        $layout_preview_route->setOption('no_cache', TRUE);
        $layout_preview_route->setRequirement('_format', 'custom_elements');
        $collection->add("lupus_decoupled_layout_builder.layout_builder.$entityTypeId.view", $layout_preview_route);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events = parent::getSubscribedEvents();

    // Run after
    // \Drupal\layout_builder\Routing\LayoutBuilderRoutes::getSubscribedEvents()
    $events[RoutingEvents::ALTER] = ['onAlterRoutes', -300];

    return $events;
  }

}
