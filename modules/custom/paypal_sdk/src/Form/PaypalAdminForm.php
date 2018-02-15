<?php

namespace Drupal\paypal_sdk\Form;

use Drupal;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\paypal_sdk\Services\BillingAgreement;
use Drupal\Core\Entity\EntityFieldManager;


/**
 * Paypal Configuration form.
 */
class PaypalAdminForm extends ConfigFormBase {

  /**
   * PaypalAdminForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    parent::__construct($configFactory);
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'paypal_admin_form';
  }

  /**
   * @return array
   */
  protected function getEditableConfigNames() {
    return ['config.paypal_credentials', 'config.paypal_mapping'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $plan_id = NULL) {
    $paypal_credentials = $this->config('config.paypal_credentials');
    $paypal_mapping = $this->config('config.paypal_mapping');

    $form['credentials']['client_id'] = array(
      '#type' => 'textfield',
      '#title' => t('Client ID'),
      '#description' => 'Paypal application Client ID',
      '#maxlength' => 255,
      '#required' => TRUE,
      '#default_value' => $paypal_credentials->get('client_id') ? $paypal_credentials->get('client_id') : '',
    );

    $form['credentials']['client_secret'] = array(
      '#type' => 'textfield',
      '#title' => t('Client Secret'),
      '#description' => 'Paypal application Client Secret',
      '#maxlength' => 255,
      '#required' => TRUE,
      '#default_value' => $paypal_credentials->get('client_secret') ? $paypal_credentials->get('client_secret') : '',
    );


    if ($paypal_credentials->get('client_id') && $paypal_credentials->get('client_secret')) {

      $form['assign_plans_to_field'] = array(
        '#type' => 'fieldset',
        '#title' => t('Contactsettings'),
        '#collapsible' => FALSE,
        '#description' => t('Please assign to each subscription plan a user field (can be the same if you want.)'),
      );

      // Create Agreement payments field mappings.
      $PlanOptions = $this->getActivePlans();
      $agreementMap = $this->getAgreementFieldsOptions();
      $default = $paypal_mapping->get('mapping');

      foreach ($PlanOptions as $k => $plan) {
        $form['assign_plans_to_field']['mapping'][$k] = array(
          '#type' => 'select',
          '#title' => $plan . t(' Plan'),
          '#options' => $agreementMap,
          '#description' => '',
          '#default_value' => $default[$k],
          '#empty_option' => '-- Select Agreement Field --',
          '#required' => TRUE
        );
      }
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // Set PayPal credentials.
    $this->config('config.paypal_credentials')
      ->set('client_id', $form_state->getValue(array('client_id')))
      ->set('client_secret', $form_state->getValue(array('client_secret')))
      ->save();

    // Set agreement field mappings.
    $planList = array_keys($this->getActivePlans());
    foreach ($planList as $plan_id) {
      if ($form_state->getValue(array($plan_id))) {
        $mapping[$plan_id] = $form_state->getValue(array($plan_id));
      }
    }

    $this->config('config.paypal_mapping')
      ->set('mapping', $mapping)
      ->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * Functions that retrieve Active Plans.
   * @return mixed
   */
  private function getActivePlans() {
    $PlanOptions = [];
    /** @var BillingAgreement $pba */
    $cache = Drupal::cache();
    if ($cache->get('paypal_sdk_plan_mapping_list')) {
      $PlanOptions = $cache->get('paypal_sdk_plan_mapping_list')->data;
    }
    else {
      $pba = Drupal::service('paypal.billing.agreement');
      if ($planList = $pba->getAllPlans(['status' => 'ACTIVE'])) {
        /** @var $plan \PayPal\Api\Plan */
        foreach ($planList->getPlans() as $k => $plan) {
          $PlanOptions[$plan->getId()] = $plan->getName();
        }

        $cache->set('paypal_sdk_plan_mapping_list', $PlanOptions);
      }
    }

    return $PlanOptions;
  }

  /**
   * Function to get list of availables Agreement fields.
   * @return mixed
   */
  private function getAgreementFieldsOptions() {
    $agreementMap = [];
    $cache = Drupal::cache();

    if ($cache->get('paypal_sdk_agreement_mapping_list')) {
      $agreementMap = $cache->get('paypal_sdk_agreement_mapping_list')->data;
    }
    else {
      // Get paypal_agreement_id_field_type fields instances by bundle.
      /** @var EntityFieldManager $fmg */
      $fmg = Drupal::service('entity_field.manager');
      /**  @var \Drupal\Core\Entity\EntityTypeBundleInfo $etb */
      $etb = \Drupal::service("entity_type.bundle.info");

      // TODO: We need to allow set also in all type of entities.
      foreach (array('user') as $entity_type) {
        $bundles = $etb->getBundleInfo($entity_type);
        $fieldMap = $fmg->getFieldMapByFieldType('paypal_agreement_id_field_type')[$entity_type];
        foreach ($fieldMap as $field_id => $info) {
          foreach ($info['bundles'] as $bundle) {
            $fieldsDefinitions = $fmg->getFieldDefinitions($entity_type, $bundle);
            $agreementMap[implode('-', array($bundle, $field_id))] = implode(' - ', array($bundles[$bundle]['label'], $fieldsDefinitions[$field_id]->getLabel()));
          }
        }
      }

      $cache->set('paypal_sdk_agreement_mapping_list', $agreementMap);
    }

    return $agreementMap;
  }
}
