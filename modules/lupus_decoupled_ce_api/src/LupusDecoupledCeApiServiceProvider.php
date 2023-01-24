<?php

namespace Drupal\lupus_decoupled_ce_api;

use Drupal\Core\Config\BootstrapConfigStorageFactory;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\Core\DependencyInjection\ServiceProviderInterface;

/**
 * Adds frontend base urls container parameter.
 *
 * DRUPAL_FRONTEND_BASE_URL environment variable has priority over
 * configuration. Additional frontend urls are appended with container
 * parameter. This allows add-on modules to add support for more frontends.
 */
class LupusDecoupledCeApiServiceProvider extends ServiceProviderBase implements ServiceProviderInterface {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Can not use lupus_decoupled_ce_api.base_url_provider service because
    // the service container is not compiled yet.
    if ($frontend_base_url = getenv('DRUPAL_FRONTEND_BASE_URL')) {
      $frontend_base_urls = [$frontend_base_url];
    }
    else {
      // Config factory service is not available yet. Bootstrap config is
      // missing the overrides.
      $config_factory = BootstrapConfigStorageFactory::get();
      $settings = $config_factory->read('lupus_decoupled_ce_api.settings');
      $frontend_base_urls = [$settings['frontend_base_url']];
    }

    if ($parameter = $container->getParameter('lupus_decoupled_ce_api.frontend_base_urls')) {
      $frontend_base_urls = array_unique(array_merge($frontend_base_url, $parameter));
    }
    // Set frontend urls parameter to be consumed by other services.
    $container->setParameter('lupus_decoupled_ce_api.frontend_base_urls', $frontend_base_urls);
  }

}
