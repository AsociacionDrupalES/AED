<?php

namespace Drupal\paypal_sdk\Services;

use Drupal;
use Drupal\Core\Url;
use Drupal\paypal_sdk\Entity\PayPalBillingPlanEntity;
use PayPal\Api\Agreement;
use PayPal\Api\ChargeModel;
use PayPal\Api\Currency;
use PayPal\Api\MerchantPreferences;
use PayPal\Api\Patch;
use PayPal\Api\PatchRequest;
use PayPal\Api\Payer;
use PayPal\Api\PaymentDefinition;
use PayPal\Api\Plan;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Common\PayPalModel;
use PayPal\Rest\ApiContext;


/**
 * Class BillingAgreement.
 *
 * @package Drupal\paypal_sdk
 */
class BillingAgreement {

  private $apiContext;

  /**
   * Constructor.
   * @param string $clientId
   * @param string $clientSecret
   */
  public function __construct($clientId, $clientSecret) {
    $this->apiContext = &drupal_static(__FUNCTION__, FALSE);

    if (!$this->apiContext) {
      $this->apiContext = new ApiContext(
        new OAuthTokenCredential($clientId, $clientSecret)
      );
    }

  }

  /**
   * Creates a plan.
   *
   * @param PayPalBillingPlanEntity $entity @todo should be a more generic object?
   * @return bool|\PayPal\Api\Plan $plan
   */
  public function createPlan($data) {
    $plan = new Plan();

    $plan
      ->setName($data['name'])
      ->setDescription($data['description'])
      ->setType($data['type']);

    $paymentDefinition = new PaymentDefinition();
    $cycles = $data['type'] == "FIXED" ? $data['payment_cycles'] : 0;
    $paymentDefinition->setName('Regular Payments')// dinamizar
    ->setType($data['payment_type'])
      ->setFrequency($data['payment_frequency'])
      ->setFrequencyInterval($data['payment_frequency_interval'])
      ->setCycles($cycles)
      ->setAmount(new Currency(array(
        'value' => $data['payment_amount'],
        'currency' => $data['payment_currency']
      )));

    // @todo hacer opcional.
    $chargeModel = new ChargeModel();
    $chargeModel->setType('TAX')
      ->setAmount(new Currency(array(
        'value' => $data['payment_amount'] * .21,
        'currency' => $data['payment_currency']
      )));

    $paymentDefinition->setChargeModels(array($chargeModel));

    $merchantPreferences = new MerchantPreferences();
    $returnURL = Url::fromUri('internal:/paypal/subscribe/response/process/', ['absolute' => TRUE])->toString();
    $cancelURL = Url::fromUri('internal:/paypal/subscribe/response/cancelled/', ['absolute' => TRUE])->toString();

    $merchantPreferences
      ->setReturnUrl($returnURL)
      ->setCancelUrl($cancelURL)
      ->setAutoBillAmount("yes")
      ->setInitialFailAmountAction("CONTINUE")
      ->setMaxFailAttempts("0");
//    ->setSetupFee(new Currency(array('value' => 1, 'currency' => $entity->get('field_payment_currency')->value)));


    $plan->setPaymentDefinitions(array($paymentDefinition));
    $plan->setMerchantPreferences($merchantPreferences);

    try {
      $createdPlan = $plan->create($this->apiContext);
      //$this->setState();
      return $createdPlan;
    } catch (\Exception $e) {
      drupal_set_message($e->getMessage(), "error");
      return FALSE;
    }
  }

  /**
   * Set a plan state.
   * @param \PayPal\Api\Plan $plan
   * @param string $state CREATED, ACTIVE, etc
   *
   * @return bool
   */
  public function setState($plan, $state) {
    try {
      $patch = new Patch();

      $value = new PayPalModel('{
	       "state":"' . $state . '"
	     }');

      $patch
        ->setOp('replace')
        ->setPath('/')
        ->setValue($value);

      $patchRequest = new PatchRequest();
      $patchRequest->addPatch($patch);
      $plan->update($patchRequest, $this->apiContext);
      return TRUE;

    } catch (\Exception $e) {
      return FALSE;
    }
  }

  /**
   * Updates a plan.
   *
   * @param string $plan_id ID of the plan
   * @param array $values key value plan nuew values.
   *
   * @return bool
   */
  public function updatePlan($plan_id, $values) {
    $plan = $this->getPlan($plan_id);

    try {
//      $this->setState($plan, 'CREATED');
      $patch = new Patch();

      $patch
        ->setOp('replace')
        ->setPath('/')
        ->setValue($values);

      $patchRequest = new PatchRequest();
      $patchRequest->addPatch($patch);
      $plan->update($patchRequest, $this->apiContext);
//      $this->setState($plan, 'ACTIVE');
      return TRUE;

    } catch (\Exception $e) {
//      $this->setState($plan, 'ACTIVE');
      return FALSE;
    }

  }

  /**
   * Gets a plan.
   *
   * @param $plan_id
   * @return bool|\PayPal\Api\Plan
   */
  public function getPlan($plan_id) {
    try {
      $plan = Plan::get($plan_id, $this->apiContext);
      return $plan;

    } catch (\Exception $e) {
      drupal_set_message($e->getMessage(), "error");
      return FALSE;
    }

  }

  /**
   * Gets all plans.
   *
   * Usage:
   *  foreach ($planList->getPlans() as $plan) {
   *    drupal_set_message(t($plan->getName() . ' - ' . $plan->getId()));
   *  }
   *
   * @param array $options @todo meter en las opciones la posibilidad de especificar el state del listado que queremos por ejemplo. O el page_size, etc.
   * @return \PayPal\Api\PlanList
   */
  public function getAllPlans($options = []) {

    $params = array_merge([
      'page_size' => 20,
      'status' => 'CREATED'
    ], $options);

    try {

      $planList = Plan::all($params, $this->apiContext);
      return $planList;

    } catch (\Exception $e) {
      drupal_set_message($e->getMessage(), "error");
      return FALSE;
    }

  }


  /**
   * Deletes a plan.
   *
   * @param $plan_id
   * @return bool
   */
  public function deletePlan($plan_id) {
    $plan = $this->getPlan($plan_id);

    try {
      $plan->delete($this->apiContext);
      return TRUE;
    } catch (\Exception $e) {
      drupal_set_message($e->getMessage(), "error");
      return FALSE;
    }
  }


  /**
   * Generates a link for a new agreement.
   *
   * @param string $plan_id
   * @return bool|null|string
   */
  function getUserAgreementLink($plan_id) {
    // Get the rellated entity
    // @todo move to a method
    $query = Drupal::entityQuery('pay_pal_billing_plan_entity');
    $query->condition('field_id', $plan_id, '=');
    $result = $query->execute();

    if (!$result) {
      // @todo lanzar un error
    }

    $node_storage = Drupal::entityTypeManager()->getStorage('pay_pal_billing_plan_entity');
    $entity = $node_storage->load(reset($result));
    $field_start_date_value = $entity->get('field_start_date')->value;

    $originalPlan = $this->getPlan($plan_id);

    // Time to compare dates
    $utcTimezone = new \DateTimeZone('UTC');
    $dateTimeNow = new \DateTime('NOW', $utcTimezone);
    $dateTimeField = new \DateTime($field_start_date_value, $utcTimezone);

    $firstDate = $dateTimeNow->format('Y-m-d');
    $secondDate = $dateTimeField->format('Y-m-d');

    $start_date = ($firstDate == $secondDate) ? $dateTimeNow : $dateTimeField;

    // We can not mark the start date with "NOW", so we move ti forward a few minutes.
    $start_date->modify('+10 minutes');

    $agreement = new Agreement();
    $agreement
      ->setName($originalPlan->getName())
      ->setDescription($originalPlan->getDescription())
      ->setStartDate($start_date->format('c'));

    $plan = new Plan();
    $plan->setId($plan_id);
    $agreement->setPlan($plan);

    $payer = new Payer();
    $payer->setPaymentMethod('paypal');
    $agreement->setPayer($payer);

    try {
      $agreement = $agreement->create($this->apiContext);
      $approvalUrl = $agreement->getApprovalLink();

    } catch (\Exception $e) {
      var_dump(json_decode($e->getData()));
      return FALSE;
    }

    return $approvalUrl;
  }

  /**
   * Process the authorized token coming from paypal if the used approved the agreement.
   *
   * @param string $token
   * @return \PayPal\Api\Agreement|string
   */
  public function processAgreementResponse($token) {
    $_agreement = new Agreement();

    try {
      $_agreement->execute($token, $this->apiContext);
      $agreement = Agreement::get($_agreement->getId(), $this->apiContext);
      return $agreement;

    } catch (\Exception $e) {
      drupal_set_message($e->getMessage(), "error");
      return "Error finalizando el agreement.";
    }

  }

}
