<?php

/**
 * @file
 * Contains Drupal\linked_field\Form\ConfigForm.
 */

namespace Drupal\linked_field\Form;

use Drupal\Component\Serialization\Exception\InvalidDataTypeException;
use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ConfigForm.
 *
 * @package Drupal\linked_field\Form
 */
class ConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'linked_field.config'
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'linked_field_config';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('linked_field.config');
    $conf = $config->get();
    $config_text = Yaml::encode($conf);

    // Each attribute needs 3 rows + "attributes:" row + 3 extra lines
    // for adding a new attribute.
    $rows = (count($config->get('attributes')) * 3) + 4;

    $form['config'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Configuration'),
      '#description' => $this->t('Available attributes can be defined in YAML syntax.'),
      '#default_value' => $config_text,
      '#rows' => $rows,
    );

    // Use module's YAML config file for example structure.
    $module_path = \Drupal::moduleHandler()->getModule('linked_field')->getPath();
    $yml_text = file_get_contents($module_path . '/config/install/linked_field.config.yml');

    $form['example'] = [
      '#type' => 'details',
      '#title' => $this->t('Example structure'),
    ];

    $form['example']['description'] = [
      '#prefix' => '<p>',
      '#suffix' => '</p>',
      '#markup' => $this->t('Each attribute has an optional label and description.')
    ];

    $form['example']['code'] = [
      '#prefix' => '<pre>',
      '#suffix' => '</pre>',
      '#markup' => $yml_text,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $config_text = $form_state->getValue('config') ?: 'attributes:';

    try {
      $form_state->set('config', Yaml::decode($config_text));
    }
    catch (InvalidDataTypeException $e) {
      $form_state->setErrorByName('config', $e->getMessage());
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $form_state->get('config');

    $this->config('linked_field.config')
      ->setData($config)
      ->save();

    parent::submitForm($form, $form_state);
  }

}
