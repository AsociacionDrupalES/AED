<?php

namespace Drupal\facets\Plugin\facets\facet_source;

use Drupal\facets\FacetSource\FacetSourceDeriverBase;

/**
 * Derives a facet source plugin definition for every Search API display plugin.
 *
 * This facet source supports all search api display sources.
 *
 * @see \Drupal\facets\Plugin\facets\facet_source\SearchApi
 */
class SearchApiViewsDeriver extends FacetSourceDeriverBase {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $base_plugin_id = $base_plugin_definition['id'];

    $search_api_displays = $this->getSearchApiDisplayPluginManager();

    $plugin_derivatives = array();
    foreach ($search_api_displays->getDefinitions() as $display) {
      // Avoid providing corrupted displays.
      if (isset($display['view_id']) && isset($display['view_display']) && isset($display['label'])) {
        $machine_name = $display['view_id'] . '__' . $display['view_display'];

        $plugin_derivatives[$machine_name] = [
          'id' => $base_plugin_id . ':' . $machine_name,
          'label' => $display['label'],
          'description' => $this->t('Provides a facet source.'),
          'view_id' => $display['view_id'],
          'view_display' => $display['view_display'],
        ] + $base_plugin_definition;

        $arguments = [
          '%view' => $display['label'],
          '%display' => $display['view_display'],
        ];
        $sources[] = $this->t('Search API view: %view, display: %display', $arguments);
      }
    }

    uasort($plugin_derivatives, array($this, 'compareDerivatives'));

    $this->derivatives[$base_plugin_id] = $plugin_derivatives;
    return $this->derivatives[$base_plugin_id];
  }

}
