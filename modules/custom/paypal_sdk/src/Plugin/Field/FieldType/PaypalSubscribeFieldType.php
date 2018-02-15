<?php

namespace Drupal\paypal_sdk\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'paypal_subscribe_field_type' field type.
 *
 * @FieldType(
 *   id = "paypal_subscribe_field_type",
 *   label = @Translation("PayPal Simple Subscription"),
 *   description = @Translation("Renders a subscription link."),
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
      ->setLabel(new TranslatableMarkup('Billing plan ID'))
      ->setSetting('case_sensitive', FALSE)
      ->setRequired(TRUE);

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
          'binary' => FALSE,
        ],
      ],
    ];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('plan_id')->getValue();
    return $value === NULL || $value === '';
  }

}
