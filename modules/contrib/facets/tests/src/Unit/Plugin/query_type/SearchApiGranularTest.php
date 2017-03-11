<?php

namespace Drupal\Tests\facets\Unit\Plugin\query_type;

use Drupal\facets\Entity\Facet;
use Drupal\facets\Plugin\facets\query_type\SearchApiGranular;
use Drupal\search_api\Plugin\views\query\SearchApiQuery;
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
    $query = new SearchApiQuery([], 'search_api_query', []);
    $facetReflection = new \ReflectionClass('Drupal\facets\Entity\Facet');
    $facet = new Facet(
      ['query_operator' => 'AND', 'widget' => 'links'],
      'facets_facet'
    );
    $widget = $this->getMockBuilder('Drupal\facets\Widget\WidgetPluginInterface')
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
        'query' => $query,
        'results' => $original_results,
      ],
      'search_api_string',
      []
    );

    $built_facet = $query_type->build();
    $this->assertInstanceOf('\Drupal\facets\FacetInterface', $built_facet);

    $results = $built_facet->getResults();
    $this->assertInternalType('array', $results);

    foreach ($grouped_results as $k => $result) {
      $this->assertInstanceOf('\Drupal\facets\Result\ResultInterface', $results[$k]);
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
    $this->assertInstanceOf('\Drupal\facets\FacetInterface', $built_facet);

    $results = $built_facet->getResults();
    $this->assertInternalType('array', $results);
    $this->assertEmpty($results);
  }

}
