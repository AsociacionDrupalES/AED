<?php

namespace Drupal\Tests\facets\Unit\Plugin\widget;

use Drupal\Core\Field\WidgetPluginManager;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Url;
use Drupal\facets\Entity\Facet;
use Drupal\facets\Plugin\facets\widget\ArrayWidget;
use Drupal\facets\Result\Result;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Unit test for widget.
 *
 * @group facets
 */
class ArrayWidgetTest extends UnitTestCase {

  /**
   * The processor to be tested.
   *
   * @var \Drupal\facets\Widget\WidgetPluginInterface
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

    // Creates a mocked container, so we can access string translation.
    $container = $this->prophesize(ContainerInterface::class);
    $string_translation = $this->prophesize(TranslationInterface::class);
    $url_generator = $this->prophesize(UrlGeneratorInterface::class);
    $widget_manager = $this->prophesize(WidgetPluginManager::class);

    $container->get('plugin.manager.facets.widget')->willReturn($widget_manager->reveal());
    $container->get('string_translation')->willReturn($string_translation->reveal());
    $container->get('url_generator')->willReturn($url_generator->reveal());
    \Drupal::setContainer($container->reveal());

    $this->widget = new ArrayWidget(['show_numbers' => 1]);
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

}
