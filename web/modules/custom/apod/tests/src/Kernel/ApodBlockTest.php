<?php

namespace Drupal\Tests\apod\Kernel;

use Drupal\apod\ApodClient;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the block provided by the Apod module.
 *
 * @coversDefaultClass \Drupal\apod\Plugin\Block\ApodBlock
 * @group apod
 */
class ApodBlockTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['block', 'apod', 'system', 'user'];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->installSchema('system', ['sequence']);
    $this->installEntitySchema('user');
  }

  /**
   * Test that the apod module provides a block plugin definition.
   */
  public function testEnablingApodModuleCreatesBlock() {
    /** @var \Drupal\Core\Block\BlockManagerInterface $block_manager */
    $block_manager = $this->container->get('plugin.manager.block');
    $plugin_id = 'apod_block';
    $this->assertTrue($block_manager->hasDefinition($plugin_id));
  }

  /**
   * Test the apod_block.
   *
   * This method tests the block provided by the apod module, using a mocked
   * ApodClient, and expects that when the client's getAstronomyPictureOfTheDay()
   * method returns NULL, the block build() method returns an empty array.
   */
  public function testEmptyBlock() {
    // Mock the ApodClient object to return NULL.
    $mockApodClient = $this->prophesize(ApodClient::class);
    $mockApodClient->getAstronomyPictureOfTheDay()
      ->willReturn(NULL);

    $this->container->set('apod.client', $mockApodClient->reveal());

    /** @var \Drupal\Core\Block\BlockManagerInterface $block_manager */
    $block_manager = $this->container->get('plugin.manager.block');

    $block = $block_manager->createInstance('apod_block');
    $render = $block->build();
    $this->assertEmpty($render);
  }

  /**
   * Test the apod_block.
   *
   * This method tests the block provided by the apod module, using a mocked
   * ApodClient, and expects that when the client's getAstronomyPictureOfTheDay()
   * method returns data, the block build() method returns a non-empty array
   * with specific keys.
   */
  public function testNonEmptyBlock() {
    // Mock the ApodClient object to return a known success response.
    $mockApodClient = $this->prophesize(ApodClient::class);
    $mockApodClient->getAstronomyPictureOfTheDay()
      ->willReturn($this->getMockResponseBody());

    $this->container->set('apod.client', $mockApodClient->reveal());

    /** @var \Drupal\Core\Block\BlockManagerInterface $block_manager */
    $block_manager = $this->container->get('plugin.manager.block');

    $block = $block_manager->createInstance('apod_block');
    $render = $block->build();
    $this->assertArrayHasKey('#theme', $render);
    $this->assertEquals('apod_block', $render['#theme']);
    $this->assertArrayHasKey('#title', $render);
    $this->assertEquals('Foo!', $render['#title']);
    $this->assertArrayHasKey('#image', $render);
    $this->assertArrayHasKey('#content', $render);
    $this->assertNotEmpty($render['#content']);
  }

  /**
   * Get a fake response from the APOD service.
   *
   * @return object
   *   Returns an object representing a fake response from the APOD service.
   */
  protected function getMockResponseBody() {
    return (object) [
      'copyright' => 'foo.com',
      'date' => '2017-11-12',
      'explanation' => 'Foo FTW.',
      'hdurl' => 'https://foo.com/hd/foo.jpg',
      'media_type' => 'image',
      'service_version' => 'v1',
      'title' => 'Foo!',
      'url' => 'https://foo.com/foo.jpg',
    ];
  }

}
