<?php

namespace Drupal\search_api\Plugin\views\relationship;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\relationship\RelationshipPluginBase;

/**
 * Views relationship plugin for datasources.
 *
 * @ingroup views_relationship_handlers
 *
 * @ViewsRelationship("search_api")
 */
class SearchApiRelationship extends RelationshipPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['skip_access']['default'] = FALSE;
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['required']['#access'] = FALSE;

    $form['skip_access'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Skip access checks'),
      '#description' => $this->t('Do not verify that the user has access to the entities referenced through this relationship. This will allow you to display data to the user to which they normally would not have access. This should therefore be used with care.'),
      '#default_value' => $this->options['skip_access'],
      '#weight' => -1,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $this->alias = $this->field;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    $dependencies = array();

    if (!empty($this->definition['entity type'])) {
      $entity_type = \Drupal::entityTypeManager()->getDefinition($this->definition['entity type']);
      if ($entity_type) {
        $dependencies['module'][] = $entity_type->getProvider();
      }
    }

    return $dependencies;
  }

}
