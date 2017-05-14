<?php

namespace Drupal\Tests\facets\Unit\Plugin\widget;

use Drupal\facets\Entity\Facet;
use Drupal\facets\Plugin\facets\widget\DateArrayWidget;

/**
 * Unit test for widget.
 *
 * @group facets
 */
class DateArrayWidgetTest extends WidgetTestBase {

  /**
   * Creates a new processor object for use in the tests.
   */
  protected function setUp() {
    parent::setUp();

    $this->widget = new DateArrayWidget(['show_numbers' => 1]);
  }

  /**
   * Tests widget without filters.
   */
  public function testNoFilterResults() {
    $facet = new Facet([], 'facets_facet');
    $facet->setResults($this->originalResults);
    $facet->setFieldIdentifier('tag');

    $output = $this->widget->build($facet);

    $this->assertInternalType('array', $output);
    $this->assertCount(4, $output['tag']);

    $expected_links = [
      ['url' => NULL, 'values' => ['value' => 'Llama', 'count' => 10]],
      ['url' => NULL, 'values' => ['value' => 'Badger', 'count' => 20]],
      ['url' => NULL, 'values' => ['value' => 'Duck', 'count' => 15]],
      ['url' => NULL, 'values' => ['value' => 'Alpaca', 'count' => 9]],
    ];
    foreach ($expected_links as $index => $value) {
      $this->assertInternalType('array', $output['tag'][$index]);
      $this->assertEquals($value['values']['value'], $output['tag'][$index]['values']['value']);
      $this->assertEquals($value['values']['count'], $output['tag'][$index]['values']['count']);
    }
  }

  /**
   * Tests get query type.
   */
  public function testGetQueryType() {
    $result = $this->widget->getQueryType($this->queryTypes);
    $this->assertEquals('date', $result);
  }

  /**
   * {@inheritdoc}
   */
  public function testDefaultConfiguration() {
    $default_config = $this->widget->defaultConfiguration();
    $expected = [
      'show_numbers' => FALSE,
      'display_relative' => FALSE,
      'granularity' => 5,
      'date_display' => '',
      'relative_granularity' => 1,
      'relative_text' => TRUE,
    ];
    $this->assertEquals($expected, $default_config);
  }

}
