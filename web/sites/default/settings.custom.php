<?php

// Disable Lagoon logs if not on Amazee.
if (!getenv('LAGOON')) {
  $config['lagoon_logs.settings']['disable'] = 1;
}
