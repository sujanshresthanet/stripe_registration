<?php

/**
 * @file
 * Contains stripe_plan.page.inc.
 *
 * Page callback for Stripe plan entities.
 */

use Drupal\Core\Render\Element;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Prepares variables for Stripe plan templates.
 *
 * Default template: stripe_plan.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_stripe_plan(array &$variables) {
  // Fetch StripePlanEntity Entity Object.
  $stripe_plan = $variables['elements']['#stripe_plan'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
