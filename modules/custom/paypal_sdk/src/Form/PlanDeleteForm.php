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
class PlanDeleteForm extends ConfirmFormBase {


  /**
   * The ID of the item to delete.
   *
   * @var string
   */
  protected $id;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'billing_paln_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('This plan will be deleted at the PayPal platform. Do you want to delete  %id?', array('%id' => $this->id));
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
    return t('Delete it!');
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
  public function buildForm(array $form, FormStateInterface $form_state, $plan_id = NULL) {
    $this->id = $plan_id;
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
    $result = $pba->deletePlan($this->id);

    if ($result) {
      drupal_set_message($this->t('The plan <strong>@name</strong> with ID <strong>@id</strong> has been deleted.', ['@name' => $name, '@id' => $this->id]));
    }

    $form_state->setRedirectUrl($this->getCancelUrl());

  }
}
