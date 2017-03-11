<?php

namespace Drupal\facets\Plugin\facets\hierarchy;

use Drupal\facets\Hierarchy\HierarchyPluginBase;

/**
 * Taxonomy hierarchy.
 *
 * @FacetsHierarchy(
 *   id = "taxonomy",
 *   label = @Translation("Taxonomy hierarchy"),
 *   description = @Translation("Hierarchy structure provided by the taxonomy module.")
 * )
 */
class Taxonomy extends HierarchyPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getParentIds($id) {
    $current_tid = $id;
    while ($parent = $this->taxonomyGetParent($current_tid)) {
      $current_tid = $parent;
      $parents[$id][] = $parent;
    }
    return isset($parents[$id]) ? $parents[$id] : [];
  }

  /**
   * {@inheritdoc}
   */
  public function getNestedChildIds($id) {
    $children = &drupal_static(__FUNCTION__, []);
    if (!isset($children[$id])) {
      // TODO: refactor to swap out deprecated db_select.
      $query = db_select('taxonomy_term_hierarchy', 'h');
      $query->addField('h', 'tid');
      $query->condition('h.parent', $id);
      $queried_children = $query->execute()->fetchCol();
      $subchilds = [];
      foreach ($queried_children as $child) {
        $subchilds = array_merge($subchilds, $this->getNestedChildIds($child));
      }
      $children[$id] = array_merge($queried_children, $subchilds);
    }
    return isset($children[$id]) ? $children[$id] : [];
  }

  /**
   * {@inheritdoc}
   */
  public function getChildIds(array $ids) {
    // TODO: refactor to swap out deprecated db_select.
    // TODO: also check if this query does not too much, plain d7 c/p here.
    $result = db_select('taxonomy_term_hierarchy', 'th')
      ->fields('th', array('tid', 'parent'))
      ->condition('th.parent', '0', '>')
      ->condition(db_or()
        ->condition('th.tid', $ids, 'IN')
        ->condition('th.parent', $ids, 'IN')
      )
      ->execute();

    $parents = array();
    foreach ($result as $record) {
      $parents[$record->parent][] = $record->tid;
    }
    return $parents;
  }

  /**
   * Returns the parent tid for a given tid, or false if no parent exists.
   *
   * @param int $tid
   *   A taxonomy term id.
   *
   * @return int|false
   *   Returns FALSE if no parent is found, else parent tid.
   */
  protected function taxonomyGetParent($tid) {
    // TODO: refactor to swap out deprecated db_select.
    $parent = &drupal_static(__FUNCTION__, []);

    if (!isset($parent[$tid])) {
      $query = db_select('taxonomy_term_hierarchy', 'h');
      $query->addField('h', 'parent');
      $query->condition('h.tid', $tid);
      $parent[$tid] = $query->execute()->fetchField();
    }
    return isset($parent[$tid]) ? $parent[$tid] : FALSE;
  }

}
