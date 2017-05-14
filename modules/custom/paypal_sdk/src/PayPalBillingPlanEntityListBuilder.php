<?php

namespace Drupal\paypal_sdk;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Routing\LinkGeneratorTrait;
use Drupal\Core\Url;
use PayPal\Api\Plan;

/**
 * Defines a class to build a listing of PayPal billing plan entities.
 *
 * @ingroup paypal_sdk
 */
class PayPalBillingPlanEntityListBuilder extends EntityListBuilder {

  use LinkGeneratorTrait;

  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage) {
    parent::__construct($entity_type, $storage);

//    $apiContext = get_api_context();
//
//    try {
//      $params = array(
//        'page_size' => '20',
//        'status' => 'ACTIVE'
//      );
//      $planList = Plan::all($params, $apiContext);
//
//      /** @var Plan $plan */
//      foreach ($planList->getPlans() as $plan) {
//        drupal_set_message(t($plan->getName() . ' - ' . $plan->getId()));
//      }
//
//    } catch (\Exception $ex) {
//    }

  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['name'] = $this->t('Name');
    $header['field_id'] = $this->t('Plan ID');
    $header['amount'] = $this->t('Price');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\paypal_sdk\Entity\PayPalBillingPlanEntity */

    $row['name'] = $this->l(
      $entity->label(),
      new Url(
        'entity.pay_pal_billing_plan_entity.edit_form', array(
          'pay_pal_billing_plan_entity' => $entity->id(),
        )
      )
    );

    $row['field_id'] = $entity->get('field_id')->value;
    $row['amount'] = $entity->get('field_payment_amount')->value . ' ' . $entity->get('field_payment_currency')->value;

    return $row + parent::buildRow($entity);
  }

}


