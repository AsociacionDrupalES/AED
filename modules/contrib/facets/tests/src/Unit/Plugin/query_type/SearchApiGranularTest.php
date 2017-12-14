<?php

namespace Drupal\Tests\facets\Unit\Plugin\query_type;

use Drupal\facets\Entity\Facet;
use Drupal\facets\FacetInterface;
use Drupal\facets\Plugin\facets\query_type\SearchApiGranular;
use Drupal\search_api\Backend\BackendInterface;
use Drupal\search_api\IndexInterface;
use Drupal\facets\Result\ResultInterface;
use Drupal\facets\Widget\WidgetPluginInterface;
use Drupal\search_api\Plugin\views\query\SearchApiQuery;
use Drupal\search_api\ServerInterface;
use Drupal\Tests\UnitTestCase;

/**
 * Unit test for granular query type.
 *
 * @group facets
 */
class SearchApiGranularTest extends UnitTestCase {

  /**
   * Tests string query type without executing the query with an "AND" operator.
   */
  public function testQueryTypeAnd() {
    $backend = $this->prophesize(BackendInterface::class);
    $backend->getSupportedFeatures()->willReturn([]);
    $server = $this->prophesize(ServerInterface::class);
    $server->getBackend()->willReturn($backend);
    $index = $this->prophesize(IndexInterface::class);
    $index->getServerInstance()->willReturn($server);
    $query = $this->prophesize(SearchApiQuery::class);
    $query->getIndex()->willReturn($index);

    $facet = new Facet(
      ['query_operator' => 'AND', 'widget' => 'links'],
      'facets_facet'
    );
    $facetReflection = new \ReflectionClass(Facet::class);
    $widget = $this->getMockBuilder(WidgetPluginInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $widget->method('getConfiguration')->will($this->returnValue(['granularity' => 10]));
    $widget_instance = $facetReflection->getProperty('widgetInstance');
    $widget_instance->setAccessible(TRUE);
    $widget_instance->setValue($facet, $widget);

    // Results for the widget.
    $original_results = [
      ['count' => 3, 'filter' => '2'],
      ['count' => 5, 'filter' => '4'],
      ['count' => 7, 'filter' => '9'],
      ['count' => 9, 'filter' => '11'],
    ];

    // Facets the widget should produce.
    $grouped_results = [
      0 => ['count' => 15, 'filter' => '0'],
      10 => ['count' => 9, 'filter' => 10],
    ];

    $query_type = new SearchApiGranular(
      [
        'facet' => $facet,
        'query' => $query->reveal(),
        'results' => $original_results,
      ],
      'search_api_string',
      []
    );

    $built_facet = $query_type->build();
    $this->assertInstanceOf(FacetInterface::class, $built_facet);

    $results = $built_facet->getResults();
    $this->assertInternalType('array', $results);

    foreach ($grouped_results as $k => $result) {
      $this->assertInstanceOf(ResultInterface::class, $results[$k]);
      $this->assertEquals($result['count'], $results[$k]->getCount());
      $this->assertEquals($result['filter'], $results[$k]->getDisplayValue());
    }
  }

  /**
   * Tests string query type without results.
   */
  public function testEmptyResults() {
    $query = new SearchApiQuery([], 'search_api_query', []);
    $facet = new Facet([], 'facets_facet');

    $query_type = new SearchApiGranular(
      [
        'facet' => $facet,
        'query' => $query,
      ],
      'search_api_string',
      []
    );

    $built_facet = $query_type->build();
    $this->assertInstanceOf(FacetInterface::class, $built_facet);

    $results = $built_facet->getResults();
    $this->assertInternalType('array', $results);
    $this->assertEmpty($results);
  }

}
