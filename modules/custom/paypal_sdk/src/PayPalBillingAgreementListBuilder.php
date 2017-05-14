<?php

namespace Drupal\paypal_sdk;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Routing\LinkGeneratorTrait;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of Paypal billing agreement entities.
 *
 * @ingroup paypal_sdk
 */
class PayPalBillingAgreementListBuilder extends EntityListBuilder {

  use LinkGeneratorTrait;

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Paypal billing agreement ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\paypal_sdk\Entity\PayPalBillingAgreement */
    $row['id'] = $entity->id();
    $row['name'] = $this->l(
      $entity->label(),
      new Url(
        'entity.paypal_billing_agreement.edit_form', array(
          'paypal_billing_agreement' => $entity->id(),
        )
      )
    );
    return $row + parent::buildRow($entity);
  }

}
