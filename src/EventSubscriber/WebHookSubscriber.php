<?php

namespace Drupal\stripe_registration\EventSubscriber;

use Drupal\stripe_api\Event\StripeApiWebhookEvent;
use Drupal\stripe_registration\Entity\StripeSubscriptionEntity;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\stripe_registration\StripeRegistrationService;

/**
 * Class WebHookSubscriber.
 *
 * @package Drupal\stripe_registration
 */
class WebHookSubscriber implements EventSubscriberInterface {

  /** @var \Drupal\stripe_registration\StripeRegistrationService  */
  protected $stripeRegApi;

  /**
   * WebHookSubscriber constructor.
   *
   * @param \Drupal\stripe_registration\StripeRegistrationService $stripe_registration_stripe_api
   */
  public function __construct(StripeRegistrationService $stripe_registration_stripe_api) {
    $this->stripeRegApi = $stripe_registration_stripe_api;
  }

  /**
   * {@inheritdoc}
   */
  static function getSubscribedEvents() {
    $events['stripe_api.webhook'][] = ['onIncomingWebhook'];
    return $events;
  }

  /**
   * Process an incoming webhook.
   *
   * @param \Drupal\stripe_api\Event\StripeApiWebhookEvent $event
   *   Logs an incoming webhook of the setting is on.
   */
  public function onIncomingWebhook(StripeApiWebhookEvent $event) {
    $type = $event->type;
    $data = $event->data;
    $stripe_event = $event->event;

    // React to subscription life cycle events.
    // @see https://stripe.com/docs/subscriptions/lifecycle
    switch ($type) {
      // Occurs whenever a customer with no subscription is signed up for a plan.
      case 'customer.subscription.created':
        break;
      // Occurs whenever a customer ends their subscription.
      case 'customer.subscription.deleted':
        $remote_subscription = $data->object;
        /** @var StripeSubscriptionEntity $local_subscription */
        $local_subscription = $this->stripeRegApi->loadLocalSubscription(['subscription_id' => $remote_subscription->id]);
        $local_subscription->delete();

        break;
      // Occurs three days before the trial period of a subscription is scheduled to end.
      case 'customer.subscription.trial_will_end':
        break;
      // Occurs whenever a subscription changes. Examples would include switching from one plan to another, or switching status from trial to active.
      case 'customer.subscription.updated':
        break;
    }

  }

}
