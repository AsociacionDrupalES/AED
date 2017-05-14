<?php

namespace Drupal\facets_custom_widget\Plugin\facets\widget;

use Drupal\facets\Widget\WidgetPluginBase;

/**
 * A simple widget class that returns a simple array of the facet results.
 *
 * @FacetsWidget(
 *   id = "custom_widget",
 *   label = @Translation("Custom widget"),
 *   description = @Translation("Custom widget"),
 * )
 */
class CustomWidget extends WidgetPluginBase {

  /**
   * {@inheritdoc}
   */
  public function isPropertyRequired($name, $type) {
    if ($type == 'processors' && $name == 'hide_non_narrowing_result_processor') {
      return TRUE;
    }
    if ($type == 'settings' && $name == 'show_only_one_result') {
      return TRUE;
    }

    return FALSE;
  }

}
