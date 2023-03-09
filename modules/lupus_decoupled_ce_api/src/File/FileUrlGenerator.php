<?php

declare(strict_types = 1);

namespace Drupal\lupus_decoupled_ce_api\File;

use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Generates absolute file URLs.
 *
 * @see https://www.drupal.org/node/2669074
 */
class FileUrlGenerator implements FileUrlGeneratorInterface {

  /**
   * Original file url generator service.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * Lupus decoupled ce-api configuration.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $lupusDecoupledCeApiSettings;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

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
   * Constructs a new Lupus decoupled ce-api file URL generator object.
   *
   * @param \Drupal\Core\File\FileUrlGeneratorInterface $fileUrlGenerator
   *   The decorated file URL generator.
   * @param \Drupal\Core\Config\ImmutableConfig $lupusDecoupledCeApiSettings
   *   The lupus decoupled ce-api configuration.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param string $apiPrefix
   *   The api path prefix.
   */
  public function __construct(
    FileUrlGeneratorInterface $fileUrlGenerator,
    ImmutableConfig $lupusDecoupledCeApiSettings,
    RequestStack $request_stack,
    string $apiPrefix
  ) {
    $this->fileUrlGenerator = $fileUrlGenerator;
    $this->lupusDecoupledCeApiSettings = $lupusDecoupledCeApiSettings;
    $this->requestStack = $request_stack;
    $this->apiPrefix = $apiPrefix;
  }

  /**
   * {@inheritdoc}
   */
  public function generateString(string $uri): string {
    if ($this->lupusDecoupledCeApiSettings->get('absolute_file_urls') && $this->isApiResponse()) {
      return $this->fileUrlGenerator->generateAbsoluteString($uri);
    }
    return $this->fileUrlGenerator->generateString($uri);
  }

  /**
   * {@inheritdoc}
   */
  public function generateAbsoluteString(string $uri): string {
    return $this->fileUrlGenerator->generateAbsoluteString($uri);
  }

  /**
   * {@inheritdoc}
   */
  public function generate(string $uri): Url {
    if ($this->lupusDecoupledCeApiSettings->get('absolute_file_urls') && $this->isApiResponse()) {
      $result = $this->fileUrlGenerator->generateAbsoluteString($uri);
      return Url::fromUri($result);
    }
    return $this->fileUrlGenerator->generate($uri);
  }

  /**
   * {@inheritdoc}
   */
  public function transformRelative(string $file_url, bool $root_relative = TRUE): string {
    return $this->fileUrlGenerator->transformRelative($file_url, $root_relative);
  }

  /**
   * Determines if we are serving an api response.
   *
   * @return bool
   *   Returns TRUE if we are serving an api response.
   *   FALSE otherwise.
   */
  protected function isApiResponse() : bool {
    $request = $this->requestStack->getCurrentRequest();
    return $request->attributes->get('lupus_ce_renderer') || $request->getRequestFormat() == 'custom_elements';
  }

}
