<?php

namespace Drupal\Tests\recent_updates\Functional;

use Drupal\Core\Url;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Tests\BrowserTestBase;

/**
 * Simple test to ensure that main page loads with module enabled.
 *
 * @group recent_updates
 */
class LoadTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['recent_updates', 'node', 'block'];

  /**
   * A user with permission to administer site configuration.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  /**
   * Theme used to render test output.
   * 
   * @var string
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->drupalCreateContentType([
      'type' => 'article',
      'name' => 'Article',
    ]);

    $this->drupalPlaceBlock('recent_updates_block', [
      'region' => 'content',
      'label' => 'Updated Today',
    ]);

    $this->container->get('router.builder')->rebuild();
  }

  /**
   * Tests that the home page loads with a 200 response.
   */
  public function testBlock() {
    $yesterday = (new DrupalDateTime('-1 day' ))->getTimestamp();
    $today = (new DrupalDateTime('today'))->getTimestamp();

    $this->drupalCreateNode([
      'title' => "Today's Article",
      'type' => 'article',
      'created' => $today,
      'changed' => $today,
    ]);

    $this->drupalCreateNode([
      'title' => "Yesterday's Article",
      'type' => 'article',
      'created' => $yesterday,
      'changed' => $yesterday,
    ]);

    $this->drupalGet(Url::fromRoute('<front>'));
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains("Today's Article");
    $this->assertSession()->pageTextNotContains("Yesterday's Article");
  }

}
