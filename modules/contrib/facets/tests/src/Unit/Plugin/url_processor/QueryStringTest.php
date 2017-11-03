<?php

namespace Drupal\Tests\facets\Unit\Plugin\url_processor;

use Drupal\facets\Entity\Facet;
use Drupal\facets\Entity\FacetSource;
use Drupal\facets\Plugin\facets\url_processor\QueryString;
use Drupal\facets\Result\Result;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Unit test for processor.
 *
 * @group facets
 */
class QueryStringTest extends UnitTestCase {

  /**
   * The processor to be tested.
   *
   * @var \Drupal\facets\Plugin\facets\url_processor\QueryString
   */
  protected $processor;

  /**
   * An array containing the results before the processor has ran.
   *
   * @var \Drupal\facets\Result\Result[]
   */
  protected $originalResults;

  /**
   * Creates a new processor object for use in the tests.
   */
  protected function setUp() {
    parent::setUp();

    $this->originalResults = [
      new Result('llama', 'Llama', 15),
      new Result('badger', 'Badger', 5),
      new Result('mushroom', 'Mushroom', 5),
      new Result('duck', 'Duck', 15),
      new Result('alpaca', 'Alpaca', 25),
    ];

    $this->setContainer();
  }

  /**
   * Tests that the processor correctly throws an exception.
   */
  public function testEmptyProcessorConfiguration() {
    $this->setExpectedException('\Drupal\facets\Exception\InvalidProcessorException', "The url processor doesn't have the required 'facet' in the configuration array.");
    new QueryString([], 'test', [], new Request());
  }

  /**
   * Tests that the processor correctly throws an exception.
   */
  public function testCreate() {
    $facet = new Facet([], 'facets_facet');

    $request_stack = new RequestStack();
    $request = new Request();
    $request_stack->push($request);

    $container = new ContainerBuilder();
    $container->set('request_stack', $request_stack);

    $qs = QueryString::create($container, ['facet' => $facet], 'query_string', []);
    $this->assertInstanceOf(QueryString::class, $qs);
  }

  /**
   * Tests with one active item.
   */
  public function testSetSingleActiveItem() {
    $facet = new Facet([], 'facets_facet');
    $facet->setResults($this->originalResults);
    $facet->setUrlAlias('test');
    $facet->setFieldIdentifier('test');

    $request = new Request();
    $request->query->set('f', ['test:badger']);

    $this->processor = new QueryString(['facet' => $facet], 'query_string', [], $request);
    $this->processor->setActiveItems($facet);

    $this->assertEquals(['badger'], $facet->getActiveItems());
  }

  /**
   * Tests with multiple active items.
   */
  public function testSetMultipleActiveItems() {
    $facet = new Facet([], 'facets_facet');
    $facet->setResults($this->originalResults);
    $facet->setUrlAlias('test');
    $facet->setFieldIdentifier('test');

    $request = new Request();
    $request->query->set('f', ['test:badger', 'test:mushroom', 'donkey:kong']);

    $this->processor = new QueryString(['facet' => $facet], 'query_string', [], $request);
    $this->processor->setActiveItems($facet);

    $this->assertEquals(['badger', 'mushroom'], $facet->getActiveItems());
  }

  /**
   * Tests with an empty build.
   */
  public function testEmptyBuild() {
    $facet = new Facet([], 'facets_facet');
    $facet->setUrlAlias('test');
    $facet->setFacetSourceId('facet_source__dummy');

    $request = new Request();
    $request->query->set('f', []);

    $this->processor = new QueryString(['facet' => $facet], 'query_string', [], $request);
    $results = $this->processor->buildUrls($facet, []);
    $this->assertEmpty($results);
  }

  /**
   * Tests with default build.
   */
  public function testBuild() {
    $facet = new Facet([], 'facets_facet');
    $facet->setFieldIdentifier('test');
    $facet->setUrlAlias('test');
    $facet->setFacetSourceId('facet_source__dummy');

    $request = new Request();
    $request->query->set('f', []);

    $this->processor = new QueryString(['facet' => $facet], 'query_string', [], $request);
    $results = $this->processor->buildUrls($facet, $this->originalResults);

    $this->assertEquals('f', $this->processor->getFilterKey());

    /** @var \Drupal\facets\Result\ResultInterface $r */
    foreach ($results as $r) {
      $this->assertInstanceOf('\Drupal\facets\Result\ResultInterface', $r);
      $this->assertEquals('route:test?f%5B0%5D=test%3A' . $r->getRawValue(), $r->getUrl()->toUriString());
    }
  }

  /**
   * Tests with an active item already from url.
   */
  public function testBuildWithActiveItem() {
    $facet = new Facet([], 'facets_facet');
    $facet->setFieldIdentifier('test');
    $facet->setUrlAlias('test');
    $facet->setFacetSourceId('facet_source__dummy');

    $original_results = $this->originalResults;
    $original_results[2]->setActiveState(TRUE);

    $request = new Request();
    $request->query->set('f', ['king:kong']);

    $this->processor = new QueryString(['facet' => $facet], 'query_string', [], $request);
    $results = $this->processor->buildUrls($facet, $original_results);

    /** @var \Drupal\facets\Result\ResultInterface $r */
    foreach ($results as $k => $r) {
      $this->assertInstanceOf('\Drupal\facets\Result\ResultInterface', $r);
      if ($k === 2) {
        $this->assertEquals('route:test?f%5B0%5D=king%3Akong', $r->getUrl()->toUriString());
      }
      else {
        $this->assertEquals('route:test?f%5B0%5D=king%3Akong&f%5B1%5D=test%3A' . $r->getRawValue(), $r->getUrl()->toUriString());
      }
    }
  }

  /**
   * Tests with only one result.
   */
  public function testWithOnlyOneResult() {
    $facet = new Facet([], 'facets_facet');
    $facet->setFieldIdentifier('test');
    $facet->setUrlAlias('test');
    $facet->setFacetSourceId('facet_source__dummy');
    $facet->setShowOnlyOneResult(TRUE);

    $this->originalResults[1]->setActiveState(TRUE);
    $this->originalResults[2]->setActiveState(TRUE);

    $this->processor = new QueryString(['facet' => $facet], 'query_string', [], new Request());
    $results = $this->processor->buildUrls($facet, $this->originalResults);

    $this->assertEquals('route:test?f%5B0%5D=test%3A' . $results[0]->getRawValue(), $results[0]->getUrl()->toUriString());
    $this->assertEquals('route:test?f%5B0%5D=test%3A' . $results[3]->getRawValue(), $results[3]->getUrl()->toUriString());
    $this->assertEquals('route:test?f%5B0%5D=test%3A' . $results[4]->getRawValue(), $results[4]->getUrl()->toUriString());
    $this->assertEquals('route:test', $results[1]->getUrl()->toUriString());
    $this->assertEquals('route:test', $results[2]->getUrl()->toUriString());
  }

  /**
   * Tests that the facet source configuration filter key override works.
   */
  public function testFacetSourceFilterKeyOverride() {
    $facet_source = new FacetSource(['filter_key' => 'ab'], 'facets_facet_source');

    // Override the container with the new facet source.
    $storage = $this->getMock('\Drupal\Core\Entity\EntityStorageInterface');
    $storage->expects($this->once())
      ->method('load')
      ->willReturn($facet_source);
    $em = $this->getMockBuilder('\Drupal\Core\Entity\EntityTypeManagerInterface')
      ->disableOriginalConstructor()
      ->getMock();
    $em->expects($this->any())
      ->method('getStorage')
      ->willReturn($storage);

    $container = \Drupal::getContainer();
    $container->set('entity_type.manager', $em);
    \Drupal::setContainer($container);

    $facet = new Facet([], 'facets_facet');
    $facet->setFieldIdentifier('test');
    $facet->setFacetSourceId('facet_source__dummy');
    $facet->setUrlAlias('test');

    $request = new Request();
    $request->query->set('ab', []);

    $this->processor = new QueryString(['facet' => $facet], 'query_string', [], $request);
    $results = $this->processor->buildUrls($facet, $this->originalResults);

    /** @var \Drupal\facets\Result\ResultInterface $r */
    foreach ($results as $r) {
      $this->assertInstanceOf('\Drupal\facets\Result\ResultInterface', $r);
      $this->assertEquals('route:test?ab%5B0%5D=test%3A' . $r->getRawValue(), $r->getUrl()->toUriString());
    }

  }

  /**
   * Tests that the separator works as expected.
   */
  public function testSeparator() {
    $facet = new Facet([], 'facets_facet');
    $facet->setFieldIdentifier('test');
    $facet->setUrlAlias('test');
    $facet->setFacetSourceId('facet_source__dummy');

    $this->processor = new QueryString(['facet' => $facet, 'separator' => '__'], 'query_string', [], new Request());
    $results = $this->processor->buildUrls($facet, $this->originalResults);

    foreach ($results as $result) {
      $this->assertEquals('route:test?f%5B0%5D=test__' . $result->getRawValue(), $result->getUrl()->toUriString());
    }
  }

  /**
   * Tests that contextual filter get's re-added.
   */
  public function testContextualFilters() {
    // Override router.
    $router = $this->getMockBuilder('Drupal\Tests\Core\Routing\TestRouterInterface')
      ->disableOriginalConstructor()
      ->getMock();
    $router->expects($this->any())
      ->method('matchRequest')
      ->willReturn([
        '_raw_variables' => new ParameterBag(['node' => '1']),
        '_route' => 'node_view',
      ]);

    // Get the container from the setUp method and change it with the
    // implementation created here, that has the route parameters.
    $container = \Drupal::getContainer();
    $container->set('router.no_access_checks', $router);
    \Drupal::setContainer($container);

    // Create facet.
    $facet = new Facet([], 'facets_facet');
    $facet->setFieldIdentifier('test');
    $facet->setUrlAlias('test');
    $facet->setFacetSourceId('facet_source__dummy');

    $this->processor = new QueryString(['facet' => $facet], 'query_string', [], new Request());
    $results = $this->processor->buildUrls($facet, $this->originalResults);

    foreach ($results as $result) {
      $this->assertEquals(['node' => 1], $result->getUrl()->getRouteParameters());
    }
  }

  /**
   * Sets up a container.
   */
  protected function setContainer() {
    $router = $this->getMockBuilder('Drupal\Tests\Core\Routing\TestRouterInterface')
      ->disableOriginalConstructor()
      ->getMock();
    $router->expects($this->any())
      ->method('matchRequest')
      ->willReturn([
        '_raw_variables' => new ParameterBag([]),
        '_route' => 'test',
      ]);

    $validator = $this->getMock('Drupal\Core\Path\PathValidatorInterface');

    $fsi = $this->getMockBuilder('\Drupal\facets\FacetSource\FacetSourcePluginInterface')
      ->disableOriginalConstructor()
      ->getMock();
    $fsi->method('getPath')
      ->willReturn('test');

    $manager = $this->getMockBuilder('\Drupal\facets\FacetSource\FacetSourcePluginManager')
      ->disableOriginalConstructor()
      ->getMock();
    $manager->method('createInstance')
      ->willReturn($fsi);
    $manager->method('hasDefinition')
      ->with('facet_source__dummy')
      ->willReturn(TRUE);

    $storage = $this->getMock('\Drupal\Core\Entity\EntityStorageInterface');
    $em = $this->getMockBuilder('\Drupal\Core\Entity\EntityTypeManagerInterface')
      ->disableOriginalConstructor()
      ->getMock();
    $em->expects($this->any())
      ->method('getStorage')
      ->willReturn($storage);

    $container = new ContainerBuilder();
    $container->set('router.no_access_checks', $router);
    $container->set('plugin.manager.facets.facet_source', $manager);
    $container->set('entity_type.manager', $em);
    $container->set('entity.manager', $em);
    $container->set('path.validator', $validator);
    \Drupal::setContainer($container);
  }

}
