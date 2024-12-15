<?php

namespace Drupal\paypal_sdk\Plugin\Field\FieldWidget;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\paypal_sdk\Services\BillingAgreement;

/**
 * Plugin implementation of the 'paypal_subscribe_field_widget' widget.
 *
 * @FieldWidget(
 *   id = "paypal_subscribe_field_widget",
 *   label = @Translation("PayPal Simple Subscription widget"),
 *   field_types = {
 *     "paypal_subscribe_field_type"
 *   }
 * )
 */
class PaypalSubscribeFieldWidget extends WidgetBase {

  /** @var BillingAgreement $pba */
  protected $pba;

  public function __construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    /** @var BillingAgreement $pba */
    $this->pba = \Drupal::service('paypal.billing.agreement');
  }


  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = [];
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $now = new DrupalDateTime();
    $options = ['' => 'none'];
    $cache = \Drupal::cache();


    if ($cache->get('paypal_sdk_options_list')) {
      $options = $cache->get('paypal_sdk_options_list')->data;
    }
    else {
      $planList = $this->pba->getAllPlans(['status' => 'ACTIVE']);

      /** @var $plan \PayPal\Api\Plan */
      foreach ($planList->getPlans() as $k => $plan) {
        $options[$plan->getId()] = $plan->getName();
      }

      $cache->set('paypal_sdk_options_list', $options);
    }

    $element['plan_id'] = [
      '#type' => 'select',
      '#options' => $options,
      '#title' => t('Select the subscription'),
      '#default_value' => isset($items[$delta]->plan_id) ? $items[$delta]->plan_id : '',
      '#required' => FALSE,
      '#min' => 1,
    ];

    $element['agreement_start_choice'] = [
      '#title' => t('When should the agreement start?'),
      '#type' => 'select',
      '#options' => [
        'ipso_facto' => $this->t('Immediately'),
        'first_of_month' => $this->t('The first day of the next month'),
        'first_of_year' => $this->t('The first day of the next year'),
      ],
      '#default_value' => isset($items[$delta]->agreement_start_choice) ? $items[$delta]->agreement_start_choice : 'ipso_facto',
    ];

//    $date_format = DateFormat::load('html_date')->getPattern();
//    $time_format = DateFormat::load('html_time')->getPattern();
//
//    $element['start_date'] = [
//      '#title' => $this->t('Start date'),
//      '#type' => 'datetime',
//      '#date_date_element' => 'date',
//      '#date_time_element' => 'time',
//      '#date_year_range' => $now->format('Y') . ':+10',
//      '#description' => $this->t('If you selected custom date please set up the right one.'),
//      '#default_value' => isset($items[$delta]->start_date) ? $items[$delta]->start_date : $now,
//      '#date_date_format' => $date_format,
//      '#date_time_format' => $time_format,
//    ];

    return $element;
  }

}
