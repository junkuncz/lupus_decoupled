<?php

namespace Drupal\lupus_decoupled_cors;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\Core\DependencyInjection\ServiceProviderInterface;

/**
 * Alters the service container.
 */
class LupusDecoupledCorsServiceProvider extends ServiceProviderBase implements ServiceProviderInterface {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Apply CORS settings but avoid overwriting things if possible. That way
    // it is still possible to customize settings per project.
    $existing = $container->getParameter('cors.config');

    $new_config = $existing;
    $new_config['enabled'] = TRUE;
    $new_config['supportsCredentials'] = TRUE;
    $new_config['maxAge'] = !empty($existing['maxAge']) ? $existing['maxAge'] : 7200;

    // Only append to existing configuration to allow pre-setting more.
    $new_config['allowedHeaders'][] = 'authorization';
    $new_config['allowedHeaders'][] = 'cache-control';
    $new_config['allowedHeaders'][] = 'pragma';
    $new_config['allowedMethods'][] = 'GET';
    $new_config['allowedMethods'][] = 'POST';
    if ($frontend_base_urls = $container->getParameter('lupus_decoupled_ce_api.frontend_base_urls')) {
      $new_config['allowedOrigins'] = array_merge($new_config['allowedOrigins'] ?? [], $frontend_base_urls);
    }

    // Add support for localhost access when the app is in development mode.
    if (getenv('PHAPP_ENV_MODE') == 'development') {
      $new_config['allowedOriginsPatterns'][] = '/:\/\/localhost:/';
    }
    $container->setParameter('cors.config', $new_config);

    // Support setting samesite cookie parameter to none via env-vars.
    if (getenv('URL_SCHEME') !== 'http' && getenv('LUPUS_DECOUPLED_CORS_DISABLE_SAME_SITE_COOKIE')) {
      $options = $container->getParameter('session.storage.options');
      $options['cookie_samesite'] = 'None';
      $container->setParameter('session.storage.options', $options);
    }
  }

}
