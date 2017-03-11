<?php

namespace Drupal\facets\Result;

use Drupal\Core\Url;

/**
 * The interface defining what a facet result should look like.
 */
interface ResultInterface {

  /**
   * Returns the raw value as present in the index.
   *
   * @return string
   *   The raw value of the result.
   */
  public function getRawValue();

  /**
   * Returns the display value as present in the index.
   *
   * @return string
   *   The formatted value of the result.
   */
  public function getDisplayValue();

  /**
   * Returns the count for the result.
   *
   * @return int|null
   *   The amount of items for the result or NULL.
   */
  public function getCount();

  /**
   * Sets the count for the result.
   *
   * @param int|null $count
   *   The amount of items for the result or NULL.
   */
  public function setCount($count);

  /**
   * Returns the url.
   *
   * @return \Drupal\Core\Url
   *   The url of the search page with the facet url appended.
   */
  public function getUrl();

  /**
   * Sets the url.
   *
   * @param \Drupal\Core\Url $url
   *   The url of the search page with the facet url appended.
   */
  public function setUrl(Url $url);

  /**
   * Indicates that the value is active (selected).
   *
   * @param bool $active
   *   A boolean indicating the active state.
   */
  public function setActiveState($active);

  /**
   * Returns true if the value is active (selected).
   *
   * @return bool
   *   A boolean indicating the active state.
   */
  public function isActive();

  /**
   * Returns true if the value has active children(selected).
   *
   * @return bool
   *   A boolean indicating the active state of children.
   */
  public function hasActiveChildren();

  /**
   * Overrides the display value of a result.
   *
   * @param string $display_value
   *   Override display value.
   */
  public function setDisplayValue($display_value);

  /**
   * Sets children results.
   *
   * @param \Drupal\facets\Result\ResultInterface[] $children
   *   The children to be added.
   */
  public function setChildren(array $children);

  /**
   * Returns children results.
   *
   * @return \Drupal\facets\Result\ResultInterface[]
   *   The children results.
   */
  public function getChildren();

}
