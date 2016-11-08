<?php

namespace Drupal\stripe_registration\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Stripe plan entities.
 *
 * @ingroup stripe_registration
 */
interface StripePlanEntityInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Stripe plan name.
   *
   * @return string
   *   Name of the Stripe plan.
   */
  public function getName();

  /**
   * Sets the Stripe plan name.
   *
   * @param string $name
   *   The Stripe plan name.
   *
   * @return \Drupal\stripe_registration\Entity\StripePlanEntityInterface
   *   The called Stripe plan entity.
   */
  public function setName($name);

  /**
   * Gets the Stripe subscription creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Stripe subscription.
   */
  public function getCreatedTime();

  /**
   * Sets the Stripe subscription creation timestamp.
   *
   * @param int $timestamp
   *   The Stripe subscription creation timestamp.
   *
   * @return \Drupal\stripe_registration\Entity\StripeSubscriptionEntityInterface
   *   The called Stripe subscription entity.
   */
  public function setCreatedTime($timestamp);
}
