<?php

namespace Drupal\search_api\Plugin\search_api\display;

use Drupal\search_api\Display\DisplayPluginBase;

/**
 * Provides a base class for Views displays.
 */
abstract class ViewsDisplayBase extends DisplayPluginBase {

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    $dependencies = parent::calculateDependencies();

    $view = $this->getView();
    $dependencies[$view->getConfigDependencyKey()][] = $view->getConfigDependencyName();

    return $dependencies;
  }

  /**
   * Retrieves the view this search display is based on.
   *
   * @returns \Drupal\views\ViewEntityInterface
   */
  protected function getView() {
    $plugin_definition = $this->getPluginDefinition();
    return $this->getEntityTypeManager()
      ->getStorage('view')
      ->load($plugin_definition['view_id']);
  }

}
