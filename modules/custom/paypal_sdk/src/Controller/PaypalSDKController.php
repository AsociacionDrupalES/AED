<?php

namespace Drupal\paypal_sdk\Controller;

use Drupal;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\paypal_sdk\Services\BillingAgreement;
use Drupal\user\Entity\User;
use PayPal\Api\Agreement;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class PaypalSDKController
 *
 * @package Drupal\paypal_sdk\Controller
 */
class PaypalSDKController extends ControllerBase {

  /**
   * @return mixed
   */
  public function processResponse() {
    $request = Drupal::request();
    $token = $request->get('token');

    /** @var BillingAgreement $pba */
    $pba = Drupal::service('paypal.billing.agreement');

    /** @var \PayPal\Api\Agreement $agreement */
    $agreement = $pba->processAgreementResponse($token);

    /** @var \PayPal\Api\Payer $payer */
    $payer = $agreement->getPayer();

    $plan_id = $agreement->getDescription();
    $plan = $pba->getPlan($plan_id);

    // Lets modules to do things with te response.
    Drupal::moduleHandler()->invokeAll('paypal_agreement_response_ok', [
      'agreement' => $agreement,
      'plan' => $plan,
      'payer' => $payer
    ]);

    return $this->redirect('<front>');
  }

  /**
   * Cancel Response.
   * @return mixed
   */
  public function cancelledResponse() {
    drupal_set_message(t('Your subscription has been cancelled.'));
    return $this->redirect('<front>');
  }

  /**
   * Fuction to redner the billing plan list.
   * @return array
   */
  public function billingPlanList() {

    $config = \Drupal::config('paypal_sdk.settings');
    $env = $config->get('environment');
    $client_id = $config->get($env . '_client_id');
    $client_secret = $config->get($env . '_client_secret');

    $access = $client_id && $client_secret;

    if (!$access) {
      $build = array(
        '#type' => 'markup',
        '#markup' => t('Please fill the PayPal credentials to use this module'),
      );
    }
    else {
      $build = array(
        '#theme' => 'plan_list_tables',
        '#tables' => [
          'created' => $this->getPlanTableList(['status' => 'CREATED']),
          'active' => $this->getPlanTableList(['status' => 'ACTIVE']),
          'inactive' => $this->getPlanTableList(['status' => 'INACTIVE'])
        ],
      );
    }


    return $build;
  }

  /**
   * Get Plan list table by status.
   * @param $status_list_options
   * @return mixed
   */
  public function getPlanTableList($status_list_options) {
    /** @var BillingAgreement $pba */
    $pba = Drupal::service('paypal.billing.agreement');
    $planList = $pba->getAllPlans($status_list_options);


    $table['contacts'] = array(
      '#type' => 'table',
      '#header' => [
        $this->t('Name'),
        $this->t('Description'),
        $this->t('Plan ID'),
        $this->t('State'),
        $this->t('Operations'),
      ],
    );

    if (count($planList->getPlans()) == 0) {
      return $table;
    }

    foreach ($planList->getPlans() as $k => $plan) {
      /** @var \PayPal\Api\Plan $plan */

      $table['contacts'][$k]['name'] = array(
        '#type' => 'markup',
        '#markup' => $plan->getName()
      );

      $table['contacts'][$k]['desc'] = array(
        '#type' => 'markup',
        '#markup' => $plan->getDescription()
      );

      $table['contacts'][$k]['plan_id'] = array(
        '#type' => 'markup',
        '#markup' => $plan->getId()
      );

      $table['contacts'][$k]['state'] = array(
        '#type' => 'markup',
        '#markup' => $plan->getState()
      );

      $table['contacts'][$k]['operations'] = array(
        '#type' => 'operations',
        '#links' => [
          'edit' => [
            'title' => t('Edit'),
            'url' => Url::fromRoute('paypal_sdk.plan_edit_form', ['plan_id' => $plan->getId()])
          ],
          'delete' => [
            'title' => t('Delete'),
            'url' => Url::fromRoute('paypal_sdk.plan_delete_form', ['plan_id' => $plan->getId()])
          ],
        ],
      );

      // Set actions depending on plan status.
      switch ($plan->getState()) {
        case BillingAgreement::PLAN_ACTIVE:
          $table['contacts'][$k]['operations']['#links']['inactive'] = [
            'title' => t('Desactivate'),
            'url' => Url::fromRoute('paypal_sdk.plan_update_status_form', ['plan_id' => $plan->getId(), 'status' => BillingAgreement::PLAN_INACTIVE])
          ];

          break;

        case BillingAgreement::PLAN_INACTIVE:
        case BillingAgreement::PLAN_CREATED:
          $table['contacts'][$k]['operations']['#links']['active'] = [
            'title' => t('Activate'),
            'url' => Url::fromRoute('paypal_sdk.plan_update_status_form', ['plan_id' => $plan->getId(), 'status' => BillingAgreement::PLAN_ACTIVE])
          ];
          break;
      }
    }

    return $table;
  }


  /**
   * Fuction to redner the Agreements list.
   * @return array
   */
  public function AgreementsList() {
    //TODO: We must check which status should be displayed.
    $build = array(
      '#theme' => 'agreement_list_tables',
      '#tables' => [
//        'created' => $this->getPlanTableList(['status' => 'CREATED']),
        'active' => $this->getAgreementsTableList(['status' => 'ACTIVE']),
//        'inactive' => $this->getPlanTableList(['status' => 'INACTIVE'])
      ],
    );

    return $build;
  }

  /**
   * @param $status_list_options
   * @return mixed
   */
  public function getAgreementsTableList($status_list_options) {
    /** @var BillingAgreement $pba */
    $pba = Drupal::service('paypal.billing.agreement');
    $agreementList = $pba->getAllAgreements($status_list_options);


    $table['contacts'] = array(
      '#type' => 'table',
      '#header' => [
        $this->t('Name'),
        $this->t('Description'),
        $this->t('Agreement ID'),
        $this->t('State'),
        $this->t('Start Date'),
        $this->t('Plan'),
        $this->t('Operations'),
      ],
    );

    if (count($agreementList) == 0) {
      return $table;
    }

    foreach ($agreementList as $k => $agreement) {
      /** @var \PayPal\Api\Agreement $agreement */

      $table['contacts'][$k]['name'] = array(
        '#type' => 'markup',
        '#markup' => $agreement->getName()
      );

      $table['contacts'][$k]['desc'] = array(
        '#type' => 'markup',
        '#markup' => $agreement->getDescription()
      );

      $table['contacts'][$k]['agreement_id'] = array(
        '#type' => 'markup',
        '#markup' => $agreement->getId()
      );

      $table['contacts'][$k]['state'] = array(
        '#type' => 'markup',
        '#markup' => $agreement->getState()
      );

      $table['contacts'][$k]['start_date'] = array(
        '#type' => 'markup',
        '#markup' => $agreement->getStartDate()
      );

      $table['contacts'][$k]['plan'] = array(
        '#type' => 'markup',
        '#markup' => $agreement->getPlan()
      );

      $table['contacts'][$k]['operations'] = array(
        '#type' => 'operations',
        '#links' => [
          'edit' => [
            'title' => t('Edit'),
            'url' => Url::fromRoute('paypal_sdk.agreement_edit_form', ['agreemen_id' => $agreement->getId()])
          ],
        ],
      );

      // Set actions depending on plan status.
      switch ($agreement->getState()) {

        case BillingAgreement::AGREEMENT_ACTIVE:

          $table['contacts'][$k]['operations']['#links']['inactive'] = [
            'title' => t('Desactivate'),
            'url' => Url::fromRoute('paypal_sdk.agreement_update_status_form', ['agreement_id' => $agreement > getId(), 'status' => BillingAgreement::AGREEMENT_SUSPENDED])
          ];

          break;

        case BillingAgreement::AGREEMENT_CANCELED:
        case BillingAgreement::AGREEMENT_EXPIRED:
        case BillingAgreement::AGREEMENT_PENDING:
        case BillingAgreement::AGREEMENT_SUSPENDED:
//          $table['contacts'][$k]['operations']['#links']['active'] = [
//            'title' => t('Activate'),
//            'url' => Url::fromRoute('paypal_sdk.plan_update_status_form', ['plan_id' => $plan->getId(), 'status' => BillingAgreement::PLAN_ACTIVE])
//          ];
          break;
      }
    }

    return $table;
  }

  public static function getAgreement($agreement_id) {
    /** @var BillingAgreement $pba */
    $pba = Drupal::service('paypal.billing.agreement');
    $agreement = $pba->getAgreement($agreement_id);

    return $agreement;
  }


  /**
   * Generates an agreement link and returns it for ajax calls.
   * @throws \Exception
   */
  public function getAgreementLink() {
    $build = ['#theme' => 'paypal_sdk__agreement_link'];
    $request = Drupal::request();
    $plan_id = $request->get('id');
    $start_date = $request->get('startDate');

    // We cant cache the link since it is created
    /** @var BillingAgreement $pba */
    $pba = Drupal::service('paypal.billing.agreement');
    $url = $pba->getUserAgreementLink($plan_id, $start_date);

    if ($url) {
      $build['#url'] = $url;
      $res = render($build);
    }
    else {
      $res = $this->t('Cant load link. Contact with the administrator.');
    }


    return new JsonResponse(['res' => $res]);
  }
}