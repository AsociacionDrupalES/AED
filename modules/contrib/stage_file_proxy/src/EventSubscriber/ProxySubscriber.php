<?php

namespace Drupal\stage_file_proxy\EventSubscriber;

use Drupal\Component\Utility\Unicode;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Url;
use Psr\Log\LoggerInterface;
use Drupal\stage_file_proxy\EventDispatcher\AlterExcludedPathsEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\stage_file_proxy\FetchManagerInterface;

/**
 * Stage file proxy subscriber for controller requests.
 */
class ProxySubscriber implements EventSubscriberInterface {

  /**
   * The manager used to fetch the file against.
   *
   * @var \Drupal\stage_file_proxy\FetchManagerInterface
   */
  protected $manager;

  /**
   * The logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * Construct the FetchManager.
   *
   * @param \Drupal\stage_file_proxy\FetchManagerInterface $manager
   *   The manager used to fetch the file against.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger interface.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   */
  public function __construct(FetchManagerInterface $manager, LoggerInterface $logger, EventDispatcherInterface $event_dispatcher) {
    $this->manager = $manager;
    $this->logger = $logger;
    $this->eventDispatcher = $event_dispatcher;
  }

  /**
   * Fetch the file according the its origin.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The Event to process.
   */
  public function checkFileOrigin(GetResponseEvent $event) {
    $config = \Drupal::config('stage_file_proxy.settings');
    // Get the origin server.
    $server = $config->get('origin');

    // Quit if no origin given.
    if (!$server) {
      return;
    }

    // Quit if we are the origin, ignore http(s).
    if (preg_replace('#^[a-z]*://#u', '', $server) === $event->getRequest()->getHost()) {
      return;
    }

    $file_dir = $this->manager->filePublicPath();
    $request_path = $event->getRequest()->getPathInfo();

    $request_path = Unicode::substr($request_path, 1);

    if (strpos($request_path, '' . $file_dir) !== 0) {
      return;
    }

    $alter_excluded_paths_event = new AlterExcludedPathsEvent([]);
    $this->eventDispatcher->dispatch('stage_file_proxy.alter_excluded_paths', $alter_excluded_paths_event);
    $excluded_paths = $alter_excluded_paths_event->getExcludedPaths();
    foreach ($excluded_paths as $excluded_path) {
      if (strpos($request_path, $excluded_path) !== FALSE) {
        return;
      }
    }

    // Note if the origin server files location is different. This
    // must be the exact path for the remote site's public file
    // system path, and defaults to the local public file system path.
    $remote_file_dir = trim($config->get('origin_dir'));
    if (!$remote_file_dir) {
      $remote_file_dir = $file_dir;
    }

    $request_path = rawurldecode($request_path);
    // Path relative to file directory. Used for hotlinking.
    $relative_path = Unicode::substr($request_path, Unicode::strlen($file_dir) + 1);
    // If file is fetched and use_imagecache_root is set, original is used.
    $fetch_path = $relative_path;

    // Is this imagecache? Request the root file and let imagecache resize.
    // We check this first so locally added files have precedence.
    $original_path = $this->manager->styleOriginalPath($relative_path, TRUE);
    if ($original_path) {
      if (file_exists($original_path)) {
        // Imagecache can generate it without our help.
        return;
      }
      if ($config->get('use_imagecache_root')) {
        // Config says: Fetch the original.
        $fetch_path = file_uri_target($original_path);
      }
    }

    $query = \Drupal::request()->query->all();
    $query_parameters = UrlHelper::filterQueryParameters($query);
      $options = [
        'verify' => \Drupal::config('stage_file_proxy.settings')->get('verify'),
      ];

    if ($config->get('hotlink')) {

      $location = Url::fromUri("$server/$remote_file_dir/$relative_path", [
        'query' => $query_parameters,
        'absolute' => TRUE,
      ])->toString();

    }
    elseif ($this->manager->fetch($server, $remote_file_dir, $fetch_path, $options)) {
      // Refresh this request & let the web server work out mime type, etc.
      $location = Url::fromUri('base://' . $request_path, [
        'query' => $query_parameters,
        'absolute' => TRUE,
      ])->toString();
      // Avoid redirection caching in upstream proxies.
      header("Cache-Control: must-revalidate, no-cache, post-check=0, pre-check=0, private");
    }
    else {
      $this->logger->error('Stage File Proxy encountered an unknown error by retrieving file @file', ['@file' => $server . '/' . UrlHelper::encodePath($remote_file_dir . '/' . $relative_path)]);
    }

    if (isset($location)) {
      header("Location: $location");
      exit;
    }
  }

  /**
   * Registers the methods in this class that should be listeners.
   *
   * @return array
   *   An array of event listener definitions.
   */
  public static function getSubscribedEvents() {
    // Priority 240 is after ban middleware but before page cache.
    $events[KernelEvents::REQUEST][] = ['checkFileOrigin', 240];
    return $events;
  }

}
