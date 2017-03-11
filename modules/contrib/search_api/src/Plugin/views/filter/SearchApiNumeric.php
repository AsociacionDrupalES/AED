<?php

namespace Drupal\search_api\Plugin\views\filter;

use Drupal\Core\Cache\UncacheableDependencyTrait;
use Drupal\views\Plugin\views\filter\NumericFilter;

/**
 * Defines a filter for filtering on numeric values.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("search_api_numeric")
 */
class SearchApiNumeric extends NumericFilter {

  use UncacheableDependencyTrait;
  use SearchApiFilterTrait;

  /**
   * {@inheritdoc}
   */
  public function operators() {
    $operators = parent::operators();
    unset($operators['regular_expression']);
    return $operators;
  }

  /**
   * {@inheritdoc}
   */
  protected function opEmpty($field) {
    $this->getQuery()->addCondition($this->realField, NULL, $this->operator == 'empty' ? '=' : '<>', $this->options['group']);
  }

}
