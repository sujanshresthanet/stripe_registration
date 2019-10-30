<?php

namespace Drupal\stripe_registration;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Routing\LinkGeneratorTrait;
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
    $row['name'] = \Drupal\Core\Link::fromTextAndUrl(
      $entity->label(),
      new Url(
        'entity.stripe_subscription.edit_form', array(
          'stripe_subscription' => $entity->id(),
        )
      )
    );
    return $row + parent::buildRow($entity);
  }

}
