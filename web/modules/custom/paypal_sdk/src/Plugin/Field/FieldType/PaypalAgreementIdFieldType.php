<?php

namespace Drupal\paypal_sdk\Plugin\Field\FieldType;

use Drupal\Core\Field\Plugin\Field\FieldType\StringItem;
use Drupal\text\Plugin\Field\FieldType\TextItem;

/**
 * Plugin implementation of the 'paypal_subscribe_field_type' field type.
 *
 * @FieldType(
 *   id = "paypal_agreement_id_field_type",
 *   label = @Translation("Paypal agreement ID"),
 *   description = @Translation("Paypal Agreement ID"),
 *   default_widget = "",
 *   default_formatter = "paypal_agreement_id_field_formatter"
 * )
 */
class PaypalAgreementIdFieldType extends TextItem {
}
