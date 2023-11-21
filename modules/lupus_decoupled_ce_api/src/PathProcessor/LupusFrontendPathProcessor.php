<?php

namespace Drupal\lupus_decoupled_ce_api\PathProcessor;

use drunomics\ServiceUtils\Core\Config\ConfigFactoryTrait;
use drunomics\ServiceUtils\Core\Routing\CurrentRouteMatchTrait;
use Drupal\Core\PathProcessor\OutboundPathProcessorInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\lupus_decoupled_ce_api\BaseUrlProviderTrait;
use Drupal\path_alias\AliasManagerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Processes paths of non-API (admin UI) responses to point to the frontend.
 */
class LupusFrontendPathProcessor implements OutboundPathProcessorInterface {

  use BaseUrlProviderTrait;
  use ConfigFactoryTrait;
  use CurrentRouteMatchTrait;

  /**
   * An array of paths to redirect to the frontend.
   *
   * Contains '/node/{node}' and possibly others. Can be configured
   * via service parameters.
   *
   * If a new path is added to frontend_path, also add its route
   * to lupus_decoupled_ce_api.frontend_routes.
   *
   * @var string[]
   */
  protected $frontendPaths;

  /**
   * Path Alias Manager service.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * FrontendRedirectSubscriber constructor.
   *
   * @param string[] $frontendPaths
   *   The paths to redirect.
   * @param \Drupal\path_alias\AliasManagerInterface $aliasManager
   *   Path Alias Manager service.
   */
  public function __construct(array $frontendPaths, AliasManagerInterface $aliasManager) {
    $this->frontendPaths = $frontendPaths;
    $this->aliasManager = $aliasManager;
  }

  /**
   * {@inheritdoc}
   */
  public function processOutbound($path, &$options = [], Request $request = NULL, BubbleableMetadata $bubbleable_metadata = NULL) {
    if (!isset($options['route'])) {
      return $path;
    }
    if (in_array($options['route']->getPath(), $this->frontendPaths) && isset($options['entity']) &&
      $request && $request->getRequestFormat() != 'custom_elements') {

      $options['base_url'] = $this->getBaseUrlProvider()->getFrontendBaseUrlForEntity($options['entity'], $bubbleable_metadata);
      $options['absolute'] = TRUE;
    }
    return $path;
  }

}
