<?php

namespace Drupal\aed_migration\Plugin\migrate\source;

use Drupal\migrate\Row;
use Drupal\taxonomy\Plugin\migrate\source\Term;

/**
 * Drupal 7 terms source from database.
 *
 * @MigrateSource(
 *   id = "aed_terms"
 * )
 */
class Terms extends Term {

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {

    //Only import 'video_ponente' terms.
    $vid = $this->select('taxonomy_vocabulary', 't')
      ->fields('t', ['vid'])
      ->condition('machine_name', 'video_ponente')
      ->execute()->fetchCol();

    if (!in_array($row->getSourceProperty('vid'), $vid)){
      return FALSE;
    }

    return parent::prepareRow($row);

  }

}
