<?php

namespace Drupal\facets\Plugin\facets\query_type;

use Drupal\facets\QueryType\QueryTypeRangeBase;

/**
 * Basic support for numeric facets grouping by a granularity value.
 *
 * Requires the facet widget to set configuration value keyed with
 * granularity.
 *
 * @FacetsQueryType(
 *   id = "search_api_granular",
 *   label = @Translation("Numeric query with set granularity"),
 * )
 */
class SearchApiGranular extends QueryTypeRangeBase {

  /**
   * {@inheritdoc}
   */
  public function calculateRange($value) {
    return [
      'start' => $value,
      'stop' => $value + $this->getGranularity(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function calculateResultFilter($value) {
    return [
      'display' => $value - ($value % $this->getGranularity()),
      'raw' => $value - ($value % $this->getGranularity()) ,
    ];
  }

  /**
   * Looks at the configuration for this facet to determine the granularity.
   *
   * Default behaviour an integer for the steps that the facet works in.
   *
   * @return mixed
   *   If not an integer the inheriting class needs to deal with calculations.
   */
  protected function getGranularity() {
    return $this->facet->getWidgetInstance()->getConfiguration()['granularity'];
  }

}
