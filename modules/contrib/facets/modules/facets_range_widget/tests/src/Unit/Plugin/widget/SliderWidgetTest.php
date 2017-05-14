<?php

namespace Drupal\Tests\facets_range_widget\Unit\Plugin\widget;

use Drupal\facets_range_widget\Plugin\facets\widget\SliderWidget;
use Drupal\Tests\facets\Unit\Plugin\widget\WidgetTestBase;

/**
 * Unit test for widget.
 *
 * @group facets
 */
class SliderWidgetTest extends WidgetTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->widget = new SliderWidget();
  }

  /**
   * {@inheritdoc}
   */
  public function testGetQueryType() {
    $result = $this->widget->getQueryType($this->queryTypes);
    $this->assertEquals('string', $result);
  }

  /**
   * {@inheritdoc}
   */
  public function testDefaultConfiguration() {
    $default_config = $this->widget->defaultConfiguration();
    $expected = [
      'show_numbers' => FALSE,
      'prefix' => '',
      'suffix' => '',
      'min_type' => 'search_result',
      'min_value' => 0,
      'max_type' => 'search_result',
      'max_value' => 10,
      'step' => 1,
    ];
    $this->assertEquals($expected, $default_config);
  }

}
