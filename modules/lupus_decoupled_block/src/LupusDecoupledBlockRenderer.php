<?php

namespace Drupal\lupus_decoupled_block;

use drunomics\ServiceUtils\Core\Render\RendererTrait;
use Drupal\block\BlockRepositoryInterface;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\custom_elements\CustomElement;

/**
 * Provides support functionality for the lupus_decoupled_block module.
 */
class LupusDecoupledBlockRenderer {
  use RendererTrait;

  /**
   * The block.repository service.
   *
   * @var \Drupal\block\BlockRepositoryInterface
   */
  protected $blockRepository;

  /**
   * Constructs a LupusDecoupledBlockRenderer object.
   *
   * @param \Drupal\block\BlockRepositoryInterface $block_repository
   *   The block.repository service.
   */
  public function __construct(BlockRepositoryInterface $block_repository) {
    $this->blockRepository = $block_repository;
  }

  /**
   * Gets blocks data.
   *
   * @param \Drupal\Core\Cache\CacheableDependencyInterface|null $cacheableDependency
   *
   * @return array
   *   Array of blocks markup, by region.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function getBlocks(CacheableDependencyInterface $cacheableDependency = NULL) {
    $blocksPerRegion = $this->blockRepository->getVisibleBlocksPerRegion();
    foreach ($blocksPerRegion as $region => $blocks) {
      foreach ($blocks as $block) {
        if ($render = $block->getPlugin()->build()) {
          $customElement = CustomElement::createFromRenderArray($render)
            ->toRenderArray();
          $output[$region][$block->id()] = $this->getrenderer()
            ->renderRoot($customElement);
        }

        if ($cacheableDependency) {
          $cacheableDependency->addCacheableDependency($block->getPlugin());
          $cacheableDependency->addCacheableDependency($block);
        }
      }
      if (!empty($output[$region])) {
        $output[$region] = array_filter($output[$region]);
      }
    }
    if (!empty($output)) {
      $output = array_filter($output);
    }
    return $output;
  }

}
