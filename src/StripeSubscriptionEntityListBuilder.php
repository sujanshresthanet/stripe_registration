<?php

namespace Drupal\stripe_registration;

use Drupal\Core\Link;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of Stripe subscription entities.
 *
 * @ingroup stripe_registration
 */
class StripeSubscriptionEntityListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Stripe subscription ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\stripe_registration\Entity\StripeSubscriptionEntity */
    $row['id'] = $entity->id();
    $row['name'] = Link::fromTextAndUrl(
      $entity->label(),
      new Url(
        'entity.stripe_subscription.edit_form', [
          'stripe_subscription' => $entity->id(),
        ]
      )
    );
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritDoc}
   */
  protected function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);

    // Cancel button.
    if ($entity->get('cancel_at_period_end')->isEmpty() || !$entity->get('cancel_at_period_end')->first()->get('value')->getValue()) {
      $operations['cancel'] = [
        'title' => $this->t('Cancel'),
        'weight' => 1,
        'url' => Url::fromRoute('stripe_registration.stripe-subscription.cancel', ['remote_id' => $entity->id()]),
      ];
    }
    // Re-activate button.
    elseif ($entity->get('current_period_end')->isEmpty() || REQUEST_TIME < $entity->get('current_period_end')->first()->get('value')->getValue()) {
      $operations['reactivate'] = [
        'title' => $this->t('Re-activate'),
        'weight' => 1,
        'url' => Url::fromRoute('stripe_registration.stripe-subscription.reactivate', ['remote_id' => $entity->id()]),
      ];
    }

    return $operations;
  }

}
