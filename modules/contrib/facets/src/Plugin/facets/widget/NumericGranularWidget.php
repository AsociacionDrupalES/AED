<?php

namespace Drupal\facets\Plugin\facets\widget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\facets\FacetInterface;

/**
 * Basic granular widget.
 *
 * @FacetsWidget(
 *   id = "numericgranular",
 *   label = @Translation("Granular numeric list"),
 *   description = @Translation("List of numbers grouped in steps."),
 * )
 */
class NumericGranularWidget extends LinksWidget {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'granularity' => 0,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, FacetInterface $facet) {
    $configuration = $this->getConfiguration();

    $form += parent::buildConfigurationForm($form, $form_state, $facet);

    $form['granularity'] = [
      '#type' => 'number',
      '#title' => $this->t('Granularity'),
      '#default_value' => $configuration['granularity'],
      '#description' => $this->t('The numeric size of the steps to group the result facets in.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getQueryType() {
    return 'numeric';
  }

}
