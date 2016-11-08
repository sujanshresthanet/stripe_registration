<?php

namespace Drupal\stripe_registration\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for Stripe subscription entities.
 */
class StripeSubscriptionEntityViewsData extends EntityViewsData implements EntityViewsDataInterface {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['stripe_subscription']['table']['base'] = array(
      'field' => 'id',
      'title' => $this->t('Stripe subscription'),
      'help' => $this->t('The Stripe subscription ID.'),
    );

    return $data;
  }

}
