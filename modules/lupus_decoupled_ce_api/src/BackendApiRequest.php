<?php

namespace Drupal\lupus_decoupled_ce_api;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Middleware for admin API Urls.
 */
class BackendApiRequest implements HttpKernelInterface {

  /**
   * The wrapped kernel implementation.
   *
   * @var \Symfony\Component\HttpKernel\HttpKernelInterface
   */
  private $httpKernel;

  /**
   * An array of paths to redirect to the frontend.
   *
   * Contains '/ce-api' by default and can be configured
   * via service parameters.
   *
   * @var string
   */
  protected $apiPrefix;

  /**
   * Create a new StackOptionsRequest instance.
   *
   * @param \Symfony\Component\HttpKernel\HttpKernelInterface $httpKernel
   *   Http Kernel.
   * @param string $apiPrefix
   *   The api path prefix.
   */
  public function __construct(HttpKernelInterface $httpKernel, $apiPrefix) {
    $this->httpKernel = $httpKernel;
    $this->apiPrefix = $apiPrefix;
  }

  /**
   * {@inheritdoc}
   */
  public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = TRUE) {
    $uri = $request->server->get('REQUEST_URI');

    // If this request is against /ce-api then internally rewrite is as a request
    // to the non /ce-api path equivalent but with the custom elements formatter
    // enabled.
    // (e.g. /ce-api/xyz -> /xyz)
    $length = strlen($this->apiPrefix);
    if (substr($uri, 0, $length) === $this->apiPrefix) {
      // Remove the API-prefix.
      $new_uri = substr($uri, 4);
      // Apply new path by generating a new request.
      $new_request = $request->duplicate(
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        array_merge($request->server->all(), [
          'REQUEST_URI' => $new_uri,
        ])
      );
      $new_request->attributes->set('lupus_ce_renderer', TRUE);
      $new_request->headers->set('X-Original-Path', $uri);
      return $this->httpKernel->handle($new_request, $type, $catch);
    }
    else {
      return $this->httpKernel->handle($request, $type, $catch);
    }
  }

}
