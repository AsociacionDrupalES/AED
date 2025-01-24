<?php

namespace Drupal\linked_image_field\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\image\Plugin\Field\FieldFormatter\ImageFormatter;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'image' formatter.
 *
 * @FieldFormatter(
 *   id = "field_linked_image",
 *   label = @Translation("Image url linked to a field link"),
 *   field_types = {
 *     "image"
 *   }
 * )
 */
class FieldLinkedImageFormatter extends ImageFormatter {

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);
    $this->fillWithLinkableFields($element['image_link']['#options']);
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    $link_types = array(
      'content' => t('Linked to content'),
      'file' => t('Linked to file'),
    );

    $this->fillWithLinkableFields($link_types);

    // Display this setting only if image is linked.
    $image_link_setting = $this->getSetting('image_link');
    if (isset($link_types[$image_link_setting])) {
      $summary[] = $link_types[$image_link_setting];
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = parent::viewElements($items, $langcode);
    $image_link_setting = $this->getSetting('image_link');

    // If the field is configurated to take a field as link we proceed:
    if (strpos($image_link_setting, 'field_') === 0) {
      $entity = $items->getEntity();
      $value = $entity->get($image_link_setting)->getValue();
      if ($value) {
        $url = $value[0]['uri'];
        $element[0]['#url'] = $url;
      }

    }

    return $element;
  }

  /**
   * Adds the available field types to link.
   *
   * @param $arr array of items
   */
  private function fillWithLinkableFields(&$arr) {
    $fields = \Drupal::service('entity_field.manager')->getFieldDefinitions($this->fieldDefinition->getTargetEntityTypeId(), $this->fieldDefinition->getTargetBundle());
    foreach ($fields as $field_def) {
      if ($field_def instanceof FieldConfig && $field_def->getType() == 'link') {
        $arr[$field_def->getName()] = t('Linked to field ' . $field_def->getLabel());
      }
    }
  }

}
