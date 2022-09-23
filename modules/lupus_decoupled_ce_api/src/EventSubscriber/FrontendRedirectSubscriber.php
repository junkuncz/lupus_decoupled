<?php

namespace Drupal\lupus_decoupled_ce_api\EventSubscriber;

use drunomics\ServiceUtils\Core\Routing\CurrentRouteMatchTrait;
use drunomics\ServiceUtils\Symfony\HttpFoundation\RequestStackTrait;
use Drupal\Core\Cache\CacheableResponseInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\lupus_decoupled_ce_api\BaseUrlProviderTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Redirects routes to the decoupled frontend when access without /ce-api.
 */
class FrontendRedirectSubscriber implements EventSubscriberInterface {

  use BaseUrlProviderTrait;
  use CurrentRouteMatchTrait;
  use RequestStackTrait;

  /**
   * The module config object.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * An array of routes to redirect to the frontend.
   *
   * Contains 'entity.node.canonical' and possibly others.
   *
   * If a new route is added to frontend_routes,
   * also add its path to lupus_decoupled_ce_api.frontend_paths.
   * You can register a new frontend route by implementing
   * a custom service provider.
   *
   * @var string[]
   */
  protected $frontendRoutes;

  /**
   * FrontendRedirectSubscriber constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param string[] $frontendRoutes
   *   The routes to redirect.
   */
  public function __construct(ConfigFactoryInterface $config_factory, array $frontendRoutes) {
    $this->config = $config_factory->get('lupus_decoupled_ce_api.settings');
    $this->frontendRoutes = $frontendRoutes;
  }

  /**
   * Redirects entity.node.canonical requests to the frontend base_url.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   The request event.
   */
  public function onKernelRequest(RequestEvent $event) {
    if ($this->getCurrentRequest()->getRequestFormat() != 'html') {
      // Do not redirect API responses.
      return;
    }

    if ($this->getBaseUrlProvider()->getFrontendBaseUrl() == NULL || !$this->config->get('frontend_routes_redirect')) {
      // Exit if frontend_base_url is not set or if frontend redirect is disabled.
      return;
    }

    if (in_array($this->getCurrentRouteMatch()->getRouteName(), $this->frontendRoutes)) {
      $route_match = $this->getCurrentRouteMatch();
      // For entity routes, get entity-specific frontend base URLs.
      if (preg_match('/entity\.[a-z]+\.canonical/', $route_match->getRouteName())) {
        $parameters = $route_match->getParameters()->all();
        $entity = reset($parameters);
        if ($entity && $entity instanceof EntityInterface) {
          $redirect_url = $this->getBaseUrlProvider()->getFrontendBaseUrlForEntity($entity) . $this->getCurrentRequest()->getRequestUri();
        }
      }
      if (!isset($redirect_url)) {
        $redirect_url = $this->getBaseUrlProvider()->getFrontendBaseUrl() . $this->getCurrentRequest()->getRequestUri();
      }
      $event->setResponse(new TrustedRedirectResponse($redirect_url));
      $event->stopPropagation();
    }
  }

  /**
   * Handles the response.
   *
   * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
   *   The event to process.
   */
  public function onResponse(ResponseEvent $event) {
    $response = $event->getResponse();
    if ($response instanceof CacheableResponseInterface) {
      $response->addCacheableDependency($this->config);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      KernelEvents::REQUEST => ['onKernelRequest', 0],
      KernelEvents::RESPONSE => ['onResponse', 5],
    ];
  }

}