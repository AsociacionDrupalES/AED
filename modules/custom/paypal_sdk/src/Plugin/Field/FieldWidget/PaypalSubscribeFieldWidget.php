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
 *   label = @Translation("Paypal subscribe field widget"),
 *   field_types = {
 *     "paypal_subscribe_field_type"
 *   }
 * )
 */
class PaypalSubscribeFieldWidget extends WidgetBase {

  /**
   * The entity query factory.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactoryInterface
   */
  protected $entityPlans;

  public function __construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings) {
    $this->entityPlans = \Drupal::entityQuery('pay_pal_billing_plan_entity');
    $this->entityPlans->condition('field_id', '', '<>');

    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
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

    /**
     * @var $query \Drupal\Core\Entity\Query\QueryInterface
     */
    $query = $this->entityPlans;
    $plan_ids = $query->execute();

    $options = ['' => 'none'];
    $cache = \Drupal::cache();


    if ($cache->get('paypal_sdk_options_list')) {
      $options = $cache->get('paypal_sdk_options_list')->data;
    }
    else {
      foreach ($plan_ids as $entity_id) {
        $etm = \Drupal::entityTypeManager()->getStorage('pay_pal_billing_plan_entity');
        /** @var BillingAgreement $pba */
        $pba = \Drupal::service('paypal.billing.agreement');

        $entity = $etm->load($entity_id);
        $plan_id = $entity->get('field_id')->value;

        $realPlan = $pba->getPlan($plan_id);
        $options[$plan_id] = $realPlan->getName();
      }

      $cache->set('paypal_sdk_options_list', $options);
    }


    $element['subscription_id'] = $element + [
        '#type' => 'select',
        '#options' => $options,
        '#title' => t('Select the subscription'),
        '#default_value' => isset($items[$delta]->subscription_id) ? $items[$delta]->subscription_id : NULL,
        '#required' => FALSE,
        '#min' => 1,
      ];

    return $element;
  }

}
