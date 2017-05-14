<?php

/**
 * @file
 * Contains paypal_billing_agreement.page.inc.
 *
 * Page callback for Paypal billing agreement entities.
 */

use Drupal\Core\Render\Element;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Prepares variables for Paypal billing agreement templates.
 *
 * Default template: paypal_billing_agreement.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_paypal_billing_agreement(array &$variables) {
  // Fetch PayPalBillingAgreement Entity Object.
  $paypal_billing_agreement = $variables['elements']['#paypal_billing_agreement'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
