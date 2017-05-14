<?php

namespace Drupal\Tests\facets\Unit\Plugin\widget;

use Drupal\facets\Entity\Facet;
use Drupal\facets\Plugin\facets\widget\CheckboxWidget;

/**
 * Unit test for widget.
 *
 * @group facets
 */
class CheckboxWidgetTest extends WidgetTestBase {

  /**
   * Creates a new processor object for use in the tests.
   */
  protected function setUp() {
    parent::setUp();

    $this->widget = new CheckboxWidget(['show_numbers' => TRUE]);
  }

  /**
   * Tests widget without filters.
   */
  public function testNoFilterResults() {
    $facet = new Facet([], 'facets_facet');
    $facet->setResults($this->originalResults);

    $output = $this->widget->build($facet);

    $this->assertInternalType('array', $output);
    $this->assertCount(4, $output['#items']);

    $this->assertEquals(['js-facets-checkbox-links'], $output['#attributes']['class']);

    $expected_links = [
      $this->buildLinkAssertion('Llama', 10),
      $this->buildLinkAssertion('Badger', 20),
      $this->buildLinkAssertion('Duck', 15),
      $this->buildLinkAssertion('Alpaca', 9),
    ];
    foreach ($expected_links as $index => $value) {
      $this->assertInternalType('array', $output['#items'][$index]);
      $this->assertEquals($value, $output['#items'][$index]['#title']);
      $this->assertInternalType('array', $output['#items'][$index]['#title']);
      $this->assertEquals('link', $output['#items'][$index]['#type']);
      $this->assertEquals(['facet-item'], $output['#items'][$index]['#wrapper_attributes']['class']);
    }
  }

  /**
   * Tests default configuration.
   */
  public function testDefaultConfiguration() {
    $default_config = $this->widget->defaultConfiguration();
    $this->assertEquals(['show_numbers' => FALSE, 'soft_limit' => 0], $default_config);
  }

}
