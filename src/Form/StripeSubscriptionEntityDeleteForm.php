<?php

namespace Drupal\stripe_registration\Form;

use Drupal\Core\Entity\ContentEntityDeleteForm;

/**
 * Provides a form for deleting Stripe subscription entities.
 *
 * @ingroup stripe_registration
 */
class StripeSubscriptionEntityDeleteForm extends ContentEntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Are you sure you want to cancel subscription %myentity?', array('%myentity' => $this->entity->label()));
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Cancel');
  }

}
