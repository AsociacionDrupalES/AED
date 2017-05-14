<?php

namespace Drupal\Tests\facets\Unit\Plugin\widget;

use Drupal\facets\Entity\Facet;
use Drupal\facets\Plugin\facets\widget\LinksWidget;
use Drupal\facets\Result\Result;

/**
 * Unit test for widget.
 *
 * @group facets
 */
class LinksWidgetTest extends WidgetTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->widget = new LinksWidget();
  }

  /**
   * Tests widget without filters.
   */
  public function testNoFilterResults() {
    $facet = new Facet([], 'facets_facet');
    $facet->setResults($this->originalResults);

    $this->widget->setConfiguration(['show_numbers' => TRUE]);
    $output = $this->widget->build($facet);

    $this->assertInternalType('array', $output);
    $this->assertCount(4, $output['#items']);

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
   * Test widget with 2 active items.
   */
  public function testActiveItems() {
    $original_results = $this->originalResults;
    $original_results[0]->setActiveState(TRUE);
    $original_results[3]->setActiveState(TRUE);

    $facet = new Facet([], 'facets_facet');
    $facet->setResults($original_results);

    $this->widget->setConfiguration(['show_numbers' => TRUE]);
    $output = $this->widget->build($facet);

    $this->assertInternalType('array', $output);
    $this->assertCount(4, $output['#items']);

    $expected_links = [
      $this->buildLinkAssertion('Llama', 10, TRUE),
      $this->buildLinkAssertion('Badger', 20),
      $this->buildLinkAssertion('Duck', 15),
      $this->buildLinkAssertion('Alpaca', 9, TRUE),
    ];
    foreach ($expected_links as $index => $value) {
      $this->assertInternalType('array', $output['#items'][$index]);
      $this->assertEquals($value, $output['#items'][$index]['#title']);
      $this->assertEquals('link', $output['#items'][$index]['#type']);
      if ($index === 0 || $index === 3) {
        $this->assertEquals(['is-active'], $output['#items'][$index]['#attributes']['class']);
      }
      $this->assertEquals(['facet-item'], $output['#items'][$index]['#wrapper_attributes']['class']);
    }
  }

  /**
   * Tests widget, make sure hiding and showing numbers works.
   */
  public function testHideNumbers() {
    $original_results = $this->originalResults;
    $original_results[1]->setActiveState(TRUE);

    $facet = new Facet([], 'facets_facet');
    $facet->setResults($original_results);

    $this->widget->setConfiguration(['show_numbers' => FALSE]);
    $output = $this->widget->build($facet);

    $this->assertInternalType('array', $output);
    $this->assertCount(4, $output['#items']);

    $expected_links = [
      $this->buildLinkAssertion('Llama', 10, FALSE, FALSE),
      $this->buildLinkAssertion('Badger', 20, TRUE, FALSE),
      $this->buildLinkAssertion('Duck', 15, FALSE, FALSE),
      $this->buildLinkAssertion('Alpaca', 9, FALSE, FALSE),
    ];
    foreach ($expected_links as $index => $value) {
      $this->assertInternalType('array', $output['#items'][$index]);
      $this->assertEquals($value, $output['#items'][$index]['#title']);
      $this->assertEquals('link', $output['#items'][$index]['#type']);
      if ($index === 1) {
        $this->assertEquals(['is-active'], $output['#items'][$index]['#attributes']['class']);
      }
      $this->assertEquals(['facet-item'], $output['#items'][$index]['#wrapper_attributes']['class']);
    }

    // Enable the 'show_numbers' setting again to make sure that the switch
    // between those settings works.
    $this->widget->setConfiguration(['show_numbers' => TRUE]);

    $output = $this->widget->build($facet);

    $this->assertInternalType('array', $output);
    $this->assertCount(4, $output['#items']);

    $expected_links = [
      $this->buildLinkAssertion('Llama', 10),
      $this->buildLinkAssertion('Badger', 20, TRUE),
      $this->buildLinkAssertion('Duck', 15),
      $this->buildLinkAssertion('Alpaca', 9),
    ];
    foreach ($expected_links as $index => $value) {
      $this->assertInternalType('array', $output['#items'][$index]);
      $this->assertEquals($value, $output['#items'][$index]['#title']);
      $this->assertEquals('link', $output['#items'][$index]['#type']);
      if ($index === 1) {
        $this->assertEquals(['is-active'], $output['#items'][$index]['#attributes']['class']);
      }
      $this->assertEquals(['facet-item'], $output['#items'][$index]['#wrapper_attributes']['class']);
    }
  }

  /**
   * Tests for links widget with children.
   */
  public function testChildren() {
    $original_results = $this->originalResults;

    $child = new Result('snake', 'Snake', 5);
    $original_results[1]->setActiveState(TRUE);
    $original_results[1]->setChildren([$child]);

    $facet = new Facet([], 'facets_facet');
    $facet->setResults($original_results);

    $this->widget->setConfiguration(['show_numbers' => TRUE]);
    $output = $this->widget->build($facet);

    $this->assertInternalType('array', $output);
    $this->assertCount(4, $output['#items']);

    $expected_links = [
      $this->buildLinkAssertion('Llama', 10),
      $this->buildLinkAssertion('Badger', 20, TRUE),
      $this->buildLinkAssertion('Duck', 15),
      $this->buildLinkAssertion('Alpaca', 9),
    ];
    foreach ($expected_links as $index => $value) {
      $this->assertInternalType('array', $output['#items'][$index]);
      $this->assertEquals($value, $output['#items'][$index]['#title']);
      $this->assertEquals('link', $output['#items'][$index]['#type']);
      if ($index === 1) {
        $this->assertEquals(['is-active'], $output['#items'][$index]['#attributes']['class']);
        $this->assertEquals(['facet-item', 'facet-item--expanded'], $output['#items'][$index]['#wrapper_attributes']['class']);
      }
      else {
        $this->assertEquals(['facet-item'], $output['#items'][$index]['#wrapper_attributes']['class']);
      }
    }

  }

}
