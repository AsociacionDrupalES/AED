<?php

namespace Drupal\social_links_simple\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\Core\Form\FormStateInterface;
use Drupal\link\Plugin\Field\FieldFormatter\LinkFormatter;

/**
 * Plugin implementation of the 'link' formatter.
 *
 * @FieldFormatter(
 *   id = "social_links_simple",
 *   label = @Translation("Treat as social links"),
 *   field_types = {
 *     "link"
 *   }
 * )
 */
class FieldSocialLinksSimpleFormatter extends LinkFormatter {

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);
    unset($element['trim_length']);
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    foreach ($summary as $k => $summary_line) {
      if ($summary_line->getUntranslatedString() == "Link text trimmed to @limit characters") {
        unset($summary[$k]);
      }
    }
    $summary[] = t('All link will be converted to social links.');
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    foreach ($elements as &$element) {
      /** @var  $url \Drupal\Core\URL */
      $url = $element['#url'];
      $social_link_labels = [
        'google' => 'Google+',
        'twitter' => 'Twitter',
        'facebook' => 'Facebook',
        'linkedin' => 'Linkedin',
        'drupal' => 'Drupal.org',
      ];

      $found = FALSE;
      foreach ($social_link_labels as $social_link_label_k => $social_link_label) {
        if (strpos($url->getUri(), $social_link_label_k) !== FALSE) {
          $element['#title'] = $social_link_label;
          $element['#options']['attributes']['class'] = 'social-' . $social_link_label_k . '-plus';
          $found = TRUE;
        }
      }
      if (!$found) {
        $element['#options']['attributes']['class'] = 'social-other';
      }
    }

    return $elements;
  }

}
