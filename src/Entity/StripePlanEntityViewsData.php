<?php

namespace Drupal\stripe_registration\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for Stripe plan entities.
 */
class StripePlanEntityViewsData extends EntityViewsData implements EntityViewsDataInterface {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['stripe_plan']['table']['base'] = array(
      'field' => 'id',
      'title' => $this->t('Stripe plan'),
      'help' => $this->t('The Stripe plan ID.'),
    );

    return $data;
  }

}
