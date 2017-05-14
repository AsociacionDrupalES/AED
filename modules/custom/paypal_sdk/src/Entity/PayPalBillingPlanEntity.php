<?php

namespace Drupal\paypal_sdk\Entity;

use Drupal;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\paypal_sdk\Services\BillingAgreement;
use Drupal\user\UserInterface;


/**
 * Defines the PayPal billing plan entity.
 *
 * @ingroup paypal_sdk
 *
 * @ContentEntityType(
 *   id = "pay_pal_billing_plan_entity",
 *   label = @Translation("PayPal billing plan"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\paypal_sdk\PayPalBillingPlanEntityListBuilder",
 *     "views_data" = "Drupal\paypal_sdk\Entity\PayPalBillingPlanEntityViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\paypal_sdk\Form\PayPalBillingPlanEntityForm",
 *       "add" = "Drupal\paypal_sdk\Form\PayPalBillingPlanEntityForm",
 *       "edit" = "Drupal\paypal_sdk\Form\PayPalBillingPlanEntityForm",
 *       "delete" = "Drupal\paypal_sdk\Form\PayPalBillingPlanEntityDeleteForm",
 *     },
 *     "access" = "Drupal\paypal_sdk\PayPalBillingPlanEntityAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\paypal_sdk\PayPalBillingPlanEntityHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "pay_pal_billing_plan_entity",
 *   admin_permission = "administer paypal billing plan entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/store/pay_pal_billing_plan_entity/{pay_pal_billing_plan_entity}",
 *     "add-form" = "/admin/store/pay_pal_billing_plan_entity/add",
 *     "edit-form" = "/admin/store/pay_pal_billing_plan_entity/{pay_pal_billing_plan_entity}/edit",
 *     "delete-form" = "/admin/store/pay_pal_billing_plan_entity/{pay_pal_billing_plan_entity}/delete",
 *     "collection" = "/admin/store/pay_pal_billing_plan_entity",
 *   },
 *   field_ui_base_route = "pay_pal_billing_plan_entity.settings"
 * )
 */
class PayPalBillingPlanEntity extends ContentEntityBase {

  use EntityChangedTrait;


  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += array(
      'user_id' => \Drupal::currentUser()->id(),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isPublished() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished($published) {
    $this->set('status', $published ? NODE_PUBLISHED : NODE_NOT_PUBLISHED);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the PayPal billing plan entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => array(
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ),
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the PayPal billing plan entity.'))
      ->setSettings(array(
        'max_length' => 50,
        'text_processing' => 0,
      ))
      ->setDefaultValue('')
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => -4,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the PayPal billing plan is published.'))
      ->setDefaultValue(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);
    /** @var BillingAgreement $pba */
    $pba = Drupal::service('paypal.billing.agreement');

    if ($this->isNew()) {
      return $pba->createPlan($this);
    }
    else {
      return $pba->updatePlan($this);
    }


  }

  public function delete() {
    /** @var BillingAgreement $pba */
    $pba = Drupal::service('paypal.billing.agreement');
    $plan_id = $this->get('field_id')->value;

    if (!is_null($plan_id)) {
      $pba->deletePlan($plan_id);
    }

    parent::delete();
  }
}
