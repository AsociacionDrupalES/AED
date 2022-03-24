<?php

$settings['config_sync_directory'] = 'sites/default/conf';

# Tweak ddev redis config.
$settings['redis.connection']['interface'] = 'PhpRedis';
$settings['queue']['default'] = 'queue.redis';
// Always set the fast backend for bootstrap, discover and config, otherwise
// this gets lost when redis is enabled.
$settings['cache']['bins']['bootstrap'] = 'cache.backend.chainedfast';
$settings['cache']['bins']['discovery'] = 'cache.backend.chainedfast';
$settings['cache']['bins']['config'] = 'cache.backend.chainedfast';
