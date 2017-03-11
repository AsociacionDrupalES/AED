<?php

namespace Drupal\facets;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Link;
use Drupal\facets_summary\Entity\FacetsSummary;
use Drupal\facets_summary\FacetsSummaryInterface;

/**
 * Builds a listing of facet entities.
 */
class FacetListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function load() {
    $entities = parent::load();
    $this->sortAlphabetically($entities);
    return $entities;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);

    if ($entity instanceof FacetInterface) {

      if ($entity->access('update') && $entity->hasLinkTemplate('edit-form')) {
        $operations['edit'] = array(
          'title' => $this->t('Edit'),
          'weight' => 10,
          'url' => $entity->toUrl('edit-form'),
        );
      }
      if ($entity->access('update') && $entity->hasLinkTemplate('settings-form')) {
        $operations['settings'] = array(
          'title' => $this->t('Facet settings'),
          'weight' => 20,
          'url' => $entity->toUrl('settings-form'),
        );
      }
      if ($entity->access('delete') && $entity->hasLinkTemplate('delete-form')) {
        $operations['delete'] = array(
          'title' => $this->t('Delete'),
          'weight' => 100,
          'url' => $entity->toUrl('delete-form'),
        );
      }
    }
    elseif ($entity instanceof FacetsSummaryInterface) {
      $operations['edit'] = array(
        'title' => $this->t('Edit'),
        'weight' => 10,
        'url' => $entity->toUrl('edit-form'),
      );
      if ($entity->access('update') && $entity->hasLinkTemplate('settings-form')) {
        $operations['settings'] = array(
          'title' => $this->t('Facet Summary settings'),
          'weight' => 20,
          'url' => $entity->toUrl('settings-form'),
        );
      }
      if ($entity->access('delete') && $entity->hasLinkTemplate('delete-form')) {
        $operations['delete'] = array(
          'title' => $this->t('Delete'),
          'weight' => 100,
          'url' => $entity->toUrl('delete-form'),
        );
      }
    }

    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = [
      'type' => $this->t('Type'),
      'title' => [
        'data' => $this->t('Title'),
      ],
    ];
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\facets\FacetInterface $entity */
    $row = parent::buildRow($entity);
    $facet = $entity;

    if ($facet instanceof FacetInterface) {
      return array(
        'data' => array(
          'type' => array(
            'data' => 'Facet',
            'class' => array('facets-type'),
          ),
          'title' => array(
            'data' => array(
              '#type' => 'link',
              '#title' => $facet->label(),
              '#suffix' => '<div>' . $entity->getFieldAlias() . ' - ' . $facet->getWidget()['type'] . '</div>',
            ) + $facet->toUrl('edit-form')->toRenderArray(),
            'class' => array('search-api-title'),
          ),
          'operations' => $row['operations'],
        ),
        'title' => $this->t('ID: @name', array('@name' => $facet->id())),
        'class' => array('facet'),
      );
    }
    elseif ($entity instanceof FacetsSummaryInterface) {
      return array(
        'data' => array(
          'type' => array(
            'data' => 'Facets Summary',
            'class' => array('facets-summary-type'),
          ),
          'title' => array(
            'data' => array(
              '#type' => 'link',
              '#title' => $facet->label(),
            ) + $facet->toUrl('edit-form')->toRenderArray(),
            'class' => array('search-api-title'),
          ),
          'operations' => $row['operations'],
        ),
        'title' => $this->t('ID: @name', array('@name' => $facet->id())),
        'class' => array('facet'),
      );
    }
    return [];
  }

  /**
   * Builds an array of facet sources for display in the overview.
   */
  public function buildFacetSourceRow(array $facet_source = []) {
    return array(
      'data' => array(
        'type' => array(
          'data' => 'Facet source',
          'class' => array('facets-type'),
        ),
        'title' => array(
          'data' => $facet_source['id'],
        ),
        'operations' => array(
          'data' => Link::createFromRoute(
            $this->t('Configure'),
            'entity.facets_facet_source.edit_form',
            ['source_id' => $facet_source['id']]
          )->toRenderable(),
        ),
      ),
      'class' => array('facet-source'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $groups = $this->loadGroups();

    // When no facet sources are found, we should show a message that you can't
    // add facets yet.
    if (empty($groups['facet_source_groups'])) {
      return [
        '#markup' => $this->t(
          'You currently have no facet sources defined. You should start by adding a facet source before creating facets.<br />
           An example of a facet source is a view based on Search API or a Search API page.
           Other modules can also implement a facet source by providing a plugin that implements the FacetSourcePluginInterface.'
        ),
      ];
    }

    $list['#attached']['library'][] = 'facets/drupal.facets.admin_css';

    $list['facet_sources'] = array(
      '#type' => 'table',
      '#header' => $this->buildHeader(),
      '#rows' => array(),
      '#empty' => $groups['lone_facets'] ? '' : $this->t('There are no facet sources or facets defined.'),
      '#attributes' => array(
        'class' => array(
          'facets-groups-list',
        ),
      ),
    );

    foreach ($groups['facet_source_groups'] as $facet_source_group) {
      $list['facet_sources']['#rows'][$facet_source_group['facet_source']['id']] = $this->buildFacetSourceRow($facet_source_group['facet_source']);
      foreach ($facet_source_group['facets'] as $i => $facet) {
        $list['facet_sources']['#rows'][$facet->id()] = $this->buildRow($facet);
      }
    }

    // Output the list of facets without a facet source separately.
    if (!empty($groups['lone_facets'])) {
      $list['lone_facets']['heading']['#markup'] = '<h3>' . $this->t('Facets not currently associated with any facet source') . '</h3>';
      $list['lone_facets']['table'] = array(
        '#type' => 'table',
        '#header' => $this->buildHeader(),
        '#rows' => array(),
      );

      foreach ($groups['lone_facets'] as $entity) {
        $list['lone_facets']['table']['#rows'][$entity->id()] = $this->buildRow($entity);
      }
    }

    return $list;
  }

  /**
   * Loads facet sources and facets, grouped by facet sources.
   *
   * @return \Drupal\Core\Config\Entity\ConfigEntityInterface[][]
   *   An associative array with two keys:
   *   - facet sources: All available facet sources, each followed by all facets
   *     attached to it.
   *   - lone_facets: All facets that aren't attached to any facet source.
   */
  public function loadGroups() {
    $facet_source_plugin_manager = \Drupal::service('plugin.manager.facets.facet_source');
    $facets = $this->storage->loadMultiple();
    $facets_summaries = [];
    if (\Drupal::moduleHandler()->moduleExists('facets_summary')) {
      $facets_summaries = FacetsSummary::loadMultiple();
    }
    $facet_sources = $facet_source_plugin_manager->getDefinitions();

    $this->sortAlphabetically($facets);

    $facet_source_groups = array();
    foreach ($facet_sources as $facet_source) {
      $facet_source_groups[$facet_source['id']] = [
        'facet_source' => $facet_source,
        'facets' => [],
      ];

      foreach ($facets as $facet) {
        /** @var \Drupal\facets\FacetInterface $facet */
        if ($facet->getFacetSourceId() == $facet_source['id']) {
          $facet_source_groups[$facet_source['id']]['facets'][$facet->id()] = $facet;
          // Remove this facet from $facet so it will finally only contain those
          // facets not belonging to any facet_source.
          unset($facets[$facet->id()]);
        }
      }

      foreach ($facets_summaries as $summary) {
        /** @var \Drupal\facets_summary\FacetsSummaryInterface $summary */
        if ($summary->getFacetSourceId() == $facet_source['id']) {
          $facet_source_groups[$facet_source['id']]['facets'][$summary->id()] = $summary;
          // Remove this facet from $facet so it will finally only contain those
          // facets not belonging to any facet_source.
          unset($facets_summaries[$summary->id()]);
        }
      }
    }

    return [
      'facet_source_groups' => $facet_source_groups,
      'lone_facets' => $facets,
    ];
  }

  /**
   * Sorts an array of entities alphabetically.
   *
   * Will preserve the key/value association of the array.
   *
   * @param \Drupal\Core\Config\Entity\ConfigEntityInterface[] $entities
   *   An array of config entities.
   */
  protected function sortAlphabetically(array &$entities) {
    uasort($entities, function (ConfigEntityInterface $a, ConfigEntityInterface $b) {
      return strnatcasecmp($a->label(), $b->label());
    });
  }

}
