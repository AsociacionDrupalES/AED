<?php

namespace Drupal\paypal_sdk\Services;

use Drupal\Core\Url;
use Drupal\Core\Config\ConfigFactoryInterface;
use PayPal\Api\Agreement;
use PayPal\Api\AgreementStateDescriptor;
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

  /**
   *
   */
  const PLAN_ACTIVE = 'ACTIVE';

  /**
   *
   */
  const PLAN_INACTIVE = 'INACTIVE';

  /**
   *
   */
  const PLAN_CREATED = 'CREATED';

  /**
   *
   */
  const AGREEMENT_PENDING = 'PENDING';

  /**
   *
   */
  const AGREEMENT_ACTIVE = 'Active';

  /**
   *
   */
  const AGREEMENT_SUSPENDED = 'Suspended';

  /**
   *
   */
  const AGREEMENT_CANCELED = 'Canceled';

  /**
   *
   */
  const AGREEMENT_EXPIRED = 'Expired';

  const AGREEMENT_REACTIVE = 'Re Active';

  /**
   * @var \PayPal\Rest\ApiContext
   */
  private $apiContext;

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;


  /**
   * BillingAgreement constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->apiContext = &drupal_static(__FUNCTION__, FALSE);
    $this->configFactory = $config_factory;

    $config = $this->configFactory->get('paypal_sdk.settings');
    $env = $config->get('environment');
    $client_id = $config->get($env . '_client_id');
    $client_secret = $config->get($env . '_client_secret');

    if (!$this->apiContext) {
      $this->apiContext = new ApiContext(
        new OAuthTokenCredential($client_id, $client_secret)
      );

      $this->apiContext->setConfig(
        array(
          'mode' => $env,
        )
      );

    }

  }

  /**
   * Creates a plan.
   *
   * @return bool|\PayPal\Api\Plan $plan
   */
  public function createPlan($data) {
    // Cycles must be 0 if the plan type is "infinite".
    $cycles = $data['plan_type'] == "FIXED" ? $data['payment_cycles'] : 0;
    $plan = new Plan();

    $plan
      ->setName($data['name'])
      ->setDescription($data['description'])
      ->setType($data['plan_type']);

    $paymentDefinition = new PaymentDefinition();

    $paymentDefinition
      ->setName('Regular Payments')// dinamizar
      ->setType('REGULAR')
      ->setFrequency($data['payment_frequency'])
      ->setFrequencyInterval($data['payment_frequency_interval'])
      ->setCycles($cycles)
      ->setAmount(new Currency(array(
        'value' => $data['payment_amount'],
        'currency' => $data['payment_currency']
      )));

    if ($data['tax'] !== '' && $data['tax'] !== '0') {
      // Taxes
      $chargeModel = new ChargeModel();

      $chargeModel->setType('TAX')
        ->setAmount(new Currency(array(
          'value' => $data['payment_amount'] * $data['tax'],
          'currency' => $data['payment_currency']
        )));

      // Sample Shipping costs
      // $chargeModel = new ChargeModel();
      // $chargeModel->setType('SHIPPING')
      //   ->setAmount(new Currency(array(
      //    'value' => "10",
      //    'currency' => "EUR"
      //  )));

      $paymentDefinition->setChargeModels(array($chargeModel));
    }

    $returnURL = Url::fromUri('internal:/paypal/subscribe/response/process/', ['absolute' => TRUE])->toString();
    $cancelURL = Url::fromUri('internal:/paypal/subscribe/response/cancelled/', ['absolute' => TRUE])->toString();

    $merchantPreferences = new MerchantPreferences();
    $merchantPreferences
      ->setReturnUrl($returnURL)
      ->setCancelUrl($cancelURL)
      ->setAutoBillAmount("yes")
      ->setInitialFailAmountAction("CONTINUE")
      ->setMaxFailAttempts("0");

    $plan->setPaymentDefinitions(array($paymentDefinition));
    $plan->setMerchantPreferences($merchantPreferences);

    try {
      $createdPlan = $plan->create($this->apiContext);
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

      // Crear Paypal SDK cache.
      $cache = \Drupal::cache();
      $cache->invalidate('paypal_sdk_options_list');

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
   * @todo implemebnt updates https://paypal.github.io/PayPal-PHP-SDK/sample/doc/billing/UpdatePlanPaymentDefinitions.html
   * @return bool
   */
  public function updatePlan($plan_id, $values) {
    $plan = $this->getPlan($plan_id);

    try {
      $patch = new Patch();

      $patch
        ->setOp('replace')
        ->setPath('/')
        ->setValue($values);

      $patchRequest = new PatchRequest();
      $patchRequest->addPatch($patch);
      $plan->update($patchRequest, $this->apiContext);
      return $plan;

    } catch (\Exception $e) {
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
   * @return \PayPal\Api\PlanList|boolean
   */
  public function getAllPlans($options = []) {

    $params = array_merge([
      'page_size' => '20',
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

      // Crear Paypal SDK cache.
      $cache = \Drupal::cache();
      $cache->invalidate('paypal_sdk_options_list');

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
   * @param string $start_date_choice Indicates the start date concept.
   * @return bool|null|string
   */
  function getUserAgreementLink($plan_id, $start_date_choice) {
    $originalPlan = $this->getPlan($plan_id);

    if (!$originalPlan) {
      return FALSE;
    }

    $utcTimezone = new \DateTimeZone('UTC');
    $base_date = new \DateTime('NOW', $utcTimezone);

    switch ($start_date_choice) {
      default:
      case 'ipso_facto':
        // We can not mark the start date with "NOW",
        // so we move it forward a few minutes.
        $base_date->modify('+10 minutes');
        $start_date = $base_date;
        break;
      case 'first_of_month':
        $base_date->modify('+1 months');
        $calc_str_date = $base_date->format('Y') . '-' . $base_date->format('m') . '-01';
        $start_date = new \DateTime($calc_str_date, $utcTimezone);
        break;
      case 'first_of_year':
        $base_date->modify('+1 years');
        $calc_str_date = $base_date->format('Y') . '-01-01';
        $start_date = new \DateTime($calc_str_date, $utcTimezone);
        break;
    }


    $agreement = new Agreement();
    $agreement
      ->setName($originalPlan->getName())
      ->setDescription($originalPlan->getId())
      ->setStartDate($start_date->format('c'));

    $plan = new Plan();
    $plan->setId($plan_id);
    $agreement->setPlan($plan);

    $payer = new Payer();
    $payer->setPaymentMethod('paypal');
    $agreement->setPayer($payer);

    try {
      $agreement = $agreement->create($this->apiContext);
      return $agreement->getApprovalLink();
    } catch (\Exception $e) {
      var_dump(json_decode($e->getData()));
      return FALSE;
    }
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

  /**
   * @param array $options
   *
   * @return array|bool
   */
  public function getAllAgreements($options = []) {
    $params = array_merge([
      'page_size' => 20,
      'status' => 'ACTIVE'
    ], $options);

    try {

      $agreementList = Agreement::getList($params);

      return $agreementList;

    } catch (\Exception $e) {
      drupal_set_message($e->getMessage(), "error");
      return FALSE;
    }
  }


  /**
   * @param $agreementId
   * @param $params
   */
  public function getAgreement($agreementId) {
    $agreement = Agreement::get($agreementId, $this->apiContext);

    return $agreement;
  }

  public function cancelAgreement($agreementId) {
    $agreement = $this->getAgreement($agreementId);
    $agreementStateDescriptor = new AgreementStateDescriptor();
    $agreementStateDescriptor->setNote('Canceling Agreement');

    return $agreement->cancel($agreementStateDescriptor, $this->apiContext);
  }


  public function suspendAgreement($agreementId) {
    $agreement = $this->getAgreement($agreementId);
    $agreementStateDescriptor = new AgreementStateDescriptor();
    $agreementStateDescriptor->setNote('Suspending Agreement');

    return $agreement->suspend($agreementStateDescriptor, $this->apiContext);
  }

  public function reactivateAgreement($agreementId) {
    $agreement = $this->getAgreement($agreementId);
    $agreementStateDescriptor = new AgreementStateDescriptor();
    $agreementStateDescriptor->setNote('Reactivating Agreement');

    return $agreement->reActivate($agreementStateDescriptor, $this->apiContext);
  }
}
