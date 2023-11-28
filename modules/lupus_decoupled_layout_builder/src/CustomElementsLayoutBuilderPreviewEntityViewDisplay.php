<?php

namespace Drupal\lupus_decoupled_layout_builder;

use drunomics\ServiceUtils\Core\Routing\CurrentRouteMatchTrait;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\custom_elements\CustomElementsLayoutBuilderEntityViewDisplay;
use Drupal\layout_builder\Context\LayoutBuilderContextTrait;

/**
 * Overrides the regular layout builder view display to provide sections.
 */
class CustomElementsLayoutBuilderPreviewEntityViewDisplay extends CustomElementsLayoutBuilderEntityViewDisplay {

  use CurrentRouteMatchTrait;
  use LayoutBuilderContextTrait;

  /**
   * {@inheritdoc}
   */
  protected function buildSections(FieldableEntityInterface $entity) {
    // Only do something if we are on our preview route. Else, continue with
    // the regular code flow.
    // @see \Drupal\lupus_decoupled_layout_builder\EventSubscriber\LupusDecoupledLayoutBuilderRouteSubscriber
    $route_match = $this->getCurrentRouteMatch();
    if (strpos($route_match->getRouteName(), 'lupus_decoupled_layout_builder.layout_builder.') !== 0) {
      return parent::buildSections($entity);
    }
    // Note that the route takes care of loading section storage from temp
    // store.
    $storage = $route_match->getParameter('section_storage');
    $contexts = $this->getContextsForEntity($entity) + $this->getPopulatedContexts($storage);

    $build = [];
    if ($storage) {
      foreach ($storage->getSections() as $delta => $section) {
        $build[$delta] = $section->toRenderArray($contexts);
      }
    }
    // Disable the cache for the preview.
    $build['#cache']['max-age'] = 0;
    return $build;
  }

}
