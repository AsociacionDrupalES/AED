<?php

namespace Drupal\Tests\facets\Unit\Plugin\widget;

use Drupal\Core\Url;
use Drupal\facets\Entity\Facet;
use Drupal\facets\Plugin\facets\widget\CheckboxWidget;
use Drupal\facets\Result\Result;
use Drupal\Tests\UnitTestCase;

/**
 * Unit test for widget.
 *
 * @group facets
 */
class CheckboxWidgetTest extends UnitTestCase {

  /**
   * The processor to be tested.
   *
   * @var \Drupal\facets\Plugin\facets\widget\CheckboxWidget
   */
  protected $widget;

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

    /** @var \Drupal\facets\Result\Result[] $original_results */
    $original_results = [
      new Result('llama', 'Llama', 10),
      new Result('badger', 'Badger', 20),
      new Result('duck', 'Duck', 15),
      new Result('alpaca', 'Alpaca', 9),
    ];

    foreach ($original_results as $original_result) {
      $original_result->setUrl(new Url('test'));
    }
    $this->originalResults = $original_results;

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

  /**
   * Build a formattable markup object to use in the other tests.
   *
   * @param string $text
   *   Text to display.
   * @param int $count
   *   Number of results.
   * @param bool $active
   *   Link is active.
   * @param bool $show_numbers
   *   Numbers are displayed.
   *
   * @return array
   *   A render array.
   */
  protected function buildLinkAssertion($text, $count = 0, $active = FALSE, $show_numbers = TRUE) {
    return [
      '#theme' => 'facets_result_item',
      '#value' => $text,
      '#show_count' => $show_numbers && ($count !== NULL),
      '#count' => $count,
      '#is_active' => $active,
    ];
  }

}
