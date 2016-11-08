<?php

namespace Drupal\stripe_registration;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Url;
use Drupal\stripe_api\StripeApiService;
use Stripe\Plan;
use Stripe\Subscription;

/**
 * Class StripeRegistrationService.
 *
 * @package Drupal\stripe_registration
 */
class StripeRegistrationService {

  /**
   * Drupal\Core\Config\ConfigFactory definition.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\Logger\LoggerChannelInterface*/
  protected $logger;

  /**
   * Constructor.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, LoggerChannelInterface $logger, StripeApiService $stripe_api) {
    $this->config = $config_factory->get('stripe_registration.settings');
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger;
    $this->stripeApi = $stripe_api;
  }

  /**
   * @param UserInterface $user
   *
   * @return bool
   */
  public function userHasStripeSubscription($user) {
    return !empty($user->stripe_customer_id->value);
  }

  /**
   * @param UserInterface $user
   *
   * @return bool|\Stripe\Collection
   */
  public function loadRemoteSubscriptionByUser($user) {
    return $this->loadRemoteSubscriptionMultiple(['customer' => $user->stripe_customer_id->value]);
  }

  /**
   * @param array $args
   *
   * @return bool|\Stripe\Collection
   */
  public function loadRemoteSubscriptionMultiple($args = []) {
    // @todo add try, catch.
    $subscriptions = Subscription::all($args);

    if (!count($subscriptions->data)) {
      return FALSE;
    }

    return $subscriptions;
  }

  /**
   * @param array $properties
   *
   * @return StripeSubscriptionEntity
   */
  public function loadLocalSubscription($properties = []) {
    $stripe_subscription_entities = $this->entityTypeManager
      ->getStorage('stripe_subscription')
      ->loadByProperties($properties);

    if (!count($stripe_subscription_entities)) {
      return FALSE;
    }

    $first = reset($stripe_subscription_entities);

    return $first;
  }

  /**
   * @param array $properties
   *
   * @return StripeSubscriptionEntity[]
   */
  public function loadLocalSubscriptionMultiple($properties = []) {
    $stripe_subscription_entities = $this->entityTypeManager
      ->getStorage('stripe_subscription');

    $stripe_subscription_entities->loadByProperties($properties);

    return $stripe_subscription_entities;
  }

  /**
   *
   */
  public function loadLocalPlanMultiple() {
    $stripe_plan_entities = $this->entityTypeManager
      ->getStorage('stripe_plan')
      ->loadMultiple();

    return $stripe_plan_entities;
  }

  /**
   * @return \Stripe\Plan[]
   */
  public function loadRemotePlanMultiple($args = []) {
    $plans = Plan::all($args);

    // @todo handle no results case.

    // Re-key array.
    $keyed_plans = [];
    foreach ($plans->data as $plan) {
      $keyed_plans[$plan->id] = $plan;
    }

    return $keyed_plans;
  }

  /**
   *
   */
  public function loadRemotePlanById($plan_id) {
    $plan = $this->loadRemotePlanMultiple(['id' => $plan_id]);

    return $plan->data;
  }

  /**
   * @param bool $delete
   *   If true, local plans without matching remote plans will be deleted from Drupal.
   */
  public function syncPlans($delete = FALSE) {
    // @todo Handle pagination here.
    $remote_plans = $this->loadRemotePlanMultiple();
    $local_plans = $this->entityTypeManager->getStorage('stripe_plan')->loadMultiple();

    /** @var EntityInterface[] $local_plans_keyed */
    $local_plans_keyed = [];
    foreach ($local_plans as $local_plan) {
      $local_plans_keyed[$local_plan->plan_id->value] = $local_plan;
    }

    $plans_to_delete = array_diff(array_keys($local_plans_keyed), array_keys($remote_plans));
    $plans_to_create = array_diff(array_keys($remote_plans), array_keys($local_plans_keyed));
    $plans_to_update = array_intersect(array_keys($remote_plans), array_keys($local_plans_keyed));

    $this->logger->info('Synchronizing Stripe plans.');

    // Create new plans.
    foreach ($plans_to_create as $plan_id) {
      $this->entityTypeManager->getStorage('stripe_plan')->create([
        'plan_id' => $remote_plans[$plan_id]->id,
        'name' => $remote_plans[$plan_id]->name,
        'livemode' => $remote_plans[$plan_id]->livemode == 'true',
      ])->save();
      $this->logger->info('Created @plan_id plan.', ['@plan_id' => $plan_id]);
    }
    // Delete invalid plans.
    if ($delete) {
      $entities_to_delete = [];
      foreach ($plans_to_delete as $plan_id) {
        $entities_to_delete[] = $local_plans_keyed[$plan_id];
      }
      $this->entityTypeManager->getStorage('stripe_plan')
        ->delete($entities_to_delete);
      $this->logger->info('Deleted plans @plan_ids.', ['@plan_ids' => $plans_to_delete]);
    }
    // Update existing plans.
    foreach ($plans_to_update as $plan_id) {
      /** @var EntityInterface $plan */
      $plan = $local_plans_keyed[$plan_id];
      $plan->set('name', $remote_plans[$plan_id]->name);
      $plan->set('livemode', $remote_plans[$plan_id]->livemode == 'true');
      $plan->save();
      $this->logger->info('Updated @plan_id plan.', ['@plan_id' => $plan_id]);
    }

    drupal_set_message(t('Stripe plans were synchronized. Visit %link to see synchronized plans.', ['%link' => Link::fromTextAndUrl('Stripe plan list', Url::fromUri('internal:/admin/structure/stripe-registration/stripe-plan'))->toString()]), 'status');
  }

  /**
   *
   */
  public function syncRemoteSubscriptionToLocal($remote_id) {
    $remote_subscripton = Subscription::retrieve($remote_id);
    $local_subscription = $this->loadLocalSubscription(['subscription_id' => $remote_id]);
    $local_subscription->updateFromUpstream($remote_subscripton);
    $this->logger->info('Updated subscription entity @subscription_id.', ['@subscription_id' => $local_subscription->id()]);
  }

  /**
   * @param \Stripe\Subscription $subscription
   */
  public function createLocalSubscription(Subscription $subscription) {
    // @todo ensure that a subscription with this id does not already exist.
    // @todo if subscription exists, trigger postSave on subscription entity to cause role assignment.
    $current_period_end = DrupalDateTime::createFromTimestamp($subscription->current_period_end);
    $values = [
      'plan_id' => $subscription->plan->id,
      'subscription_id' => $subscription->id,
      'customer_id' => $subscription->customer,
      'status' => $subscription->status,
      'roles' => [],
      'current_period_end' => ['value' => $current_period_end->format('U')],
    ];
    $subscription = $this->entityTypeManager->getStorage('stripe_subscription')->create($values);
    $subscription->save();
    $this->logger->info('Created @subscription_id plan.', ['@subscription_id' => $subscription->id]);

    return $subscription;
  }

  /**
   *
   */
  public function reactivateRemoteSubscription($remote_id) {
    // @see https://stripe.com/docs/subscriptions/guide#reactivating-canceled-subscriptions
    $subscripton = Subscription::retrieve($remote_id);
    $subscripton->plan = $subscripton->plan->id;
    $subscripton->save();
    drupal_set_message('Subscription re-activated.');
    $this->logger->info('Re-activated remote subscription @subscription_id id.', ['@subscription_id' => $remote_id]);
  }

  /**
   *
   */
  public function cancelRemoteSubscription($remote_id) {
    $subscripton = Subscription::retrieve($remote_id);
    $subscripton->cancel(['at_period_end' => TRUE]);
    drupal_set_message('Subscription cancelled. It will not renew after the current pay period.');
    $this->logger->info('Cancelled remote subscription @subscription_id id.', ['@subscription_id' => $remote_id]);
  }

  /**
   *
   */
  public function setLocalUserCustomerId($uid, $customer_id) {
    /** @var Customer $user */
    $user = \Drupal::entityManager()->getStorage('user')->load($uid);
    $user->set('stripe_customer_id', $customer_id);
    $user->save();
  }

}
