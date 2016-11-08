<?php

namespace Drupal\stripe_registration\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Stripe subscription entities.
 *
 * @ingroup stripe_registration
 */
interface StripeSubscriptionEntityInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Stripe subscription name.
   *
   * @return string
   *   Name of the Stripe subscription.
   */
  public function getName();

  /**
   * Sets the Stripe subscription name.
   *
   * @param string $name
   *   The Stripe subscription name.
   *
   * @return \Drupal\stripe_registration\Entity\StripeSubscriptionEntityInterface
   *   The called Stripe subscription entity.
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
