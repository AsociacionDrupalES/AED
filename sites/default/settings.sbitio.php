<?php

// Set sticky bit.
$settings['file_chmod_directory'] = 02775;

// Don't generate useless gzip aggregates.
$config['system.performance']['css']['gzip'] = FALSE;
$config['system.performance']['js']['gzip'] = FALSE;

// Disable cron autorun.
$config['system.cron']['threshold']['autorun'] = 0; // D8
$config['automated_cron']['interval'] = 0;          // D9

/**
 * Http client config.
 */
// Set short timeouts for external calls. It can be override per uri with
// https://www.drupal.org/project/http_client_options_per_uri module.
$settings['http_client_config']['connect_timeout'] = 0.3;
$settings['http_client_config']['timeout'] = 1;

// Set a more identificative User-Agent.
$unique_id = isset($_SERVER['HTTP_X_UNIQUE_ID']) ? $_SERVER['HTTP_X_UNIQUE_ID'] : '-';
$request_uri = isset($_SERVER['REQUEST_URI']) ? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) : '';
$short_uri = $request_uri ? implode('/', array_slice(explode('/', $request_uri), 0, 3)) : '/';
$amp_tag = isset($_GET['amp']) ? '?amp' : '';
$settings['http_client_config']['headers']['User-Agent'] = implode(' ', [
  gethostname(),
  $unique_id,
  $_SERVER['REQUEST_URI'],
  $short_uri . $amp_tag,
]);
