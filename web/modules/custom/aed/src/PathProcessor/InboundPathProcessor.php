<?php

namespace Drupal\aed\PathProcessor;

use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Symfony\Component\HttpFoundation\Request;

class InboundPathProcessor implements InboundPathProcessorInterface {

  public function processInbound($path, Request $request): string {
    // We use to have a video content type and all videos would start
    // as `/video/*`
    if (str_starts_with($path, '/video/')) {
      return '/videos';
    }

    return $path;
  }

}
