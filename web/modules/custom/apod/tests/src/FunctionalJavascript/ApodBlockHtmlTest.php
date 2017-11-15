<?php

namespace Drupal\Tests\apod\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\JavascriptTestBase;

/**
 * Class ApodBlockHtmlTest
 * @group apod
 */
class ApodBlockHtmlTest extends JavascriptTestBase {

  /**
   * Tests the APOD block properties.
   */
  public function testBlockExists() {
    $page = $this->getSession()->getPage();
    $element = $page->find('css', '#block-astronomypictureofthedayblock');
    $this->assertTrue($element->isVisible());

    // Find the title and ensure it's not empty.
    $title = $element->find('css', 'h2');
    $this->assertTrue($title->isVisible());
    $this->assertNotNull($title->getHtml());

    // Find the content div in the block.
    $content = $element->find('css', 'div.content');
    $this->assertTrue($content->isVisible());

    // Find the image, and ensure it has the expected class.
    $image = $content->find('css', 'img');
    $this->assertTrue($image->isVisible());
    $this->assertTrue($image->hasClass('image-style-thumbnail'));

    // Find the description and ensure it's not empty.
    $description = $content->find('css', 'p');
    $this->assertTrue($description->isVisible());
    $this->assertNotNull($description->getHtml());
    $this->createScreenshot(\Drupal::root() . '/sites/default/files/simpletest/apod_block_exists.png');
  }
}
