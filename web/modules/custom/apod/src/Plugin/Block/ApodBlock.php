<?php

namespace Drupal\apod\Plugin\Block;

use Drupal\apod\ApodClient;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Renderer;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Provides an Astronomy Picture of the Day Block.
 *
 * @Block(
 *   id = "apod_block",
 *   admin_label = @Translation("Astronomy Picture of the Day block"),
 *   category = @Translation("APOD"),
 * )
 */
class ApodBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Instance of the ApodClient service.
   *
   * @var \Drupal\apod\ApodClient
   */
  protected $apodClient;

  protected $renderer;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ApodClient $apod, Renderer $renderer) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->setConfiguration($configuration);
    $this->apodClient = $apod;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('apod.client'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $data = $this->apodClient->getAstronomyPictureOfTheDay();
    if ($data === NULL) {
      return [];
    }

    $image = array(
      '#theme' => 'imagecache_external',
      '#uri' => $data->url,
      '#style_name' => 'thumbnail',
      '#alt' => $data->title,
      '#title' => $data->title,
    );

    return array(
      '#theme' => 'apod_block',
      '#title' => $data->title,
      '#image' => $this->renderer->renderRoot($image),
      '#content' => $data->explanation,
    );

  }

}
