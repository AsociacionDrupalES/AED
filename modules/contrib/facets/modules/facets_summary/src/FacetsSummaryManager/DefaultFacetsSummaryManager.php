<?php

namespace Drupal\facets_summary\FacetsSummaryManager;

use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\facets\Exception\InvalidProcessorException;
use Drupal\facets\FacetManager\DefaultFacetManager;
use Drupal\facets\FacetSource\FacetSourcePluginManager;
use Drupal\facets_summary\Processor\BuildProcessorInterface;
use Drupal\facets_summary\Processor\ProcessorInterface;
use Drupal\facets_summary\Processor\ProcessorPluginManager;
use Drupal\facets_summary\FacetsSummaryInterface;

/**
 * The facet manager.
 *
 * The manager is responsible for interactions with the Search backend, such as
 * altering the query, it is also responsible for executing and building the
 * facet. It is also responsible for running the processors.
 */
class DefaultFacetsSummaryManager {

  use StringTranslationTrait;

  /**
   * The facet source plugin manager.
   *
   * @var \Drupal\facets\FacetSource\FacetSourcePluginManager
   */
  protected $facetSourcePluginManager;

  /**
   * The processor plugin manager.
   *
   * @var \Drupal\facets_summary\Processor\ProcessorPluginManager
   */
  protected $processorPluginManager;

  /**
   * The Facet Manager.
   *
   * @var \Drupal\facets\FacetManager\DefaultFacetManager
   */
  protected $facetManager;

  /**
   * Constructs a new instance of the DefaultFacetManager.
   *
   * @param \Drupal\facets\FacetSource\FacetSourcePluginManager $facet_source_manager
   *   The facet source plugin manager.
   * @param \Drupal\facets_summary\Processor\ProcessorPluginManager $processor_plugin_manager
   *   The facets summary processor plugin manager.
   * @param \Drupal\facets\FacetManager\DefaultFacetManager $facet_manager
   *   The facet manager service.
   */
  public function __construct(FacetSourcePluginManager $facet_source_manager, ProcessorPluginManager $processor_plugin_manager, DefaultFacetManager $facet_manager) {
    $this->facetSourcePluginManager = $facet_source_manager;
    $this->processorPluginManager = $processor_plugin_manager;
    $this->facetManager = $facet_manager;
  }

  /**
   * Builds a facet and returns it as a renderable array.
   *
   * This method delegates to the relevant plugins to render a facet, it calls
   * out to a widget plugin to do the actual rendering when results are found.
   * When no results are found it calls out to the correct empty result plugin
   * to build a render array.
   *
   * Before doing any rendering, the processors that implement the
   * BuildProcessorInterface enabled on this facet will run.
   *
   * @param \Drupal\facets_summary\FacetsSummaryInterface $facets_summary
   *   The facet we should build.
   *
   * @return array
   *   Facet render arrays.
   *
   * @throws \Drupal\facets\Exception\InvalidProcessorException
   *   Throws an exception when an invalid processor is linked to the facet.
   */
  public function build(FacetsSummaryInterface $facets_summary) {
    // Let the facet_manager build the facets.
    $facetsource_id = $facets_summary->getFacetSourceId();

    $facets = $this->facetManager->getFacetsByFacetSourceId($facetsource_id);
    // Get the current results from the facets and let all processors that
    // trigger on the build step do their build processing.
    // @see \Drupal\facets\Processor\BuildProcessorInterface.
    // @see \Drupal\facets\Processor\SortProcessorInterface.
    $this->facetManager->updateResults($facetsource_id);

    foreach ($facets as $facet) {
      // For clarity, process facets is called each build.
      // The first facet therefor will trigger the processing. Note that
      // processing is done only once, so repeatedly calling this method will
      // not trigger the processing more than once.
      $this->facetManager->processFacets($facetsource_id);
      $this->facetManager->build($facet);
    }

    $build = array(
      '#theme' => 'item_list',
      '#attributes' => array(
        'data-drupal-facets-summary-id' => $facets_summary->id(),
      ),
    );

    $results = [];
    $facets_config = $facets_summary->getFacets();

    // Go through each facet and get the results. After, check if we have to
    // show the counts for each facet and respectively set those to NULL if this
    // should not be shown. We do that here so that we can use our sort
    // processors on all the facet items accordingly.
    foreach ($facets as $facet) {
      $facet_results = $facet->getResults();
      $show_count = $facets_config[$facet->id()]['show_count'];
      if (!$show_count) {
        foreach ($facet_results as $facet_result_id => $facet_result) {
          $facet_results[$facet_result_id]->setCount(NULL);
        }
      }
      $results = array_merge($facet_results, $results);
    }

    // Trigger sort stage.
    $active_sort_processors = [];
    foreach ($facets_summary->getProcessorsByStage(ProcessorInterface::STAGE_SORT) as $processor) {
      $active_sort_processors[] = $processor;
    }

    // Sort the results.
    uasort($results, function ($a, $b) use ($active_sort_processors) {
      $return = 0;
      /** @var \Drupal\facets_summary\Processor\SortProcessorPluginBase $sort_processor */
      foreach ($active_sort_processors as $sort_processor) {
        if ($return = $sort_processor->sortResults($a, $b)) {
          if ($sort_processor->getConfiguration()['sort'] == 'DESC') {
            $return *= -1;
          }
          break;
        }
      }
      return $return;
    });

    /** @var \Drupal\facets\Result\Result $result */
    foreach ($results as $result) {
      if ($result->isActive()) {
        $item = [
          '#theme' => 'facets_result_item',
          '#value' => $result->getDisplayValue(),
          '#show_count' => $result->getCount() !== NULL,
          '#count' => $result->getCount(),
          '#is_active' => TRUE,
        ];
        $item = (new Link($item, $result->getUrl()))->toRenderable();
        $build['#items'][] = $item;
      }
    }

    // Allow our Facets Summary processors to alter the build array in a
    // configured order.
    foreach ($facets_summary->getProcessorsByStage(ProcessorInterface::STAGE_BUILD) as $processor) {
      if (!$processor instanceof BuildProcessorInterface) {
        throw new InvalidProcessorException("The processor {$processor->getPluginDefinition()['id']} has a build definition but doesn't implement the required BuildProcessorInterface interface");
      }
      $build = $processor->build($facets_summary, $build, $facets);
    }

    return $build;
  }

}
