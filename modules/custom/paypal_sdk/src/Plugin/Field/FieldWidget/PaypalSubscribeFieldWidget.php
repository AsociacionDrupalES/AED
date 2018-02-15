<?php

namespace Drupal\paypal_sdk\Plugin\Field\FieldWidget;

use Drupal\Core\Entity\Entity;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\field\Entity\FieldConfig;
use Drupal\paypal_sdk\Services\BillingAgreement;
use Symfony\Component\DependencyInjection\ContainerInterface;

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


    $element['plan_id'] = $element + [
        '#type' => 'select',
        '#options' => $options,
        '#title' => t('Select the subscription'),
        '#default_value' => isset($items[$delta]->plan_id) ? $items[$delta]->plan_id : NULL,
        '#required' => FALSE,
        '#min' => 1,
      ];

    return $element;
  }

}
