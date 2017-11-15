<?php

namespace Drupal\Tests\apod\Unit;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\apod\ApodClient;
use Drupal\Tests\UnitTestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Prophecy\Argument;

/**
 * @coversDefaultClass \Drupal\apod\ApodClient
 * @group apod
 */
class ApodClientTest extends UnitTestCase {

  /**
   * Test double for the Guzzle HTTP Client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $mockHttpClient;

  /**
   * Test double for the Logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactory
   */
  protected $mockLoggerFactory;

  /**
   * Test double for the config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $mockConfig;


  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->mockHttpClient = $this->prophesize(Client::class);
    $this->mockHttpClient->willImplement('\GuzzleHttp\ClientInterface');

    $this->mockLoggerFactory = $this->prophesize(LoggerChannelFactory::class);
    $this->mockLoggerFactory->willImplement('Drupal\Core\Logger\LoggerChannelFactoryInterface');

    $immutableConfig = $this->prophesize(ImmutableConfig::class);
    $immutableConfig->get('api_key')
      ->willReturn('123456abc');

    $this->mockConfig = $this->prophesize(ConfigFactoryInterface::class);
    $this->mockConfig->get(Argument::is('apod.api_config'))
      ->willReturn($immutableConfig);



  }

  /**
   * Tests the class constructor.
   *
   * This method tests the class constructor, and expects to not get an exception.
   *
   * @covers ::__construct
   */
  public function testConstructor() {
    $apodClient = new ApodClient(
      $this->mockHttpClient->reveal(),
      $this->mockLoggerFactory->reveal(),
      $this->mockConfig->reveal());
    $this->assertInstanceOf('Drupal\apod\ApodClient', $apodClient);
  }

  /**
   * Test the getAstronomyPictureOfTheDay() method.
   *
   * This method tests the apodClient getAstronomyPictureOfTheDay() method with
   * a mocked success response.  The test expects that for successful responses,
   * the apodClient will not invoke any logger methods, and will return a string
   * representing the body of the response.
   *
   * @covers ::getAstronomyPictureOfTheDay
   */
  public function testgetAstronomyPictureOfTheDaySuccess() {
    $string = json_encode($this->getMockResponse());
    $response = new Response(200, ['Content-Type' => 'application/json'], $string);
    $this->mockHttpClient->request(
      Argument::any(),
      Argument::any(),
      Argument::any()
    )->willReturn($response);

    /*
     * Create a mock logger channel with some expectations on logging methods
     * which should not be called.
     */
    $mockLogger = $this->prophesize(LoggerChannel::class);
    $mockLogger->warning(Argument::any())->shouldNotBeCalled();
    $mockLogger->error(Argument::any())->shouldNotBeCalled();
    $mockLogger->info(Argument::any())->shouldNotBeCalled();
    $this->mockLoggerFactory->get('apod')->willReturn($mockLogger);

    // Create the apodClient instance.
    $apodClient = new ApodClient(
      $this->mockHttpClient->reveal(),
      $this->mockLoggerFactory->reveal(),
      $this->mockConfig->reveal()
    );

    // Call the getAstronomyPictureOfTheDay() method and make sure all is good.
    $content = $apodClient->getAstronomyPictureOfTheDay();
    $this->assertInstanceOf('stdClass', $content);
    $this->assertObjectHasAttribute('title', $content);
    $this->assertEquals('Foo!', $content->title);
  }

  /**
   * Test the getAstronomyPictureOfTheDay() method.
   *
   * This method tests the apodClient getAstronomyPictureOfTheDay method with
   * mocked failure responses.  The test expects that for error responses, the
   * apodClient will invoke the logger error() method once, and will return NULL.
   *
   * @covers ::getAstronomyPictureOfTheDay
   */
  public function testgetAstronomyPictureOfTheDayErrors() {
    $status = [
      '403' => 'Access denied',
      '404' => 'Not found',
      '500' => 'Server error',
      '598' => 'Network error',
    ];

    /*
     * Create a mock error response for each code/message, configure the mock
     * http client to return the mock response, and ensure that we get a NULL
     * back from the apodClient getAstronomyPictureOfTheDay() method.
     */
    foreach ($status as $code => $error) {
      $response = new Response($code, ['Content-Type' => 'application/json'], $error);
      $this->mockHttpClient->request(
        Argument::any(),
        Argument::any(),
        Argument::any()
      )->willReturn($response);

      $mockLogger = $this->prophesize(LoggerChannel::class);
      $mockLogger->error(Argument::any())->shouldBeCalledTimes(1);
      $mockLogger->warning(Argument::any())->shouldNotBeCalled();
      $mockLogger->info(Argument::any())->shouldNotBeCalled();
      $this->mockLoggerFactory->get('apod')->willReturn($mockLogger);

      // Create the apodClient instance.
      $apodClient = new ApodClient(
        $this->mockHttpClient->reveal(),
        $this->mockLoggerFactory->reveal(),
        $this->mockConfig->reveal()
      );

      // Call the getAstronomyPictureOfTheDay() method and make sure all is good.
      $content = $apodClient->getAstronomyPictureOfTheDay();
      $this->assertNull($content);
    }
  }

  /**
   * Get a fake response from the APOD service.
   *
   * @return object
   *   Returns an object representing a fake response from the APOD service.
   */
  protected function getMockResponse() {
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
