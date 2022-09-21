<?php

namespace Drupal\lupus_decoupled_ce_api;

/**
 * Allows setter injection and simple usage of the service.
 */
trait BaseUrlProviderTrait {

  /**
   * The base URL provider.
   *
   * @var \Drupal\lupus_decoupled_ce_api\BaseUrlProvider
   */
  protected $baseUrlProvider;

  /**
   * Sets the base URL provider.
   *
   * @param \Drupal\lupus_decoupled_ce_api\BaseUrlProvider $baseUrlProvider
   *   The base URL provider.
   *
   * @return $this
   */
  public function setBaseUrlProvider(BaseUrlProvider $baseUrlProvider) {
    $this->baseUrlProvider = $baseUrlProvider;
    return $this;
  }

  /**
   * Gets the base URL provider.
   *
   * @return \Drupal\lupus_decoupled_ce_api\BaseUrlProvider
   *   The base URL provider.
   */
  public function getBaseUrlProvider() {
    if (empty($this->baseUrlProvider)) {
      $this->baseUrlProvider = \Drupal::service('lupus_decoupled_ce_api.base_url_provider');
    }
    return $this->baseUrlProvider;
  }

}
