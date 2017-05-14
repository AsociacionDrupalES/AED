<?php

namespace Drupal\facets\UrlProcessor;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\facets\Exception\InvalidProcessorException;
use Drupal\facets\Processor\ProcessorPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * A base class for plugins that implements most of the boilerplate.
 */
abstract class UrlProcessorPluginBase extends ProcessorPluginBase implements UrlProcessorInterface, ContainerFactoryPluginInterface {

  /**
   * The query string variable.
   *
   * @var string
   *   The query string variable that holds all the facet information.
   */
  protected $filterKey = 'f';

  /**
   * The url separator variable.
   *
   * @var string
   *   The sepatator to use between field and value.
   */
  protected $separator;

  /**
   * The clone of the current request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * {@inheritdoc}
   */
  public function getFilterKey() {
    return $this->filterKey;
  }

  /**
   * {@inheritdoc}
   */
  public function getSeparator() {
    return $this->separator;
  }

  /**
   * Constructs a new instance of the class.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   A request object for the current request.
   *
   * @throws \Drupal\facets\Exception\InvalidProcessorException
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Request $request) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->request = clone $request;

    if (!isset($configuration['facet'])) {
      throw new InvalidProcessorException("The url processor doesn't have the required 'facet' in the configuration array.");
    }

    /** @var \Drupal\facets\FacetInterface $facet */
    $facet = $configuration['facet'];

    /** @var \Drupal\facets\FacetSourceInterface $facet_source_config */
    $facet_source_config = $facet->getFacetSourceConfig();

    $this->filterKey = $facet_source_config->getFilterKey() ?: 'f';

    // Set the separator to the predefined colon char but override if passed
    // along as part of the plugin configuration.
    $this->separator = ':';
    if (isset($configuration['separator'])) {
      $this->separator = $configuration['separator'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('request_stack')->getMasterRequest()
    );
  }

}
