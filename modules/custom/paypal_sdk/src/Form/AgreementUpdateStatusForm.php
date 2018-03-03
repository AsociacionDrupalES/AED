<?php

namespace Drupal\paypal_sdk\Form;

use Drupal;
use Drupal\Core\Url;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\paypal_sdk\Services\BillingAgreement;

/**
 *
 */
class AgreementUpdateStatusForm extends ConfirmFormBase {


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
    return 'billing_agreement_update_status_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('This Agreement will be updated at the PayPal platform. Do you want to update  %id with status %status ?', array('%id' => $this->id, '%status' => $this->status));
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return Url::fromRoute('user.page');
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
  public function buildForm(array $form, FormStateInterface $form_state, $agreement_id = NULL, $status = NULL) {
    $this->id = $agreement_id;
    $this->status = $status;

    return parent::buildForm($form, $form_state);
  }


  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var BillingAgreement $pba */
    $pba = Drupal::service('paypal.billing.agreement');
    $result = FALSE;
    switch ($this->status) {
      case BillingAgreement::AGREEMENT_CANCELED:
        $result = $pba->cancelAgreement($this->id);
        break;
      case BillingAgreement::AGREEMENT_SUSPENDED:
        $result = $pba->suspendAgreement($this->id);
        break;
      case BillingAgreement::AGREEMENT_REACTIVE:
        $result = $pba->reactivateAgreement($this->id);
        break;
    }

    if ($result) {
      drupal_set_message($this->t('The Agreement with ID <strong>@id</strong> has been updated.', ['@id' => $this->id]));
      // TODO: Improve how clean the cache based on the field.
      // The field is being used only in user entities but should allow be used
      // in any entity.
      $user = Drupal::currentUser();
      \Drupal\Core\Cache\Cache::invalidateTags(['user:' . $user->id()]);
    }

    $form_state->setRedirectUrl($this->getCancelUrl());

  }
}
