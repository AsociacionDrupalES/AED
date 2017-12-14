<?php

namespace Drupal\Tests\facets\Unit\Plugin\widget;

use Drupal\facets\Plugin\facets\widget\NumericGranularWidget;

/**
 * Unit test for widget.
 *
 * @group facets
 */
class NumericGranularWidgetTest extends WidgetTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->widget = new NumericGranularWidget();
  }

  /**
   * {@inheritdoc}
   */
  public function testGetQueryType() {
    $result = $this->widget->getQueryType($this->queryTypes);
    $this->assertEquals('numeric', $result);
  }

  /**
   * {@inheritdoc}
   */
  public function testDefaultConfiguration() {
    $default_config = $this->widget->defaultConfiguration();
    $expected = [
      'show_numbers' => FALSE,
      'soft_limit' => 0,
      'granularity' => 0,
      'soft_limit_settings' => [
        'show_less_label' => 'Show less',
        'show_more_label' => 'Show more',
      ],
    ];
    $this->assertEquals($expected, $default_config);
  }

}
