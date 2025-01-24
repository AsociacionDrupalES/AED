<?php

$settings['config_sync_directory'] = '../config';

// Drupal 11 will default to having state cache enabled.
$settings['state_cache'] = TRUE;

# Tweak ddev redis config.
$settings['redis.connection']['interface'] = 'PhpRedis';
$settings['queue']['default'] = 'queue.redis';
// Always set the fast backend for bootstrap, discover and config, otherwise
// this gets lost when redis is enabled.
$settings['cache']['bins']['bootstrap'] = 'cache.backend.chainedfast';
$settings['cache']['bins']['discovery'] = 'cache.backend.chainedfast';
$settings['cache']['bins']['config'] = 'cache.backend.chainedfast';

$settings['config_exclude_modules'] = [
  'devel_generate',
  'stage_file_proxy',
  'config_inspector',
  'devel',
  'migrate_devel',
  'upgrade_status',
  'upgrade_rector',
];

// Do not allow config changes unless they are via `drush` or `local` setup.
$settings['config_readonly'] = TRUE;
if (PHP_SAPI === 'cli' || getenv('IS_DDEV_PROJECT') === 'true') {
  $settings['config_readonly'] = FALSE;
}

// Paypal settings should be set at server level. If you need credentials for
// "sandbox" or "live" environments, contact the AED to get them and put them
// in your "settings.local.php" file, which will override these settings.
$config['paypal_sdk.settings']['environment'] = getenv('PAYPAL_ENVIRONMENT') ?: 'sandbox';
$config['paypal_sdk.settings']['live_client_id'] = getenv('PAYPAL_LIVE_CLIENT_ID') ?: '';
$config['paypal_sdk.settings']['live_client_secret'] = getenv('PAYPAL_LIVE_CLIENT_SECRET') ?: '';
$config['paypal_sdk.settings']['sandbox_client_id'] = getenv('PAYPAL_SANDBOX_CLIENT_ID') ?: '';
$config['paypal_sdk.settings']['sandbox_client_secret'] = getenv('PAYPAL_SANDBOX_CLIENT_SECRET') ?: '';

// Switfmailer settings. If you need credentials for "sandbox" or "live"
// environments, contact the AED to get them and put them in your
// "settings.local.php" file, which will override these settings.
$config['swiftmailer.transport']['smtp_credentials']['swiftmailer']['username'] = getenv('SWIFTMAILER_USERNAME') ?: '';
$config['swiftmailer.transport']['smtp_credentials']['swiftmailer']['password'] = getenv('SWIFTMAILER_PASSWORD') ?: '';
