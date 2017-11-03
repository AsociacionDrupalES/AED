<?php

namespace Drupal\facets\FacetSource;

/**
 * A facet source that uses Search API as a base.
 */
interface SearchApiFacetSourceInterface extends FacetSourcePluginInterface {

  /**
   * Returns the search_api index.
   *
   * @return \Drupal\search_api\IndexInterface
   *   The Search API index.
   */
  public function getIndex();

  /**
   * Retrieves the Search API display plugin associated with this facet source.
   *
   * @return \Drupal\search_api\Display\DisplayInterface
   *   The Search API display plugin associated with this facet source.
   */
  public function getDisplay();

}
