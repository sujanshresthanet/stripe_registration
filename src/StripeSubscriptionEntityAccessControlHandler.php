<?php

namespace Drupal\stripe_registration;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Stripe subscription entity.
 *
 * @see \Drupal\stripe_registration\Entity\StripeSubscriptionEntity.
 */
class StripeSubscriptionEntityAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\stripe_registration\Entity\StripeSubscriptionEntityInterface $entity */
    switch ($operation) {
      case 'view':
      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'administer stripe subscriptions');

      case 'update':
        return AccessResult::forbidden();
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::forbidden();
  }

}
