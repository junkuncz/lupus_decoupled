<?php

namespace Drupal\lupus_decoupled_responsive_preview\EventSubscriber;

use Drupal\lupus_decoupled_ce_api\BaseUrlProviderTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Response subscriber to alter content security for the preview in an iframe.
 *
 * This is required to allow the responsive-preview iframe to display frontend
 * pages.
 */
class ContentSecurityEventSubscriber implements EventSubscriberInterface {

  use BaseUrlProviderTrait;

  /**
   * Sets extra headers on successful responses.
   *
   * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
   *   The event to process.
   *
   * @throws \Exception
   */
  public function onRespond(ResponseEvent $event) {
    if (!$event->isMainRequest()) {
      return;
    }
    // Get all URLs for the front end; replace hostname parts that contain
    // underscores with '*'.
    $all_base_urls = $this->getBaseUrlProvider()->getAllFrontendBaseUrls() ?? [];
    foreach ($all_base_urls as &$url) {
      if (is_string($url) && str_contains($url, '_')) {
        $parsed_url = parse_url($url);
        $hostname_parts = explode('.', $parsed_url['host'] ?? '');
        foreach ($hostname_parts as &$part) {
          if (str_contains($url, '_')) {
            $part = '*';
          }
        }
        $scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
        $url = $scheme . implode('.', $hostname_parts)
          . (isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '');
      }
    }
    unset($url);
    unset($hostname_parts);
    $policy_urls = array_unique($all_base_urls);
    // Set the csp header.
    if (!empty($policy_urls)) {
      // Add self to urls.
      $policy_urls[] = "'self'";
      $this->setFrameAncestorsDirective($event->getResponse(), $policy_urls);
    }
  }

  /**
   * Registers the methods in this class that should be listeners.
   *
   * @return array
   *   An array of event listener definitions.
   */
  public static function getSubscribedEvents() {
    // Make sure this runs after SeckitEventSubscriber.
    $events[KernelEvents::RESPONSE][] = ['onRespond', -100];
    return $events;
  }

  /**
   * Adds frame-ancestors directive to CSP header.
   *
   * @param \Symfony\Component\HttpFoundation\Response $response
   *   The response.
   * @param array $policy_urls
   *   Array of urls.
   */
  protected function setFrameAncestorsDirective(Response $response, array $policy_urls) {
    $csp_header_values = [];
    // If seckit module already set the header, we need to add the directive.
    if ($response->headers->has('Content-Security-Policy')) {
      $value = $response->headers->get('Content-Security-Policy');
      // Remove frame-ancestors and frame-src directives coming from seckit.
      $regex = "/(frame-ancestors|frame-src)[^;]+(;|$)/";
      $value = preg_replace($regex, "", $value);
      $csp_header_values[] = preg_replace($regex, "", $value);
    }
    $csp_header_values[] = 'frame-ancestors ' . implode(' ', $policy_urls);
    $csp_header_values[] = 'frame-src ' . implode(' ', $policy_urls);
    $response->headers->set('Content-Security-Policy', implode('; ', $csp_header_values), TRUE);
  }

}
