<?php

$config['paypal_sdk.settings']['environment'] = getenv('paypal__environment');
$config['paypal_sdk.settings']['live_client_id'] = getenv('paypal__live_client_id');
$config['paypal_sdk.settings']['live_client_secret'] = getenv('paypal__live_client_secret');
$config['paypal_sdk.settings']['sandbox_client_id'] = getenv('paypal__sandbox_client_id');
$config['paypal_sdk.settings']['sandbox_client_secret'] = getenv('paypal__sandbox_client_secret');

$config['symfony_mailer_lite.symfony_mailer_lite_transport.smtp']['configuration']['user'] = getenv('smtp__user');
$config['symfony_mailer_lite.symfony_mailer_lite_transport.smtp']['configuration']['pass'] = getenv('smtp__pass');

// Disable Lagoon logs if not on Amazee.
if (!getenv('LAGOON')) {
  $config['lagoon_logs.settings']['disable'] = 1;
}
