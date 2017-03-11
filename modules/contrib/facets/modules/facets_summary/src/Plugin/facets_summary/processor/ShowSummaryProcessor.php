<?php

namespace Drupal\facets_summary\Plugin\facets_summary\processor;

use Drupal\facets_summary\FacetsSummaryInterface;
use Drupal\facets_summary\Processor\BuildProcessorInterface;
use Drupal\facets_summary\Processor\ProcessorPluginBase;

/**
 * Provides a processor that hides the facet when the facets were not rendered.
 *
 * @SummaryProcessor(
 *   id = "show_summary",
 *   label = @Translation("Show a summary of all selected facets"),
 *   description = @Translation("When checked, this facet will show an imploded list of all selected facets."),
 *   stages = {
 *     "build" = 50
 *   }
 * )
 */
class ShowSummaryProcessor extends ProcessorPluginBase implements BuildProcessorInterface {

  /**
   * {@inheritdoc}
   */
  public function build(FacetsSummaryInterface $facets_summary, array $build, array $facets) {
    $facets_config = $facets_summary->getFacets();

    if (isset($build['#items'])) {
      /** @var \Drupal\facets\Entity\Facet $facet */
      foreach ($facets as $facet) {
        $facet_summary = [
          '#theme' => 'facets_summary_facet',
          '#label' => $facets_config[$facet->id()]['label'],
          '#separator' => $facets_config[$facet->id()]['separator'],
          '#items' => $facet->getActiveItems(),
          '#facet_id' => $facet->id(),
          '#facet_admin_label' => $facet->getName(),
        ];
        array_unshift($build['#items'], $facet_summary);
      }
    }
    return $build;
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
