<?php

namespace Drupal\paypal_sdk\Form;

use Drupal;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\paypal_sdk\Services\BillingAgreement;

/**
 * Implements an example form.
 */
class PlanEditForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'plan_edit_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $plan_id = NULL) {
    /** @var BillingAgreement $pba */
    $pba = Drupal::service('paypal.billing.agreement');
    $plan = $pba->getPlan($plan_id);

    /** @var \PayPal\Api\MerchantPreferences $mp */
    // $mp = $plan->getMerchantPreferences();

    /** @var \PayPal\Api\PaymentDefinition $pd */
    $pd = $plan->getPaymentDefinitions()[0];

    $form['#title'] = $this->t('Plan edit "' . $plan->getName() . '" (' . $plan_id . ')');

    $form['plan_state'] = array(
      '#title' => $this->t('State'),
      '#type' => 'markup',
      '#markup' => $plan->getState(),
    );


    $form['name'] = array(
      '#title' => $this->t('Name'),
      '#type' => 'textfield',
      '#description' => $this->t('The name of the PayPal billing plan entity.'),
      '#default_value' => $plan->getName(),
    );

    $form['description'] = array(
      '#title' => $this->t('Description'),
      '#type' => 'textfield',
      '#default_value' => $plan->getDescription(),
    );


    $form['payment_amount'] = array(
      '#title' => $this->t('Payment amount'),
      '#type' => 'number',
      '#default_value' => $pd->getAmount()->getValue(),
    );


    // Falta
    $form['accepted_payment_method'] = array(
      '#title' => $this->t('Accepted payment methods'),
      '#type' => 'select',
      '#options' => [
        'paypal' => 'PayPal',
        'credit_card' => 'Credit Card',
      ],
    );

    $form['payment_currency'] = array(
      '#title' => $this->t('Payment currency'),
      '#type' => 'select',
      '#default_value' => $pd->getAmount()->getCurrency(),
      '#options' => [
        'EUR' => 'EUR',
        'USD' => 'USD',
      ],
    );

    $form['payment_cycles'] = array(
      '#title' => $this->t('Payment cycles'),
      '#type' => 'select',
      '#default_value' => (int) $pd->getCycles(),
      '#options' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 18, 24, 36, 48],
    );

    $form['payment_frequency'] = array(
      '#title' => $this->t('Payment frequency'),
      '#type' => 'select',
      '#description' => $this->t('Si has seleccionado "FIXED" como tipo de plan, selecciona aquí la periodicidad con la que se emitirá un cobro. Por ejemplo, si quieres cobrarle al usuario una vez al mes selecciona "MONTHLY"'),
      '#default_value' => strtoupper($pd->getFrequency()),
      '#options' => [
        'DAY' => 'DAILY',
        'WEEK' => 'WEEKLY',
        'MONTH' => 'MONTHLY',
        'YEAR' => 'YEARLY',
      ],
    );

    $form['payment_frequency_interval'] = array(
      '#title' => $this->t('Payment frequency interval'),
      '#type' => 'number',
      '#description' => $this->t('A billing agreement can be for a determined period, eg: for 6 months, or just be a subscription with no expiration. In the first case select "FIXED" and setup the frequencies.'),
      '#default_value' => $pd->getFrequencyInterval(),
    );

    // Falta
    $form['payment_type'] = array(
      '#title' => $this->t('Payment type'),
      '#type' => 'select',
      '#options' => ['REGULAR' => 'REGULAR', 'TRIAL' => 'TRIAL'],
    );

    $form['plan_type'] = array(
      '#title' => $this->t('Plan type'),
      '#type' => 'select',
      '#default_value' => $plan->getType(),
      '#options' => ['INFINITE' => 'INFINITE', 'FIXED' => 'FIXED'],
    );


    // Falta
    $form['plan_start'] = array(
      '#title' => $this->t('Plan start'),
      '#type' => 'date',
      '#description' => $this->t('Especifica cuando arrancar con la suscripción. Por defecto se inicia en el mismo momento que el usuario la completa pero se puede desplazar hacia adelante.'),
    );

    $form['actions']['#type'] = 'actions';

    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // @todo implementar validaciones.
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var BillingAgreement $pba */
    $plan_id = $form_state->getBuildInfo()['args'][0];
    $pba = Drupal::service('paypal.billing.agreement');

    $values = [
      'name' => $form_state->getValue('name')
    ];

    $plan = $pba->updatePlan($plan_id, $values);
  }

}
