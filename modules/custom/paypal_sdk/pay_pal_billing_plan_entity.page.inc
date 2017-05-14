<?php

/**
 * @file
 * Contains pay_pal_billing_plan_entity.page.inc.
 *
 * Page callback for PayPal billing plan entities.
 */

use Drupal\Core\Render\Element;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Prepares variables for PayPal billing plan templates.
 *
 * Default template: pay_pal_billing_plan_entity.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_pay_pal_billing_plan_entity(array &$variables) {
  // Fetch PayPalBillingPlanEntity Entity Object.
  $pay_pal_billing_plan_entity = $variables['elements']['#pay_pal_billing_plan_entity'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
