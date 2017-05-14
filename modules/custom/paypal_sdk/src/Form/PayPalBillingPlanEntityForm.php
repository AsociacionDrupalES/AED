<?php

namespace Drupal\paypal_sdk\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for PayPal billing plan edit forms.
 *
 * @ingroup paypal_sdk
 */
class PayPalBillingPlanEntityForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\paypal_sdk\Entity\PayPalBillingPlanEntity */
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
        drupal_set_message($this->t('Created the %label PayPal billing plan.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label PayPal billing plan.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.pay_pal_billing_plan_entity.canonical', ['pay_pal_billing_plan_entity' => $entity->id()]);
  }

}
