<?php

namespace Drupal\facets\Plugin\facets\url_processor;

use Drupal\Core\Url;
use Drupal\facets\FacetInterface;
use Drupal\facets\UrlProcessor\UrlProcessorPluginBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Query string URL processor.
 *
 * @FacetsUrlProcessor(
 *   id = "query_string",
 *   label = @Translation("Query string"),
 *   description = @Translation("Query string is the default Facets URL processor, and uses GET parameters, for example ?f[0]=brand:drupal&f[1]=color:blue")
 * )
 */
class QueryString extends UrlProcessorPluginBase {

  /**
   * A string of how to represent the facet in the url.
   *
   * @var string
   */
  protected $urlAlias;

  /**
   * An array of active filters.
   *
   * @var string[]
   *   An array containing the active filters
   */
  protected $activeFilters = [];

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Request $request) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $request);
    $this->initializeActiveFilters();
  }

  /**
   * {@inheritdoc}
   */
  public function buildUrls(FacetInterface $facet, array $results) {
    // No results are found for this facet, so don't try to create urls.
    if (empty($results)) {
      return [];
    }

    // First get the current list of get parameters.
    $get_params = $this->request->query;

    // When adding/removing a filter the number of pages may have changed,
    // possibly resulting in an invalid page parameter.
    if ($get_params->has('page')) {
      $current_page = $get_params->get('page');
      $get_params->remove('page');
    }

    // Set the url alias from the the facet object.
    $this->urlAlias = $facet->getUrlAlias();

    $request = $this->request;
    if ($facet->getFacetSource()->getPath()) {
      $request = Request::create($facet->getFacetSource()->getPath());
    }

    /** @var \Drupal\facets\Result\ResultInterface[] $results */
    foreach ($results as &$result) {
      // Reset the URL for each result.
      $url = Url::createFromRequest($request);
      $url->setOption('attributes', ['rel' => 'nofollow']);

      // Sets the url for children.
      if ($children = $result->getChildren()) {
        $this->buildUrls($facet, $children);
      }

      $filter_string = $this->urlAlias . $this->getSeparator() . $result->getRawValue();
      $result_get_params = clone $get_params;

      $filter_params = $result_get_params->get($this->filterKey, [], TRUE);
      // If the value is active, remove the filter string from the parameters.
      if ($result->isActive()) {
        foreach ($filter_params as $key => $filter_param) {
          if ($filter_param == $filter_string) {
            unset($filter_params[$key]);
          }
        }
        if ($facet->getEnableParentWhenChildGetsDisabled() && $facet->getUseHierarchy()) {
          // Enable parent id again if exists.
          $parent_ids = $facet->getHierarchyInstance()->getParentIds($result->getRawValue());
          if (isset($parent_ids[0]) && $parent_ids[0]) {
            $filter_params[] = $this->urlAlias . $this->getSeparator() . $parent_ids[0];
          }
        }
      }
      // If the value is not active, add the filter string.
      else {
        $filter_params[] = $filter_string;

        if ($facet->getUseHierarchy()) {
          // If hierarchy is active, unset parent trail and every child when
          // building the enable-link to ensure those are not enabled anymore.
          $parent_ids = $facet->getHierarchyInstance()->getParentIds($result->getRawValue());
          $child_ids = $facet->getHierarchyInstance()->getNestedChildIds($result->getRawValue());
          $parents_and_child_ids = array_merge($parent_ids, $child_ids);
          foreach ($parents_and_child_ids as $id) {
            $filter_params = array_diff($filter_params, [$this->urlAlias . $this->getSeparator() . $id]);
          }
        }
        // Exclude currently active results from the filter params if we are in
        // the show_only_one_result mode.
        if ($facet->getShowOnlyOneResult()) {
          foreach ($results as $result2) {
            if ($result2->isActive()) {
              $active_filter_string = $this->urlAlias . $this->getSeparator() . $result2->getRawValue();
              foreach ($filter_params as $key2 => $filter_param2) {
                if ($filter_param2 == $active_filter_string) {
                  unset($filter_params[$key2]);
                }
              }
            }
          }
        }
      }

      $result_get_params->set($this->filterKey, array_values($filter_params));
      // Grab any route params from the original request.
      $routeParameters = Url::createFromRequest($this->request)
        ->getRouteParameters();
      if (!empty($routeParameters)) {
        $url->setRouteParameters($routeParameters);
      }

      $new_url = clone $url;
      if ($result_get_params->all() !== [$this->filterKey => []]) {
        $new_url->setOption('query', $result_get_params->all());
      }

      $result->setUrl($new_url);
    }

    // Restore page parameter again. See https://www.drupal.org/node/2726455.
    if (isset($current_page)) {
      $get_params->set('page', $current_page);
    }
    return $results;
  }

  /**
   * {@inheritdoc}
   */
  public function setActiveItems(FacetInterface $facet) {
    // Set the url alias from the the facet object.
    $this->urlAlias = $facet->getUrlAlias();

    // Get the filter key of the facet.
    if (isset($this->activeFilters[$this->urlAlias])) {
      foreach ($this->activeFilters[$this->urlAlias] as $value) {
        $facet->setActiveItem(trim($value, '"'));
      }
    }
  }

  /**
   * Initializes the active filters.
   *
   * Get all the filters that are active. This method only get's all the
   * filters but doesn't assign them to facets. In the processFacet method the
   * active values for a specific facet are added to the facet.
   */
  protected function initializeActiveFilters() {
    $url_parameters = $this->request->query;

    // Get the active facet parameters.
    $active_params = $url_parameters->get($this->filterKey, [], TRUE);

    // When an invalid parameter is passed in the url, we can't do anything.
    if (!is_array($active_params)) {
      return;
    }

    // Explode the active params on the separator.
    foreach ($active_params as $param) {
      $explosion = explode($this->getSeparator(), $param);
      $key = array_shift($explosion);
      $value = '';
      while (count($explosion) > 0) {
        $value .= array_shift($explosion);
        if (count($explosion) > 0) {
          $value .= $this->getSeparator();
        }
      }
      if (!isset($this->activeFilters[$key])) {
        $this->activeFilters[$key] = [$value];
      }
      else {
        $this->activeFilters[$key][] = $value;
      }
    }
  }

}
