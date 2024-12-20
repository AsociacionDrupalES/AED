<?php

namespace Drupal\paypal_sdk\Plugin\Field\FieldFormatter;

use Drupal;
use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\paypal_sdk\Services\BillingAgreement;

/**
 * Plugin implementation of the 'paypal_subscribe_field_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "paypal_subscribe_field_formatter",
 *   label = @Translation("Paypal subscribe field fromatter"),
 *   field_types = {
 *     "paypal_subscribe_field_type"
 *   }
 * )
 */
class PaypalSubscribeFieldFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $options = parent::defaultSettings();
    $options['link_text'] = 'Subscribe';
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $form['link_text'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Link text'),
      '#default_value' => $this->getSetting('link_text'),
      '#required' => TRUE,
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $link_text = $this->getSetting('link_text');
    $summary[] = t('Link text: @link_text', array('@link_text' => $link_text));

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $elements[$delta] = ['#markup' => $this->viewValue($item)];
    }

    return $elements;
  }

  /**
   * Generate the output appropriate for one field item.
   *
   * @param $item
   * @return array
   */
  protected function viewValue($item) {
    /*
     * Since twe need a fresh link for each user and since the API is so slow, we return
     * a placeholder and replace it via ajax with the generated link.
     * */
    $build = [
      '#theme' => 'paypal_sdk__agreement_placeholder_link',
      '#plan_id' => $item->plan_id,
      '#start_date' => $item->agreement_start_choice,
      '#attached' => [
        'library' => ['paypal_sdk/generate-link'],
        'drupalSettings' => [
          'ppssFieldFormatter' => [
            'url' => Url::fromRoute('paypal_sdk.generate_agreement_link')->toString()
          ]
        ]
      ]
    ];

    return \Drupal::service('renderer')->render($build);
  }

}
