<?php

namespace Drupal\Tests\search_api\Unit\Plugin\Processor;

use Drupal\search_api\IndexInterface;
use Drupal\search_api\Item\Field;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Plugin\search_api\data_type\value\TextValue;
use Drupal\search_api\Plugin\search_api\processor\Stemmer;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the "Stemmer" processor.
 *
 * @coversDefaultClass \Drupal\search_api\Plugin\search_api\processor\Stemmer
 *
 * @group search_api
 */
class StemmerTest extends UnitTestCase {

  use ProcessorTestTrait;
  use TestItemsTrait;

  /**
   * Creates a new processor object for use in the tests.
   */
  protected function setUp() {
    parent::setUp();

    $this->setUpMockContainer();

    $this->processor = new Stemmer(array(), 'string', array());
  }

  /**
   * Tests the preprocessIndexItems() method.
   *
   * @covers ::preprocessIndexItems
   */
  public function testPreprocessIndexItems() {
    $index = $this->getMock(IndexInterface::class);

    $item_en = $this->getMockBuilder(ItemInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $item_en->method('getLanguage')->willReturn('en');
    $field_en = new Field($index, 'foo');
    $field_en->setType('text');
    $field_en->setValues(array(
      new TextValue('ties'),
    ));
    $item_en->method('getFields')->willReturn(array('foo' => $field_en));

    $item_de = $this->getMockBuilder(ItemInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $item_de->method('getLanguage')->willReturn('de');
    $field_de = new Field($index, 'foo');
    $field_de->setType('text');
    $field_de->setValues(array(
      new TextValue('ties'),
    ));
    $item_de->method('getFields')->willReturn(array('foo' => $field_de));

    $items = array($item_en, $item_de);
    $this->processor->preprocessIndexItems($items);

    /** @var \Drupal\search_api\Plugin\search_api\data_type\value\TextValueInterface $value */
    $value = $field_en->getValues()[0];
    $this->assertEquals('tie', $value->toText());
    $value = $field_de->getValues()[0];
    $this->assertEquals('ties', $value->toText());
  }

  /**
   * Tests the process() method.
   *
   * @param string $passed_value
   *   The value that should be passed into process().
   * @param string $expected_value
   *   The expected processed value.
   *
   * @covers ::process
   *
   * @dataProvider processDataProvider
   */
  public function testProcess($passed_value, $expected_value) {
    $this->invokeMethod('process', array(&$passed_value));
    $this->assertEquals($passed_value, $expected_value);
  }

  /**
   * Provides sets of arguments for testProcess().
   *
   * @return array[]
   *   Arrays of arguments for testProcess().
   */
  public function processDataProvider() {
    return array(
      array('Yo', 'yo'),
      array('ties', 'tie'),
      array('cries', 'cri'),
      array('exceed', 'exceed'),
      array('consign', 'consign'),
      array('consigned', 'consign'),
      array('consigning', 'consign'),
      array('consignment', 'consign'),
      array('consist', 'consist'),
      array('consisted', 'consist'),
      array('consistency', 'consist'),
      array('consistent', 'consist'),
      array('consistently', 'consist'),
      array('consisting', 'consist'),
      array('consists', 'consist'),
      array('consolation', 'consol'),
      array('consolations', 'consol'),
      array('consolatory', 'consolatori'),
      array('console', 'consol'),
      array('consoled', 'consol'),
      array('consoles', 'consol'),
      array('consolidate', 'consolid'),
      array('consolidated', 'consolid'),
      array('consolidating', 'consolid'),
      array('consoling', 'consol'),
      array('consolingly', 'consol'),
      array('consols', 'consol'),
      array('consonant', 'conson'),
      array('consort', 'consort'),
      array('consorted', 'consort'),
      array('consorting', 'consort'),
      array('conspicuous', 'conspicu'),
      array('conspicuously', 'conspicu'),
      array('conspiracy', 'conspiraci'),
      array('conspirator', 'conspir'),
      array('conspirators', 'conspir'),
      array('conspire', 'conspir'),
      array('conspired', 'conspir'),
      array('conspiring', 'conspir'),
      array('constable', 'constabl'),
      array('constables', 'constabl'),
      array('constance', 'constanc'),
      array('constancy', 'constanc'),
      array('constant', 'constant'),
      array('knack', 'knack'),
      array('knackeries', 'knackeri'),
      array('knacks', 'knack'),
      array('knag', 'knag'),
      array('knave', 'knave'),
      array('knaves', 'knave'),
      array('knavish', 'knavish'),
      array('kneaded', 'knead'),
      array('kneading', 'knead'),
      array('knee', 'knee'),
      array('kneel', 'kneel'),
      array('kneeled', 'kneel'),
      array('kneeling', 'kneel'),
      array('kneels', 'kneel'),
      array('knees', 'knee'),
      array('knell', 'knell'),
      array('knelt', 'knelt'),
      array('knew', 'knew'),
      array('knick', 'knick'),
      array('knif', 'knif'),
      array('knife', 'knife'),
      array('knight', 'knight'),
      array('knightly', 'knight'),
      array('knights', 'knight'),
      array('knit', 'knit'),
      array('knits', 'knit'),
      array('knitted', 'knit'),
      array('knitting', 'knit'),
      array('knives', 'knive'),
      array('knob', 'knob'),
      array('knobs', 'knob'),
      array('knock', 'knock'),
      array('knocked', 'knock'),
      array('knocker', 'knocker'),
      array('knockers', 'knocker'),
      array('knocking', 'knock'),
      array('knocks', 'knock'),
      array('knopp', 'knopp'),
      array('knot', 'knot'),
      array('knots', 'knot'),
      // This can happen when Tokenizer is off during indexing, or when
      // preprocessing a search query with quoted keywords.
      array(" \tExtra  spaces \rappeared \n", 'extra space appear'),
      array("\tspaced-out  \r\n", 'space out'),
    );
  }

}
