<?php

namespace Drupal\paypal_sdk\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for Paypal billing agreement entities.
 */
class PayPalBillingAgreementViewsData extends EntityViewsData implements EntityViewsDataInterface {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['paypal_billing_agreement']['table']['base'] = array(
      'field' => 'id',
      'title' => $this->t('PayPal billing agreement'),
      'help' => $this->t('The Paypal billing agreement ID.'),
    );

    return $data;
  }

}
