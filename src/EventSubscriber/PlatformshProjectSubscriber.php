<?php

namespace Drupal\platformsh_project\EventSubscriber;

use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * platformsh_project event subscriber.
 *
 * 2023-02: Just a skeleton to capture and trace event handling.
 * Currently does nothing useful beyond providing boilerplate proving that it can be done.
 */
class PlatformshProjectSubscriber implements EventSubscriberInterface {

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs event subscriber.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   */
  public function __construct(MessengerInterface $messenger) {
    $this->messenger = $messenger;
  }

  /**
   * Kernel request event handler.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   Response event.
   */
  public function onKernelRequest(GetResponseEvent $event) {
    #$this->messenger->addStatus(__FUNCTION__);
  }

  /**
   * Kernel response event handler.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   *   Response event.
   */
  public function onKernelResponse(FilterResponseEvent $event) {
    #$this->messenger->addStatus(__FUNCTION__);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // Run my own custom stuff before and after each main thread request.
    return [
      KernelEvents::REQUEST => ['onKernelRequest'],
      KernelEvents::RESPONSE => ['onKernelResponse'],
    ];
  }

}
