<?php

namespace Drupal\facets\Plugin\facets\widget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\facets\FacetInterface;
use Drupal\facets\Result\Result;
use Drupal\facets\Widget\WidgetPluginBase;
use Drupal\facets\Plugin\facets\query_type\SearchApiDate;

/**
 * Basic date widget.
 *
 * @FacetsWidget(
 *   id = "datebasic",
 *   label = @Translation("Date list"),
 *   description = @Translation("A simple list of dates"),
 * )
 */
class DateBasicWidget extends WidgetPluginBase {

  /**
   * Human readable array of granularity options.
   *
   * @return array
   *   An array of granularity options.
   */
  private function granularityOptions() {
    return array(
      SearchApiDate::FACETAPI_DATE_YEAR => $this->t('Year'),
      SearchApiDate::FACETAPI_DATE_MONTH => $this->t('Month'),
      SearchApiDate::FACETAPI_DATE_DAY => $this->t('Day'),
      SearchApiDate::FACETAPI_DATE_HOUR => $this->t('Hour'),
      SearchApiDate::FACETAPI_DATE_MINUTE => $this->t('Minute'),
      SearchApiDate::FACETAPI_DATE_SECOND => $this->t('Second'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'display_relative' => FALSE,
      'granularity' => SearchApiDate::FACETAPI_DATE_MONTH,
      'date_display' => '',
      'relative_granularity' => 1,
      'relative_text' => TRUE,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, FacetInterface $facet) {
    $configuration = $this->getConfiguration();

    $form += parent::buildConfigurationForm($form, $form_state, $facet);

    $form['display_relative'] = [
      '#type' => 'radios',
      '#title' => $this->t('Date display'),
      '#default_value' => $configuration['display_relative'],
      '#options' => [
        FALSE => $this->t('Actual date with granularity'),
        TRUE => $this->t('Relative date'),
      ],
    ];

    $form['granularity'] = [
      '#type' => 'radios',
      '#title' => $this->t('Granularity'),
      '#default_value' => $configuration['granularity'],
      '#options' => $this->granularityOptions(),
    ];
    $form['date_display'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Date format'),
      '#default_value' => $configuration['date_display'],
      '#description' => $this->t('Override default date format used for the displayed filter format. See the <a href="http://php.net/manual/function.date.php">PHP manual</a> for available options.'),
      '#states' => [
        'visible' => [':input[name="widget_config[display_relative]"]' => ['value' => 0]],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getQueryType(array $query_types) {
    return $query_types['date'];
  }

  /**
   * {@inheritdoc}
   */
  public function build(FacetInterface $facet) {
    $this->facet = $facet;

    $items = array_map(function (Result $result) {
      if (empty($result->getUrl())) {
        return ['#markup' => $this->extractText($result)];
      }
      else {
        return $this->buildListItems($result);
      }
    }, $facet->getResults());

    return [
      '#theme' => 'facets_item_list',
      '#items' => $items,
      '#attributes' => ['data-drupal-facet-id' => $facet->id()],
      '#cache' => [
        'contexts' => [
          'url.path',
          'url.query_args',
        ],
      ],
    ];
  }

}
