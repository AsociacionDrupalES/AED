<?php

namespace Drupal\facets\Plugin\facets\processor;

use Drupal\facets\FacetInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\facets\Processor\BuildProcessorInterface;
use Drupal\facets\Processor\ProcessorPluginBase;

/**
 * Provides a processor for boolean labels.
 *
 * @FacetsProcessor(
 *   id = "boolean_item",
 *   label = @Translation("Boolean item label"),
 *   description = @Translation("Fields that are boolean can use this processor to show the configured On/Off values instead of values 0,1"),
 *   stages = {
 *     "build" = 35
 *   }
 * )
 */
class BooleanItemProcessor extends ProcessorPluginBase implements BuildProcessorInterface {

  /**
   * {@inheritdoc}
   */
  public function build(FacetInterface $facet, array $results) {
    $config = $this->getConfiguration();

    /** @var \Drupal\facets\Result\Result $result */
    foreach ($results as $id => $result) {
      if ($result->getRawValue() == 0) {
        $result->setDisplayValue($config['off_value']);
      }
      elseif ($result->getRawValue() == 1) {
        $result->setDisplayValue($config['on_value']);
      }
    }

    return $results;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, FacetInterface $facet) {
    $processors = $facet->getProcessors();
    $config = isset($processors[$this->getPluginId()]) ? $processors[$this->getPluginId()] : NULL;

    $build['on_value'] = [
      '#title' => $this->t('On value'),
      '#type' => 'textfield',
      '#default_value' => !is_null($config) ? $config->getConfiguration()['on_value'] : $this->defaultConfiguration()['on_value'],
      '#description' => $this->t('Set the display value for "On" facet result.'),
      '#states' => [
        'required' => ['input[name="facet_settings[boolean_item][status]"' => ['checked' => TRUE]],
      ],
    ];

    $build['off_value'] = [
      '#title' => $this->t('Off value'),
      '#type' => 'textfield',
      '#default_value' => !is_null($config) ? $config->getConfiguration()['off_value'] : $this->defaultConfiguration()['off_value'],
      '#description' => $this->t('Set the display value for "Off" facet result.'),
      '#states' => [
        'required' => ['input[name="facet_settings[boolean_item][status]"' => ['checked' => TRUE]],
      ],
    ];

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array $form, FormStateInterface $form_state, FacetInterface $facet) {}

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'on_value' => 'On',
      'off_value' => 'Off',
    ];
  }

}
