<?php

namespace Drupal\Tests\facets\Unit\Plugin\processor;

use Drupal\Component\Transliteration\TransliterationInterface;
use Drupal\facets\Plugin\facets\processor\DisplayValueWidgetOrderProcessor;
use Drupal\facets\Result\Result;
use Drupal\Tests\UnitTestCase;

/**
 * Unit test for processor.
 *
 * @group facets
 */
class DisplayValueWidgetOrderProcessorTest extends UnitTestCase {

  /**
   * The processor to be tested.
   *
   * @var \Drupal\facets\Processor\SortProcessorInterface
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
      new Result('thetans', 'thetans', 10),
      new Result('xenu', 'xenu', 5),
      new Result('Tom', 'Tom', 15),
      new Result('Hubbard', 'Hubbard', 666),
      new Result('FALSE', 'FALSE', 1),
      new Result('1977', '1977', 20),
      new Result('2', '2', 22),
    ];

    $transliteration = $this->getMockBuilder(TransliterationInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $transliteration
      ->expects($this->any())
      ->method('removeDiacritics')
      ->will($this->returnArgument(0));

    $this->processor = new DisplayValueWidgetOrderProcessor([], 'display_value_widget_order', [], $transliteration);
  }

  /**
   * Tests sorting.
   */
  public function testSorting() {
    $result_count = $this->processor->sortResults($this->originalResults[0], $this->originalResults[1]);
    $this->assertEquals(-1, $result_count);

    $result_count = $this->processor->sortResults($this->originalResults[1], $this->originalResults[2]);
    $this->assertEquals(1, $result_count);

    $result_count = $this->processor->sortResults($this->originalResults[2], $this->originalResults[3]);
    $this->assertEquals(1, $result_count);

    $result_count = $this->processor->sortResults($this->originalResults[3], $this->originalResults[4]);
    $this->assertEquals(1, $result_count);

    $result_count = $this->processor->sortResults($this->originalResults[4], $this->originalResults[5]);
    $this->assertEquals(1, $result_count);

    $result_count = $this->processor->sortResults($this->originalResults[5], $this->originalResults[6]);
    $this->assertEquals(1, $result_count);

    $result_count = $this->processor->sortResults($this->originalResults[6], $this->originalResults[5]);
    $this->assertEquals(-1, $result_count);

    $result_count = $this->processor->sortResults($this->originalResults[3], $this->originalResults[3]);
    $this->assertEquals(0, $result_count);
  }

  /**
   * Tests that sorting uses the display value.
   */
  public function testUseActualDisplayValue() {
    $original = [
      new Result('bb_test', 'Test AA', 10),
      new Result('aa_test', 'Test BB', 10),
    ];

    $sorted_results = $this->processor->sortResults($original[0], $original[1]);
    $this->assertEquals(-1, $sorted_results);

    $sorted_results = $this->processor->sortResults($original[1], $original[0]);
    $this->assertEquals(1, $sorted_results);
  }

  /**
   * Tests configuration.
   */
  public function testDefaultConfiguration() {
    $config = $this->processor->defaultConfiguration();
    $this->assertEquals(['sort' => 'ASC'], $config);
  }

}
