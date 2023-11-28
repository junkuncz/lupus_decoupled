<?php

namespace Drupal\lupus_decoupled_responsive_preview;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Url;
use Drupal\lupus_decoupled_ce_api\BaseUrlProviderTrait;
use Drupal\responsive_preview\ResponsivePreview;

/**
 * LupusDecoupledResponsivePreview service.
 *
 * This overrides ResponsivePreview service from responsive_preview module.
 */
class LupusDecoupledResponsivePreview extends ResponsivePreview {

  use BaseUrlProviderTrait;

  /**
   * {@inheritdoc}
   */
  public function getPreviewUrl() {
    $preview_url = parent::getPreviewUrl();
    $entityTypeId = $this->routeMatch->getRouteObject()->getDefault('entity_type_id');
    $routeName = $this->routeMatch->getRouteName();
    $parameters = iterator_to_array($this->routeMatch->getRawParameters());
    $entity = NULL;

    // Add support for preview of editing entities' individual layouts.
    if ($entityTypeId && $routeName == "layout_builder.overrides.$entityTypeId.view") {
      $entity = $this->entityTypeManager->getStorage($entityTypeId)->load($parameters[$entityTypeId]);
      $preview_url = Url::fromRoute("lupus_decoupled_layout_builder.layout_builder.$entityTypeId.view", $parameters, [])
        ->toString();
    }

    // Modify any preview URL to have the correct frontend base URL. This is
    // usually done by LupusFrontendPathProcessor, but that needs a URL object
    // which we don't always have here.
    if ($preview_url && !UrlHelper::isExternal($preview_url)) {
      $baseUrlProvider = $this->getBaseUrlProvider();
      if ($entity) {
        $frontend_url = $baseUrlProvider->getFrontendBaseUrlForEntity($entity);
      }
      else {
        $frontend_url = $baseUrlProvider->getFrontendBaseUrl();
      }
      $preview_url = $frontend_url . $preview_url;
    }
    return $preview_url;
  }

}
