<?php

/**
 * @file
 * Contains stripe_subscription.page.inc.
 *
 * Page callback for Stripe subscription entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Stripe subscription templates.
 *
 * Default template: stripe_subscription.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_stripe_subscription(array &$variables) {
  // Fetch StripeSubscriptionEntity Entity Object.
  $stripe_subscription = $variables['elements']['#stripe_subscription'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
