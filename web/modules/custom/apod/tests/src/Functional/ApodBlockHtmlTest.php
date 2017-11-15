<?php

namespace Drupal\Tests\apod\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests block HTML ID validity.
 *
 * @group apod
 */
class ApodBlockHtmlTest extends BrowserTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = ['block', 'imagecache_external', 'apod'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    // Ignore schema errors.
    $this->strictConfigSchema = FALSE;
    parent::setUp();

    $this->drupalLogin($this->rootUser);

    // Set the API key.
    $config = \Drupal::configFactory()->getEditable('apod.api_config');
    $config->set('api_key', 'Coz2GoIC0BTKh39KVeNZZNevUGGwsBA3UEOFbHuY');
    $config->save();

    // Place the APOD block.
    $this->drupalPlaceBlock('apod_block', [
      'id' => 'astronomypictureofthedayblock',
    ]);
  }

  /**
   * Tests that the APOD block is on the front page and that it contains the
   * elements we expect.
   */
  public function testHtml() {
    $this->drupalGet('');
    $this->assertSession()->statusCodeEquals(200);
    // Assert that the APOD block is present in the HTML.
    $this->assertRaw('id="block-astronomypictureofthedayblock"');

    $page = $this->getSession()->getPage();
    $element = $page->find('css', '#block-astronomypictureofthedayblock');

    // Find the title and ensure it's not empty.
    $title = $element->find('css', 'h2');
    $this->assertNotNull($title->getHtml());

    // Find the image, and ensure it has the expected class.
    $image = $element->find('css', 'img');
    $this->assertTrue($image->hasClass('image-style-thumbnail'));

    // Find the description and ensure it's not empty.
    $description = $element->find('css', 'p');
    $this->assertNotNull($description->getHtml());

  }

}
