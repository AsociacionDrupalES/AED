<?php

namespace Drupal\paypal_sdk\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for PayPal billing plan entities.
 */
class PayPalBillingPlanEntityViewsData extends EntityViewsData implements EntityViewsDataInterface {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['pay_pal_billing_plan_entity']['table']['base'] = array(
      'field' => 'id',
      'title' => $this->t('PayPal billing plan'),
      'help' => $this->t('The PayPal billing plan ID.'),
    );

    return $data;
  }

}
