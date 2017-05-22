<?php

namespace Drupal\paypal_sdk\Form;

use Drupal;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\paypal_sdk\Services\BillingAgreement;

/**
 * Implements an example form.
 */
class PlanAddForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'plan_add_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $plan_id = NULL) {
    $form['#title'] = $this->t('Plan add');

    $form['name'] = array(
      '#title' => $this->t('Name'),
      '#type' => 'textfield',
      '#description' => $this->t('The name of the PayPal billing plan entity.'),
    );

    $form['description'] = array(
      '#title' => $this->t('Description'),
      '#type' => 'textfield',
    );


    $form['payment_amount'] = array(
      '#title' => $this->t('Payment amount'),
      '#type' => 'number',
    );

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
      '#options' => [
        'EUR' => 'EUR',
        'USD' => 'USD',
      ],
    );

    $payment_cycles = array_combine(
      [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 18, 24, 36, 48],
      [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 18, 24, 36, 48]
    );


    $form['payment_cycles'] = array(
      '#title' => $this->t('Payment cycles'),
      '#type' => 'select',
      '#options' => $payment_cycles,
    );

    $form['payment_frequency'] = array(
      '#title' => $this->t('Payment frequency'),
      '#type' => 'select',
      '#description' => $this->t('Si has seleccionado "FIXED" como tipo de plan, selecciona aquí la periodicidad con la que se emitirá un cobro. Por ejemplo, si quieres cobrarle al usuario una vez al mes selecciona "MONTHLY"'),
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
    );

    $form['payment_type'] = array(
      '#title' => $this->t('Payment type'),
      '#type' => 'select',
      '#options' => ['REGULAR' => 'REGULAR', 'TRIAL' => 'TRIAL'],
    );

    $form['plan_type'] = array(
      '#title' => $this->t('Plan type'),
      '#type' => 'select',
      '#options' => ['INFINITE' => 'INFINITE', 'FIXED' => 'FIXED'],
    );

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
    $pba = Drupal::service('paypal.billing.agreement');

    $data = [
      'name' => $form_state->getValue('name'),
      'description' => $form_state->getValue('description'),
      'type' => $form_state->getValue('plan_type'),
      'payment_cycles' => $form_state->getValue('payment_cycles'),
      'payment_type' => $form_state->getValue('payment_type'),
      'payment_frequency' => $form_state->getValue('payment_frequency'),
      'payment_frequency_interval' => $form_state->getValue('payment_frequency_interval'),
      'payment_amount' => $form_state->getValue('payment_amount'),
      'payment_currency' => $form_state->getValue('payment_currency'),
    ];

    $result = $pba->createPlan($data);

    if ($result !== FALSE) {
      drupal_set_message($this->t('The plan <strong>@name</strong> with ID <strong>@id</strong> has been created.', ['@name' => $result->getName(), '@id' => $result->getId()]));
    }

    $form_state->setRedirectUrl(Url::fromRoute('paypal_sdk.billing_plan_list'));
  }

}
