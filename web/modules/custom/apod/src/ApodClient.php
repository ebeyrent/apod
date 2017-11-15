<?php

namespace Drupal\apod;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Psr7\Response;

/**
 * Class ApodClient.
 *
 * @package Drupal\apod
 */
class ApodClient {

  use StringTranslationTrait;

  /**
   * Instance of the Guzzle http client class.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * Instance of the LoggerInterface.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * String representing the APOD service url.
   *
   * @var string
   */
  protected $url = 'https://api.nasa.gov/planetary/apod';

  /**
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * ApodClient constructor.
   *
   * @param \GuzzleHttp\ClientInterface $httpClient
   *   Instance of the Guzzle HTTP Client.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   Instance of the Logger factory.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Instance of an object that implements the ConfigFactoryInterface.
   */
  public function __construct(ClientInterface $httpClient, LoggerChannelFactoryInterface $logger, ConfigFactoryInterface $config_factory) {
    $this->httpClient = $httpClient;
    $this->logger = $logger->get('apod');
    $this->config = $config_factory->get('apod.api_config');
  }

  /**
   * {@inheritdoc}
   *
   * @internal Pass in API key in options
   */
  public function getAstronomyPictureOfTheDay() {
    $uri = $this->addQueryString($this->url, ['query' => ['api_key' => $this->config->get('api_key')]]);
    try {
      $response = $this->httpClient->request('GET', $uri);
    }
    catch (\GuzzleHttp\Exception\ConnectException $exception) {
      $response = new Response(500, [], $exception->getMessage());
    }
    return $this->handleResponse($response);
  }

  /**
   * Add the query string to the uri.
   *
   * @param string $uri
   *   The URI including host and path.
   * @param array $options
   *   Options array possibly including a 'query' key.
   *
   * @return string
   *   The URI with a query string appended.
   */
  protected function addQueryString($uri, array $options) {
    $query = !empty($options['query']) ? (array) $options['query'] : [];
    return $uri . '?' . UrlHelper::buildQuery($query);
  }

  /**
   * Process the http client response.
   *
   * @param \Psr\Http\Message\ResponseInterface $response
   *   Instance of the http client response.
   * @param bool $assoc
   *   Value to pass as the second parameter to json_decode().
   *
   * @return \stdClass|null
   *   Returns a string for a valid/success response, null otherwise.
   */
  protected function handleResponse(ResponseInterface $response, $assoc = FALSE) {
    switch ($response->getStatusCode()) {
      case 200:
      case 201:
        return json_decode($response->getBody()->getContents(), $assoc);

      case 403:
        $this->logger->error($this->t('Access denied for the remote endpoint.  %reason', ['%reason' => $response->getReasonPhrase()]));
        break;

      case 404:
        $this->logger->error($this->t('Invalid jsonapi request path.  %reason', ['%reason' => $response->getReasonPhrase()]));
        break;

      case 500:
        $this->logger->error($this->t('A 500 error occurred at the remote endpoint.  %reason', ['%reason' => $response->getReasonPhrase()]));
        break;

      default:
        $this->logger->error($this->t('An unknown error (%code) occurred.  %reason', ['%code' => $response->getStatusCode(), '%reason' => $response->getReasonPhrase()]));
        break;

    }
    return NULL;
  }

}
