<?php

namespace Drupal\lupus_decoupled_block\EventSubscriber;

use Drupal\lupus_decoupled_block\LupusDecoupledBlockRenderer;
use Drupal\lupus_ce_renderer\Cache\CustomElementsJsonResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Lupus decoupled block event subscriber.
 */
class LupusDecoupledBlockSubscriber implements EventSubscriberInterface {

  /**
   * The lupus_decoupled_block.renderer service.
   *
   * @var \Drupal\lupus_decoupled_block\LupusDecoupledBlockRenderer
   */
  protected $renderer;

  /**
   * Constructor function.
   *
   * @param \Drupal\lupus_decoupled_block\LupusDecoupledBlockRenderer $renderer
   *   Renderer.
   */
  public function __construct(LupusDecoupledBlockRenderer $renderer) {
    $this->renderer = $renderer;
  }

  /**
   * Kernel response event handler.
   *
   * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
   *   Response event.
   *
   * @see \Drupal\lupus_ce_renderer\CustomElementsRenderer::getDynamicContent()
   */
  public function onResponse(ResponseEvent $event) {
    $response = $event->getResponse();
    // Do not add dynamic data to redirects.
    if ($response instanceof CustomElementsJsonResponse && !$response->isRedirect()) {
      $response_data = $response->getResponseData();
      $response->setData($response_data + [
        'blocks' => $this->renderer->getBlocks(),
      ]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // Runs before DynamicPageCacheSubscriber::onRespond(),
    // which has priority 100.
    return [
      KernelEvents::RESPONSE => ['onResponse', 101],
    ];
  }

}
