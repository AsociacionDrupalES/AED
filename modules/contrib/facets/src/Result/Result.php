<?php

namespace Drupal\facets\Result;

use Drupal\Core\Url;

/**
 * The default implementation of the result interfaces.
 */
class Result implements ResultInterface {

  /**
   * The facet value.
   *
   * @var string
   */
  protected $displayValue;

  /**
   * The raw facet value.
   *
   * @var string
   */
  protected $rawValue;

  /**
   * The facet count.
   *
   * @var int|null
   */
  protected $count = NULL;

  /**
   * The Url object.
   *
   * @var \Drupal\Core\Url
   */
  protected $url;

  /**
   * Is this a selected value or not.
   *
   * @var bool
   */
  protected $active = FALSE;

  /**
   * Children results.
   *
   * @var \Drupal\facets\Result\ResultInterface[]
   */
  protected $children = [];

  /**
   * Constructs a new result value object.
   *
   * @param mixed $raw_value
   *   The raw value.
   * @param mixed $display_value
   *   The formatted value.
   * @param int|null $count
   *   The amount of items or NULL.
   */
  public function __construct($raw_value, $display_value, $count) {
    $this->rawValue = $raw_value;
    $this->displayValue = $display_value;
    $this->count = (int) $count;
  }

  /**
   * {@inheritdoc}
   */
  public function getDisplayValue() {
    return $this->displayValue;
  }

  /**
   * {@inheritdoc}
   */
  public function getRawValue() {
    return $this->rawValue;
  }

  /**
   * {@inheritdoc}
   */
  public function getCount() {
    return $this->count;
  }

  /**
   * {@inheritdoc}
   */
  public function setCount($count) {
    $this->count = $count;
  }

  /**
   * {@inheritdoc}
   */
  public function getUrl() {
    return $this->url;
  }

  /**
   * {@inheritdoc}
   */
  public function setUrl(Url $url) {
    $this->url = $url;
  }

  /**
   * {@inheritdoc}
   */
  public function setActiveState($active) {
    $this->active = $active;
  }

  /**
   * {@inheritdoc}
   */
  public function isActive() {
    return $this->active;
  }

  /**
   * {@inheritdoc}
   */
  public function setDisplayValue($display_value) {
    $this->displayValue = $display_value;
  }

  /**
   * {@inheritdoc}
   */
  public function setChildren(array $children) {
    $this->children = $children;
  }

  /**
   * {@inheritdoc}
   */
  public function getChildren() {
    return $this->children;
  }

  /**
   * Returns true if the value has active children(selected).
   *
   * @return bool
   *   A boolean indicating the active state of children.
   */
  public function hasActiveChildren() {
    foreach ($this->getChildren() as $child) {
      if ($child->isActive() || $child->hasActiveChildren()) {
        return TRUE;
      }
    }
    return FALSE;
  }

}
