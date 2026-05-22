<?php

$settings['file_private_path'] = '../private-files';

// Ignore dev modules from config.
$settings['config_exclude_modules'] = [
  'devel',
  'devel_generate',
  'stage_file_proxy',
  'backup_migrate',
];

// Non-local projects can only change configuration via "drush".
if (getenv('IS_DDEV_PROJECT') != 'true' && PHP_SAPI !== 'cli') {
  $settings['config_readonly'] = TRUE;
}

// Disable Lagoon logs if not on Amazee.
if (!getenv('LAGOON')) {
  $config['lagoon_logs.settings']['disable'] = 1;
}
