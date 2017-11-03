<?php

namespace Drupal\facets\Plugin\facets\widget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\facets\FacetInterface;
use Drupal\facets\Widget\WidgetPluginBase;

/**
 * The links widget.
 *
 * @FacetsWidget(
 *   id = "links",
 *   label = @Translation("List of links"),
 *   description = @Translation("A simple widget that shows a list of links"),
 * )
 */
class LinksWidget extends WidgetPluginBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['soft_limit' => 0] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function build(FacetInterface $facet) {
    $build = parent::build($facet);
    $soft_limit = (int) $this->getConfiguration()['soft_limit'];
    if ($soft_limit !== 0) {
      $build['#attached']['library'][] = 'facets/soft-limit';
      $build['#attached']['drupalSettings']['facets']['softLimit'][$facet->id()] = $soft_limit;
    }
    if ($facet->getUseHierarchy()) {
      $build['#attached']['library'][] = 'facets/drupal.facets.hierarchical';
    }
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, FacetInterface $facet) {
    $form = parent::buildConfigurationForm($form, $form_state, $facet);

    $options = [50, 40, 30, 20, 15, 10, 5, 3];
    $form['soft_limit'] = [
      '#type' => 'select',
      '#title' => $this->t('Soft limit'),
      '#default_value' => $this->getConfiguration()['soft_limit'],
      '#options' => [0 => $this->t('No limit')] + array_combine($options, $options),
      '#description' => $this->t('Limit the number of displayed facets via JavaScript.'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getQueryType(array $query_types) {
    return $query_types['string'];
  }

}
