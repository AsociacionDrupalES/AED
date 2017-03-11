<?php

namespace Drupal\facets\Widget;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Plugin\PluginBase;
use Drupal\facets\FacetInterface;
use Drupal\facets\Result\Result;
use Drupal\facets\Result\ResultInterface;

/**
 * A base class for widgets that implements most of the boilerplate.
 */
abstract class WidgetPluginBase extends PluginBase implements WidgetPluginInterface {

  /**
   * Show the amount of results next to the result.
   *
   * @var bool
   */
  protected $showNumbers;

  /**
   * The facet the widget is being built for.
   *
   * @var \Drupal\facets\FacetInterface
   */
  protected $facet;

  /**
   * Constructs a plugin object.
   *
   * @param array $configuration
   *   (optional) An optional configuration to be passed to the plugin. If
   *   empty, the plugin is initialized with its default plugin configuration.
   */
  public function __construct(array $configuration = []) {
    $plugin_id = $this->getPluginId();
    $plugin_definition = $this->getPluginDefinition();
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->setConfiguration($configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function build(FacetInterface $facet) {
    $this->facet = $facet;

    $items = array_map(function (Result $result) {
      if (empty($result->getUrl())) {
        return $this->buildResultItem($result);
      }
      else {
        return $this->buildListItems($result);
      }
    }, $facet->getResults());

    return [
      '#theme' => 'item_list',
      '#items' => $items,
      '#attributes' => [
        'data-drupal-facet-id' => $facet->id(),
        'data-drupal-facet-alias' => $facet->getUrlAlias(),
      ],
      '#cache' => [
        'contexts' => [
          'url.path',
          'url.query_args',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['show_numbers' => FALSE];
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = NestedArray::mergeDeep(
      $this->defaultConfiguration(),
      $configuration
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function getQueryType(array $query_types) {
    return $query_types['string'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, FacetInterface $facet) {
    $form['show_numbers'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show the amount of results'),
      '#default_value' => $this->getConfiguration()['show_numbers'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return [];
  }

  /**
   * Builds a renderable array of result items.
   *
   * @param \Drupal\facets\Result\ResultInterface $result
   *   A result item.
   *
   * @return array
   *   A renderable array of the result.
   */
  protected function buildListItems(ResultInterface $result) {
    $classes = ['facet-item'];
    $items = $this->prepareLink($result);

    $children = $result->getChildren();
    // Check if we need to expand this result.
    if ($children && ($this->facet->getExpandHierarchy() || $result->isActive() || $result->hasActiveChildren())) {

      $child_items = [];
      $classes[] = 'facet-item--expanded';
      foreach ($children as $child) {
        $child_items[] = $this->buildListItems($child);
      }

      $items['children'] = [
        '#theme' => 'item_list',
        '#items' => $child_items,
      ];

      if ($result->hasActiveChildren()) {
        $classes[] = 'facet-item--active-trail';
      }

    }
    else {
      if ($children) {
        $classes[] = 'facet-item--collapsed';
      }
    }

    if ($result->isActive()) {
      $items['#attributes'] = ['class' => 'is-active'];
    }

    $items['#wrapper_attributes'] = ['class' => $classes];
    $items['#attributes']['data-drupal-facet-item-id'] = $this->facet->getUrlAlias() . '-' . $result->getRawValue();
    $items['#attributes']['data-drupal-facet-item-value'] = $result->getRawValue();
    return $items;
  }

  /**
   * Returns the text or link for an item.
   *
   * @param \Drupal\facets\Result\ResultInterface $result
   *   A result item.
   *
   * @return array
   *   The item as a render array.
   */
  protected function prepareLink(ResultInterface $result) {
    $item = $this->buildResultItem($result);

    if (!is_null($result->getUrl())) {
      $item = (new Link($item, $result->getUrl()))->toRenderable();
    }

    return $item;
  }

  /**
   * Builds a facet result item.
   *
   * @param \Drupal\facets\Result\ResultInterface $result
   *   The result item.
   *
   * @return array
   *   The facet result item as a render array.
   */
  protected function buildResultItem(ResultInterface $result) {
    $count = $result->getCount();
    return [
      '#theme' => 'facets_result_item',
      '#is_active' => $result->isActive(),
      '#value' => $result->getDisplayValue(),
      '#show_count' => $this->getConfiguration()['show_numbers'] && ($count !== NULL),
      '#count' => $count,
    ];
  }

}
