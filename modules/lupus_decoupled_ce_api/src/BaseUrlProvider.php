<?php

namespace Drupal\lupus_decoupled_ce_api;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\StreamWrapper\PublicStream;
use Drupal\Core\Url;

/**
 * Provides base URLs.
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
   * Constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param string $apiPrefix
   *   The api path prefix.
   */
  public function __construct(ConfigFactoryInterface $config_factory, string $apiPrefix) {
    $this->config = $config_factory->get('lupus_decoupled_ce_api.settings');
    $this->apiPrefix = $apiPrefix;
  }

  /**
   * Provides frontend base URL.
   *
   * @param \Drupal\Core\Render\BubbleableMetadata|null $bubbleable_metadata
   *   (optional) If given, an object to attach cache metadata to.
   *
   * @return string|null
   *   Frontend base URL.
   */
  public function getFrontendBaseUrl(BubbleableMetadata $bubbleable_metadata = NULL) {
    if (isset($bubbleable_metadata)) {
      $bubbleable_metadata->addCacheDependency($this->config);
    }
    return $this->config->get('frontend_base_url');
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
    $base_url = $this->config->get('frontend_base_url');
    if (isset($bubbleable_metadata)) {
      $bubbleable_metadata->addCacheDependency($this->config);
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
