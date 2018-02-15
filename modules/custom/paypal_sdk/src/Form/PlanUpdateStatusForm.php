<?php

namespace Drupal\paypal_sdk\Form;

use Drupal;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\paypal_sdk\Services\BillingAgreement;

/**
 * Implements an example form.
 */
class PlanUpdateStatusForm extends ConfirmFormBase {


  /**
   * The ID of the item to delete.
   *
   * @var string
   */
  protected $id;

  protected $status;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'billing_paln_update_status_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('This plan will be updated at the PayPal platform. Do you want to update  %id with status %status ?', array('%id' => $this->id, '%status' => $this->status));
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return Url::fromRoute('paypal_sdk.billing_plan_list');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return t('Only do this if you are sure!');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return t('Update it!');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelText() {
    return t('Cancel');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $plan_id = NULL, $status = NULL) {
    $this->id = $plan_id;
    $this->status = $status;

    return parent::buildForm($form, $form_state);
  }


  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var BillingAgreement $pba */
    $pba = Drupal::service('paypal.billing.agreement');
    $plan = $pba->getPlan($this->id);
    $name = $plan->getName();
    $result = $pba->setState($plan, $this->status);

    if ($result) {
      drupal_set_message($this->t('The plan <strong>@name</strong> with ID <strong>@id</strong> has been updated.', ['@name' => $name, '@id' => $this->id]));
    }

    $form_state->setRedirectUrl($this->getCancelUrl());

  }
}
