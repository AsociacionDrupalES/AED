<?php

namespace Drupal\paypal_sdk\Plugin\Field\FieldFormatter;

use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\paypal_sdk\Services\BillingAgreement;
use Drupal\paypal_sdk\Controller\PaypalSDKController;
use Symfony\Component\Validator\Constraints\DateTime;


/**
 * Plugin implementation of the 'paypal_agreement_id_field_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "paypal_agreement_id_field_formatter",
 *   label = @Translation("Paypal agreement ID"),
 *   field_types = {
 *     "paypal_agreement_id_field_type"
 *   }
 * )
 */
class PaypalAgreementIdFieldFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $options = parent::defaultSettings();

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $agreement_id = $item->getString();
      $elements[$delta] = ['#markup' => $this->viewValue($agreement_id)];
    }

    return $elements;
  }


  /**
   * @param $agreementId
   *
   * @return mixed
   */
  protected function viewValue($agreementId) {

    // Agreement.
    /** @var \PayPal\Api\Agreement $details */
    $agreement = PaypalSDKController::getAgreement($agreementId);
    /** @var \PayPal\Api\AgreementDetails $details */
    $details = $agreement->getAgreementDetails();

    // Plan.
    /** @var BillingAgreement $pba */
    $pba = \Drupal::service('paypal.billing.agreement');
    $plan = $pba->getPlan($agreement->getDescription());
    $plan_payment_definitions_arr = $plan->getPaymentDefinitions();
    /** @var \PayPal\Api\PaymentDefinition $plan_payment_definitions */
    $plan_payment_definitions = $plan_payment_definitions_arr[0];
    /** @var \PayPal\Api\Currency $plan_amount */
    $plan_amount = $plan_payment_definitions->getAmount();

    // Format dates.
    $utcTimezone = new \DateTimeZone('UTC');
    $next_billing_date = new \DateTime($details->getNextBillingDate(), $utcTimezone);
    $start_date = new \DateTime($agreement->getStartDate(), $utcTimezone);

    $name_and_desc = ['#markup' => $plan->getName() . '<br/><sub>' . $plan->getDescription() . '</sub>'];

    $table_header = [
      $this->t('ID'),
      $this->t('Name'),
      // $this->t('Subscription type'),
      $this->t('Amount'),
      $this->t('Started at'),
      $this->t('Next billing date'),
      $this->t('Status'),
    ];

    $table_rows = [
      ['data' => $agreementId],
      ['data' => render($name_and_desc)],
      // ['data' => $plan_payment_definitions->getType()],
      ['data' => $plan_amount->getValue() . ' ' . $plan_amount->getCurrency()],
      ['data' => $start_date->format('m/d/Y')],
      ['data' => $next_billing_date->format('m/d/Y')],
      ['data' => $agreement->getState()],
    ];

    $actions = [];
    switch ($agreement->getState()) {
      case BillingAgreement::AGREEMENT_ACTIVE:
        // Suspend link. @todo review if have sense to give to the user this option.
        // $actions['Suspend'] = Url::fromRoute('paypal_sdk.agreement_update_status_form', ['agreement_id' => $agreementId, 'status' => BillingAgreement::AGREEMENT_SUSPENDED]);
        // Cancel link.
        $actions['Cancel'] = Url::fromRoute('paypal_sdk.agreement_update_status_form', ['agreement_id' => $agreementId, 'status' => BillingAgreement::AGREEMENT_CANCELED]);
        break;
      case BillingAgreement::AGREEMENT_SUSPENDED:
        // Re-activate it @todo review if have sense to give to the user this option.
        // $actions['Reactivate'] = Url::fromRoute('paypal_sdk.agreement_update_status_form', ['agreement_id' => $agreementId, 'status' => BillingAgreement::AGREEMENT_REACTIVE]);
        // Cancel link.
        $actions['Cancel'] = Url::fromRoute('paypal_sdk.agreement_update_status_form', ['agreement_id' => $agreementId, 'status' => BillingAgreement::AGREEMENT_CANCELED]);
        break;
      case BillingAgreement::AGREEMENT_CANCELED:
        // TODO: Do we need to allow active the agreement or just need to create a new one?
        break;
    }

    if (count($actions)) {
      $links = [];

      foreach ($actions as $label => $url) {
        $links[] = Link::fromTextAndUrl($label, $url)->toString()->getGeneratedLink();
      }

      $renderable_links = ['#markup' => implode(' | ', $links)];
      $table_rows[] = ['data' => render($renderable_links)];
      $table_header[] = $this->t('Actions');
    }

    $build['table'] = [
      '#theme' => 'table',
      '#header' => $table_header,
      '#rows' => [
        $table_rows
      ],
      '#empty' => $this->t("There are no PayPal subscriptions for this user."),
    ];


    // Convert $build to HTML and attach any asset libraries.
    $html = \Drupal::service('renderer')->renderRoot($build);

    return $html;
  }
}
