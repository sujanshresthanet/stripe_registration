<?php

namespace Drupal\stripe_registration\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Url;
use Drupal\user\UserInterface;
use Stripe\Subscription;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\stripe_registration\StripeRegistrationService;
use Drupal\Core\Entity\EntityManager;

/**
 * Class UserSubscriptionsController.
 *
 * @package Drupal\stripe_registration\Controller
 */
class UserSubscriptionsController extends ControllerBase {

  /**
   * Drupal\stripe_registration\StripeRegistrationService definition.
   *
   * @var \Drupal\stripe_registration\StripeRegistrationService
   */
  protected $stripeApi;
  /**
   * Drupal\Core\Entity\EntityManager definition.
   *
   * @var \Drupal\Core\Entity\EntityManager
   */
  protected $entityManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(StripeRegistrationService $stripe_api, EntityManager $entity_manager) {
    $this->stripeApi = $stripe_api;
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('stripe_registration.stripe_api'),
      $container->get('entity.manager')
    );
  }

  /**
   * Getusersubscriptions.
   *
   * @return string
   *   Return Hello string.
   */
  public function viewAll(UserInterface $user) {

    if ($user->stripe_customer_id->value && $user_subscriptions = $this->stripeApi->loadRemoteSubscriptionByUser($user)) {

      $output['subscriptions'] = array(
        '#type' => 'table',
        '#header' => array($this->t('Plan'), $this->t('Status'), $this->t('Current Period'), $this->t('Will renew'), $this->t('Operations')),
        '#empty' => $this->t('No subscriptions.'),
      );

      /** @var Subscription $remote_subscription */
      foreach ($user_subscriptions->data as $remote_subscription) {
        $local_subscription = $this->stripeApi->loadLocalSubscription(['subscription_id' => $remote_subscription->id]);
        // Attempt to create the subscription locally.
        if (!$local_subscription) {
          $local_subscription = $this->stripeApi->createLocalSubscription($remote_subscription);
        }

        // Show local subscription, if active.
        if ($local_subscription && empty($remote_subscription->ended_at)) {

          $output['subscriptions'][$remote_subscription->id]['plan'] = [
            '#plain_text' => $remote_subscription->plan->name,
          ];
          $output['subscriptions'][$remote_subscription->id]['status'] = [
            '#plain_text' => $remote_subscription->status,
          ];
          $output['subscriptions'][$remote_subscription->id]['period'] = [
            '#plain_text' => DrupalDateTime::createFromTimestamp($remote_subscription->current_period_start)->format('F d, Y') . ' - ' . DrupalDateTime::createFromTimestamp($remote_subscription->current_period_end)->format('F d, Y'),
          ];
          $output['subscriptions'][$remote_subscription->id]['renew'] = [
            '#plain_text' => $remote_subscription->cancel_at_period_end ? $this->t('No') : $this->t('Yes')
          ];

          $output['subscriptions'][$remote_subscription->id]['operations'] = [];

          // Cancel button.
          if (!$remote_subscription->cancel_at_period_end) {
            $output['subscriptions'][$remote_subscription->id]['operations']['data'] = [
              '#type' => 'operations',
              '#links' => [
                'delete' => [
                  'title' => t('Cancel'),
                  'url' => Url::fromRoute('stripe_registration.stripe-subscription.cancel', ['remote_id' => $remote_subscription->id]),
                ],
              ],
            ];
          }
          // Re-activate button.
          elseif (REQUEST_TIME < $remote_subscription->current_period_end ){
            $output['subscriptions'][$remote_subscription->id]['operations']['reactivate'] = [
              '#type' => 'operations',
              '#links' => [
                'delete' => [
                  'title' => t('Re-activate'),
                  'url' => Url::fromRoute('stripe_registration.stripe-subscription.reactivate', ['remote_id' => $remote_subscription->id]),
                ],
              ],
            ];
          }
        }
      }
    }
    else {
      $form = $this->formBuilder()->getForm('Drupal\stripe_registration\Form\StripeSubscribeForm');

      return $form;
    }

    return $output;
  }

  public function cancelSubscription() {
    $remote_id = \Drupal::request()->get('remote_id');
    $this->stripeApi->cancelRemoteSubscription($remote_id);
    $this->stripeApi->syncRemoteSubscriptionToLocal($remote_id);

    return $this->redirect("stripe_registration.user.subscriptions.viewAll", [
      'user' => $this->currentUser()->id(),
    ]);
  }

  public function reactivateSubscription() {
    $remote_id = \Drupal::request()->get('remote_id');
    $this->stripeApi->reactivateRemoteSubscription($remote_id);
    $this->stripeApi->syncRemoteSubscriptionToLocal($remote_id);

    return $this->redirect("stripe_registration.user.subscriptions.viewAll", [
      'user' => $this->currentUser()->id(),
    ]);
  }

}
