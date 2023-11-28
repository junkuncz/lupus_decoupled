<?php

namespace Drupal\lupus_decoupled_responsive_preview;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Modifies the responsive preview service.
 */
class LupusDecoupledResponsivePreviewServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    if ($definition = $container->getDefinition('responsive_preview')) {
      $definition->setClass('Drupal\lupus_decoupled_responsive_preview\LupusDecoupledResponsivePreview');
    }
  }

}
