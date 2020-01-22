<?php

namespace Drupal\stripe_registration\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;

/**
 * Form controller for Stripe subscription edit forms.
 *
 * @ingroup stripe_registration
 */
class StripeSubscriptionEntityForm extends ContentEntityForm {

  use MessengerTrait;

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\stripe_registration\Entity\StripeSubscriptionEntity */
    $form = parent::buildForm($form, $form_state);
    $entity = $this->entity;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Stripe subscription.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Stripe subscription.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.stripe_subscription.canonical', ['stripe_subscription' => $entity->id()]);
  }

}
