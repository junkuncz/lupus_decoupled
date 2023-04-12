<?php

namespace Drupal\lupus_decoupled_ce_api;

use Symfony\Component\HttpFoundation\Response;
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
   * The CE content format.
   *
   * @var string
   */
  protected $contentFormat;

  /**
   * Create a new StackOptionsRequest instance.
   *
   * @param \Symfony\Component\HttpKernel\HttpKernelInterface $httpKernel
   *   Http Kernel.
   * @param string $apiPrefix
   *   The api path prefix.
   * @param string $contentFormat
   *   (optional) The content format to apply. Defaults to 'json'.
   */
  public function __construct(HttpKernelInterface $httpKernel, string $apiPrefix, string $contentFormat = 'json') {
    $this->httpKernel = $httpKernel;
    $this->apiPrefix = $apiPrefix;
    $this->contentFormat = $contentFormat;
  }

  /**
   * {@inheritdoc}
   */
  public function handle(Request $request, $type = self::MAIN_REQUEST, $catch = TRUE): Response {
    $uri = $request->server->get('REQUEST_URI');

    // If this request is against /ce-api then internally
    // rewrite is as a request
    // to the non /ce-api path equivalent but with the custom elements formatter
    // enabled.
    // (e.g. /ce-api/xyz -> /xyz)
    // Check if on homepage(compare uri with apiPrefix without trailing slash)
    // in order to add the trailing slash to the uri.
    if ($uri == $this->apiPrefix) {
      $uri = $uri . '/';
    }
    // If homepage without trailing slash, but with query set, it won't match
    // above so check here and insert slash in between. 
    if (strtok($uri, '?') == $this->apiPrefix) {
      $query = $request->server->get('QUERY_STRING');
      $uri = str_replace('?' . $query, '/?' . $query, $uri);
    }
    // Add trailing slash to apiPrefix so that requests don't work
    // without a "/" separator.
    $apiPrefixSlash = $this->apiPrefix . '/';
    $length = strlen($apiPrefixSlash);
    if (substr($uri, 0, $length) === $apiPrefixSlash) {
      // Remove the API-prefix.
      $new_uri = substr($uri, strlen($apiPrefixSlash));
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
      $new_request->attributes->set('lupus_ce_renderer.content_format', $this->contentFormat);
      // Merge headers to not lose custom headers set in the object.
      $new_request->headers->add($request->headers->all() + $new_request->headers->all());
      $new_request->headers->set('X-Original-Path', $uri);
      return $this->httpKernel->handle($new_request, $type, $catch);
    }
    else {
      return $this->httpKernel->handle($request, $type, $catch);
    }
  }

}
