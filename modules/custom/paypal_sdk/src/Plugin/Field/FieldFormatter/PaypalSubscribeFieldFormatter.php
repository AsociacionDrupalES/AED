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
      /** @var Drupal\Core\TypedData\Plugin\DataType\StringData $a */
      $subscription_id = $item->get('subscription_id');

      $elements[$delta] = ['#markup' => $this->viewValue($subscription_id->getCastedValue())];
    }

    return $elements;
  }

  /**
   * Generate the output appropriate for one field item.
   *
   * @param $subscription_id
   * @return string The textual output generated.
   * The textual output generated.
   * @internal param \Drupal\Core\Field\FieldItemInterface $item One field
   *   item.*   One field item.
   *
   */
  protected function viewValue($subscription_id) {
    /** @var BillingAgreement $pba */
    $pba = Drupal::service('paypal.billing.agreement');
    $url = $pba->getUserAgreementLink($subscription_id);


    if ($url) {
      /** @var Drupal\Core\GeneratedLink $link */
      $link = Link::fromTextAndUrl(
        $this->getSetting('link_text'),
        Url::fromUri($url, array(
          'absolute' => TRUE,
          'attributes' => array(
            'class' => array('paypal-subscribe-link')
          )
        )))->toRenderable();

      return render($link);
    }

    return '';
  }

}
