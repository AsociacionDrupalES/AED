<?php

namespace Drupal\paypal_sdk\Form;

use Drupal;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\paypal_sdk\Services\BillingAgreement;
use Drupal\Core\Url;

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

    $form['plan_state'] = [
      '#title' => $this->t('State'),
      '#type' => 'markup',
      '#markup' => $plan->getState(),
    ];

    $payment_cycles = array_combine(
      [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 18, 24, 36, 48],
      [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 18, 24, 36, 48]
    );

    $form['name'] = [
      '#title' => $this->t('Name'),
      '#required' => TRUE,
      '#type' => 'textfield',
      '#default_value' => $plan->getName(),
      '#description' => $this->t('The name of the PayPal billing plan entity.'),
    ];

    $form['description'] = [
      '#title' => $this->t('Description'),
      '#type' => 'textfield',
      '#default_value' => $plan->getDescription(),
      '#required' => TRUE,
    ];

    $form['plan_type'] = [
      '#title' => $this->t('Plan type'),
      '#type' => 'select',
      '#options' => [
        'INFINITE' => 'INFINITE',
        'FIXED' => 'FIXED'
      ],
      '#description' => $this->t('INFINITE will never end until the client cancel the agreement. FIXED will finish on the specified amount of cycles.'),
      '#default_value' => $plan->getType(),
      '#required' => TRUE,
    ];

    $form['options_type_fixed'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Fixed plan type options'),
      '#states' => [
        'visible' => [
          ':input[name="plan_type"]' => ['value' => 'FIXED'],
        ]
      ]
    ];

    $form['options_type_fixed']['payment_cycles'] = [
      '#title' => $this->t('Payment cycles'),
      '#type' => 'select',
      '#options' => $payment_cycles,
      '#description' => $this->t('Specify how many times the plan will be charged. After the last charge gets applied the plan will be finished'),
      '#default_value' => (int) $pd->getCycles(),
    ];

    $form['amount_options'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Amount'),
    ];

    $form['amount_options']['payment_amount'] = [
      '#title' => $this->t('Payment amount'),
      '#type' => 'number',
      '#default_value' => $pd->getAmount()->getValue(),
      '#description' => $this->t('Set the value as an integer or as a decimal (use a dot for the decimal part). '),
      '#required' => TRUE,
    ];

    $form['amount_options']['payment_currency'] = [
      '#title' => $this->t('Payment currency'),
      '#type' => 'select',
      '#options' => [
        'EUR' => 'EUR',
        'USD' => 'USD',
      ],
      '#default_value' => $pd->getAmount()->getCurrency(),
    ];

    $form['amount_options']['tax'] = [
      '#title' => $this->t('Tax'),
      '#type' => 'number',
      '#step' => 0.01,
      '#description' => $this->t('Specify the applied tax. For example 0,21 (Spanish tax).'),
    ];

    $form['frequency_options'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Frequency'),
      '#description' => $this->t('If you select an interval of 3 and a "Monthly" frequency it means: "When a client subscribes to this plan It fill be charged every 3 months".'),
    ];

    $form['frequency_options']['payment_frequency_interval'] = [
      '#title' => $this->t('Payment frequency interval'),
      '#min' => 1,
      '#max' => 50,
      '#type' => 'number',
      '#required' => TRUE,
      '#default_value' => $pd->getFrequencyInterval(),
    ];

    $form['frequency_options']['payment_frequency'] = [
      '#title' => $this->t('Payment frequency'),
      '#type' => 'select',
      '#options' => [
        'DAY' => 'DAILY',
        'WEEK' => 'WEEKLY',
        'MONTH' => 'MONTHLY',
        'YEAR' => 'YEARLY',
      ],
      '#default_value' => strtoupper($pd->getFrequency()),
    ];

    $form['actions']['#type'] = 'actions';

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // @todo implement validations.
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var BillingAgreement $pba */
    $plan_id = $form_state->getBuildInfo()['args'][0];
    $pba = Drupal::service('paypal.billing.agreement');
    $values = $form_state->getValues();
    unset($values['submit'], $values['op'], $values['form_build_id'], $values['form_token'], $values['form_id']);
//    $result = $pba->updatePlan($plan_id, $form_state->getValues());
//
//
//    if ($result !== FALSE) {
//      drupal_set_message($this->t('The plan <strong>@name</strong> with ID <strong>@id</strong> has been updated.', ['@name' => $result->getName(), '@id' => $result->getId()]));
//    }

    drupal_set_message($this->t('Sorry updates are not implemented yet.'), 'warning');

    $form_state->setRedirectUrl(Url::fromRoute('paypal_sdk.billing_plan_list'));

  }

}
