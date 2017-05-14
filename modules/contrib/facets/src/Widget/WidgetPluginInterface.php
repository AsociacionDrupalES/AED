<?php

namespace Drupal\facets\Widget;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\facets\FacetInterface;

/**
 * Provides an interface describing the a facet widgets.
 */
interface WidgetPluginInterface extends ConfigurablePluginInterface {

  /**
   * Builds the facet widget for rendering.
   *
   * @param \Drupal\facets\FacetInterface $facet
   *   The facet we need to build.
   *
   * @return array
   *   A renderable array.
   */
  public function build(FacetInterface $facet);

  /**
   * Picks the preferred query type for this widget.
   *
   * @param string[] $query_types
   *   An array keyed with query type name and it's plugin class to load.
   *
   * @return string
   *   The query type plugin class to load.
   */
  public function getQueryType(array $query_types);

  /**
   * Checks is a specific property is required for this widget.
   *
   * This works for base properties (show_only_one_result,
   * only_visible_when_facet_source_is_visible) or one the processors.
   *
   * @param string $name
   *   The name of the property. Something like
   *   'hide_non_narrowing_result_processor' or 'show_only_one_result'.
   * @param string $type
   *   The type of the property. Either 'processors' or 'settings'. Another
   *   value will not be picked up by the widgets.
   *
   * @return bool
   *   True when the property is required, false by default.
   */
  public function isPropertyRequired($name, $type);

  /**
   * Provides a configuration form for this widget.
   *
   * @param array $form
   *   A form API form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   * @param \Drupal\facets\FacetInterface $facet
   *   The facet entitu.
   *
   * @return array
   *   A renderable form array.
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, FacetInterface $facet);

}
