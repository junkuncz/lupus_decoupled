<?php

namespace Drupal\lupus_decoupled_ce_api;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\StreamWrapper\PublicStream;
use Drupal\Core\Url;

/**
 * Provides base URLs.
 *
 * Main frontend URL is set in Lupus Decoupled Settings but can be overridden
 * with DRUPAL_FRONTEND_BASE_URL environment variable. Additional frontend urls
 * (used for CORS configuration can be added with
 * lupus_decoupled_ce_api.frontend_base_urls container parameter).
 */
class BaseUrlProvider {

  /**
   * The module config object.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

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
   * An array of frontend base urls.
   *
   * Used for setting cors and csp headers.
   *
   * @var string[]
   */
  protected $frontendBaseUrls;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param string $apiPrefix
   *   The api path prefix.
   * @param string[] $frontendBaseUrls
   *   The frontend base urls.
   */
  public function __construct(ConfigFactoryInterface $config_factory, string $apiPrefix, array $frontendBaseUrls) {
    $this->config = $config_factory->get('lupus_decoupled_ce_api.settings');
    $this->apiPrefix = $apiPrefix;
    $this->frontendBaseUrls = $frontendBaseUrls;
  }

  /**
   * Provides frontend base URL.
   *
   * For sites with more than one frontend site this method has to be
   * overridden with a decision-making mechanism to pick the right frontend
   * url.
   *
   * @param \Drupal\Core\Render\BubbleableMetadata|null $bubbleable_metadata
   *   (optional) If given, an object to attach cache metadata to.
   *
   * @return string|null
   *   Frontend base URL.
   */
  public function getFrontendBaseUrl(BubbleableMetadata $bubbleable_metadata = NULL) : string|null {
    if ($frontend_base_url = getenv('DRUPAL_FRONTEND_BASE_URL')) {
      return $frontend_base_url;
    }
    if (isset($bubbleable_metadata)) {
      $bubbleable_metadata->addCacheableDependency($this->config);
    }
    return $this->config->get('frontend_base_url') ?? NULL;
  }

  /**
   * Provides the list of all frontend urls.
   *
   * This parameter can be used to set cors or csp headers.
   *
   * @param \Drupal\Core\Render\BubbleableMetadata|null $bubbleable_metadata
   *   (optional) If given, an object to attach cache metadata to.
   *
   * @return string[]
   *   Array of frontend base urls.
   */
  public function getAllFrontendBaseUrls(BubbleableMetadata $bubbleable_metadata = NULL) {
    $frontend_base_urls = [$this->getFrontendBaseUrl($bubbleable_metadata)];
    if (!empty($this->frontendBaseUrls)) {
      $frontend_base_urls = array_unique(array_merge($frontend_base_urls, $this->frontendBaseUrls));
    }
    return $frontend_base_urls;
  }

  /**
   * Provides frontend base URL for entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity object to get the base URL for.
   * @param \Drupal\Core\Render\BubbleableMetadata|null $bubbleable_metadata
   *   (optional) If given, an object to attach cache metadata to.
   *
   * @return string|null
   *   Frontend base URL.
   */
  public function getFrontendBaseUrlForEntity(EntityInterface $entity, BubbleableMetadata $bubbleable_metadata = NULL) {
    $base_url = $this->getFrontendBaseUrl($bubbleable_metadata);
    if (isset($bubbleable_metadata)) {
      $bubbleable_metadata->addCacheableDependency($this->config);
    }
    // It's the same for all entities now but can be overwritten
    // in case multiple frontends are supported.
    return $base_url;
  }

  /**
   * Provides admin base URL.
   *
   * @param \Drupal\Core\Render\BubbleableMetadata|null $bubbleable_metadata
   *   (optional) If given, an object to attach cache metadata to.
   *
   * @return string
   *   Admin base URL.
   */
  public function getAdminBaseUrl(BubbleableMetadata $bubbleable_metadata = NULL) {
    $url_options = [
      'absolute' => TRUE,
      'language' => \Drupal::languageManager()->getCurrentLanguage(),
    ];
    $admin_base_url = Url::fromRoute('<front>', [], $url_options)->toString();
    return rtrim($admin_base_url, '/');
  }

  /**
   * Provides the CE-API base URL.
   *
   * @param \Drupal\Core\Render\BubbleableMetadata|null $bubbleable_metadata
   *   (optional) If given, an object to attach cache metadata to.
   *
   * @return string
   *   Api base URL.
   */
  public function getApiBaseUrl(BubbleableMetadata $bubbleable_metadata = NULL) {
    $admin_base_url = $this->getAdminBaseUrl($bubbleable_metadata);
    return $admin_base_url . $this->apiPrefix;
  }

  /**
   * Provides base URL for files.
   *
   * Adjust via settings.php 'file_public_base_url'.
   * See default.settings.php file.
   *
   * @param \Drupal\Core\Render\BubbleableMetadata|null $bubbleable_metadata
   *   (optional) If given, an object to attach cache metadata to.
   *
   * @return string
   *   Files base URL.
   */
  public function getFilesBaseUrl(BubbleableMetadata $bubbleable_metadata = NULL) {
    // We simply build upon Drupal's 'file_public_base_url' setting.
    return PublicStream::baseUrl();
  }

}
