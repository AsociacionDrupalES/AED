<?php

namespace Drupal\paypal_sdk\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'paypal_subscribe_field_type' field type.
 *
 * @FieldType(
 *   id = "paypal_subscribe_field_type",
 *   label = @Translation("PayPal Simple Subscription"),
 *   description = @Translation("Renders a subscription link."),
 *   category = @Translation("Commerce"),
 *   default_widget = "paypal_subscribe_field_widget",
 *   default_formatter = "paypal_subscribe_field_formatter"
 * )
 */
class PaypalSubscribeFieldType extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    // Prevent early t() calls by using the TranslatableMarkup.
    $properties['plan_id'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Billing plan ID'));

    $properties['agreement_start_choice'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Start date agreement'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = [
      'columns' => [
        'plan_id' => [
          'type' => 'varchar',
          'length' => 50,
        ],
        'agreement_start_choice' => [
          'type' => 'varchar',
          'length' => 20,
        ]
      ],
      'indexes' => []
    ];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $isEmpty =
      empty($this->get('plan_id')->getValue()) &&
      empty($this->get('agreement_start_choice')->getValue());

    return $isEmpty;
  }

}
