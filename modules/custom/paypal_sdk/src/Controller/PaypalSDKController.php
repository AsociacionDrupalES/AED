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


    // If the current user is anonymous and the email does not exist on any user, create a new account.

    if (Drupal::currentUser()->isAnonymous()) {

      // The email (user) already exist?
      $existingUser = user_load_by_mail($payer->getPayerInfo()->getEmail());

      if (!$existingUser) {
        // If user does not exists, create a new one.
        $user = User::create();
        $user->set("init", 'mail');
        $user->enforceIsNew();
        $user->setEmail($payer->getPayerInfo()->getEmail());
        $user->setUsername($payer->getPayerInfo()->getEmail());
        //$user->addRole('socio'); ?
        $user->activate();
        $user->save();

        _user_mail_notify('register_no_approval_required', $user);
        // user_login_finalize($user);
        drupal_set_message(t('Subscription successful. We sent you an activation email.'));
      }
      else {
        $user = $existingUser;
      }

    }
    else {
      $user = Drupal::currentUser();
    }

    // Append new agreement.
    $agreementMapping = $this->config('config.paypal_mapping')->get('mapping');
    $plan_id = $agreement->getDescription();
    list($entity, $agreementField) = explode('-', $agreementMapping[$plan_id]);
    $userEntity = User::load($user->id());
    $userEntity->{$agreementField}->appendItem($agreement->getId());

    $userEntity->save();
    return $this->redirect('<front>');

// Debug
//    return array(
//      '#markup' => '<pre>' . $agreement->toJSON(JSON_PRETTY_PRINT) . '</pre>',
//    );

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

    $paypal_credentials = \Drupal::config('config.paypal_credentials');
    $access = $paypal_credentials->get('client_id') && $paypal_credentials->get('client_secret');

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
   */
  public function getAgreementLink() {
    $request = Drupal::request();
    $plan_id = $request->get('id');

    // We cant cache the link since it is created
    /** @var BillingAgreement $pba */
    $pba = Drupal::service('paypal.billing.agreement');
    $url = $pba->getUserAgreementLink($plan_id);
    $res = '';

    if ($url) {
      /** @var Drupal\Core\GeneratedLink $link */
      $link = Link::fromTextAndUrl(
        'Subscribe',
        Url::fromUri($url, array(
          'absolute' => TRUE,
          'attributes' => array(
            'target' => '_blank',
            'class' => array('paypal-subscribe-link')
          )
        )))->toRenderable();

      $res = render($link);
    }

    return new JsonResponse(['link' => $res]);
  }
}