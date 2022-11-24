<?php

namespace Drupal\lupus_decoupled_ce_api\PathProcessor;

use Drupal\Core\PathProcessor\OutboundPathProcessorInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Symfony\Component\HttpFoundation\Request;

/**
 * Processes outgoing paths.
 */
class LupusPreviewPathProcessor implements OutboundPathProcessorInterface {

  /**
   * {@inheritdoc}
   */
  public function processOutbound($path, &$options = [], Request $request = NULL, BubbleableMetadata $bubbleable_metadata = NULL) {
    // Ensure the frontend reaches out to Drupal to fetch the preview pages
    // by enabling authentication via query parameter auth=1.
    if ((strpos($path, 'node/preview') !== FALSE || strpos($path, '/layout-preview') !== FALSE) && !isset($options['query']['auth'])) {
      $options['query']['auth'] = 1;
    }
    return $path;
  }

}
