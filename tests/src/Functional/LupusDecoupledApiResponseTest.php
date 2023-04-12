<?php

namespace Drupal\Tests\lupus_decoupled\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Test Lupus Decoupled features.
 *
 * @group lupus_decoupled
 */
class LupusDecoupledApiResponseTest extends BrowserTestBase {

  /**
   * The node to use for testing.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $node;

  /**
   * Path to created node.
   *
   * @var string
   */
  protected $nodePath;
  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'custom_elements',
    'lupus_ce_renderer',
    'lupus_decoupled',
    'lupus_decoupled_ce_api',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create Basic page node type.
    $this->drupalCreateContentType(['type' => 'page', 'name' => 'Basic page']);
    $this->node = $this->drupalCreateNode();
    $this->nodePath = 'ce-api/node/' . $this->node->id();
  }

  /**
   * Tests if created node is accessible.
   */
  public function testExistingPageResponse() {
    $this->drupalGet('node/' . $this->node->id());
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Tests un-existing page access.
   */
  public function test404Page() {
    $this->drupalGet('i-dont-exist');
    $this->assertSession()->statusCodeEquals(404);
  }

  /**
   * Tests if created node is accessible at api endpoint.
   */
  public function testExistingPageApiResponse() {
    $this->drupalGet($this->nodePath);
    $this->assertSession()->statusCodeEquals(200);
  }

}
