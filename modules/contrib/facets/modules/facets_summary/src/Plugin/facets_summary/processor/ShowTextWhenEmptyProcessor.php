<?php

namespace Drupal\facets_summary\Plugin\facets_summary\processor;

use Drupal\Core\Form\FormStateInterface;
use Drupal\facets_summary\FacetsSummaryInterface;
use Drupal\facets_summary\Processor\BuildProcessorInterface;
use Drupal\facets_summary\Processor\ProcessorPluginBase;

/**
 * Provides a processor that hides the facet when the facets were not rendered.
 *
 * @SummaryProcessor(
 *   id = "show_text_when_empty",
 *   label = @Translation("Show a text when there are no results"),
 *   description = @Translation("Show a text when there are no results, otherwise it will hide the block."),
 *   default_enabled = TRUE,
 *   stages = {
 *     "build" = 30
 *   }
 * )
 */
class ShowTextWhenEmptyProcessor extends ProcessorPluginBase implements BuildProcessorInterface {

  /**
   * {@inheritdoc}
   */
  public function build(FacetsSummaryInterface $facets_summary, array $build, array $facets) {
    $processors = $facets_summary->getProcessors();
    $config = isset($processors[$this->getPluginId()]) ? $processors[$this->getPluginId()] : NULL;

    if (!isset($build['#items'])) {
      return [
        '#theme' => 'facets_summary_empty',
        '#message' => [
          '#type' => 'processed_text',
          '#text' => !is_null($config) ? $config->getConfiguration()['text']['value'] : $this->defaultConfiguration()['text']['value'],
          '#format' => !is_null($config) ? $config->getConfiguration()['text']['format'] : $this->defaultConfiguration()['text']['format'],
        ],
      ];
    }
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, FacetsSummaryInterface $facets_summary) {
    // By default, there should be no config form.
    $processors = $facets_summary->getProcessors();
    $config = isset($processors[$this->getPluginId()]) ? $processors[$this->getPluginId()] : NULL;

    $build['text'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Empty text'),
      '#format' => !is_null($config) ? $config->getConfiguration()['text']['format'] : $this->defaultConfiguration()['text']['format'],
      '#editor' => TRUE,
      '#default_value' => !is_null($config) ? $config->getConfiguration()['text']['value'] : $this->defaultConfiguration()['text']['value'],
    ];

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'text' => [
        'format' => 'plain_text',
        'value' => $this->t('There is no current search in progress.'),
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function isHidden() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function isLocked() {
    return FALSE;
  }

}
