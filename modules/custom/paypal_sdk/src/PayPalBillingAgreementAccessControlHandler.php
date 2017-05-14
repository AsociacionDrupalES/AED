<?php

namespace Drupal\paypal_sdk;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Paypal billing agreement entity.
 *
 * @see \Drupal\paypal_sdk\Entity\PayPalBillingAgreement.
 */
class PayPalBillingAgreementAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\paypal_sdk\Entity\PayPalBillingAgreement $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished paypal billing agreement entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published paypal billing agreement entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit paypal billing agreement entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete paypal billing agreement entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add paypal billing agreement entities');
  }

}
