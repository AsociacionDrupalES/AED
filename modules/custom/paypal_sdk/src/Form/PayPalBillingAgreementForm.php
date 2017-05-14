<?php

namespace Drupal\paypal_sdk\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Paypal billing agreement edit forms.
 *
 * @ingroup paypal_sdk
 */
class PayPalBillingAgreementForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\paypal_sdk\Entity\PayPalBillingAgreement */
    $form = parent::buildForm($form, $form_state);
    $entity = $this->entity;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Paypal billing agreement.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Paypal billing agreement.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.paypal_billing_agreement.canonical', ['paypal_billing_agreement' => $entity->id()]);
  }

}
