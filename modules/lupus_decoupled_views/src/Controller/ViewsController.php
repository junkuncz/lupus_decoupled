<?php

namespace Drupal\lupus_decoupled_views\Controller;

use drunomics\ServiceUtils\Core\Render\RendererTrait;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\custom_elements\CustomElement;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for decoupled Views.
 */
class ViewsController extends ControllerBase {

  use RendererTrait;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a ViewsController object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   */
  public function __construct(ModuleHandlerInterface $module_handler) {
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_handler')
    );
  }

  /**
   * Renders Views pages into custom elements.
   *
   * @param string $view_id
   *   The ID of the view.
   * @param string $display_id
   *   The ID of the display.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match object.
   *
   * @return \Drupal\custom_elements\CustomElement
   *   Return Custom element object.
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
    $custom_element->setAttribute('title', $view->getTitle());
    $custom_element->setAttribute('view_id', $view_id);
    $custom_element->setAttribute('display_id', $display_id);
    $custom_element->setAttribute('args', $args);
    $custom_element->setAttribute('pager', $result['#rows']['pager'] ?? []);
    $custom_element->setSlotFromNestedElements('rows', $result['#rows']['rows'] ?? []);
    $custom_element->addCacheableDependency(BubbleableMetadata::createFromRenderArray($result));

    // Allow other modules to change the custom element without replacing the
    // entire method.
    $this->moduleHandler()->alter('lupus_decoupled_views_page_alter', $custom_element);

    return $custom_element;
  }

}
