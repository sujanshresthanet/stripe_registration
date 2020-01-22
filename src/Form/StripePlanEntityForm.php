<?php

namespace Drupal\stripe_registration\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;

/**
 * Form controller for Stripe plan edit forms.
 *
 * @ingroup stripe_registration
 */
class StripePlanEntityForm extends ContentEntityForm {

  use MessengerTrait;

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\stripe_registration\Entity\StripePlanEntity */
    $form = parent::buildForm($form, $form_state);

    $form['plan_id']['#disabled'] = TRUE;
    $form['name']['#disabled'] = TRUE;
    $form['livemode']['#disabled'] = TRUE;

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
        $this->messenger()->addMessage($this->t('Created the %label Stripe plan.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Stripe plan.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.stripe_plan.canonical', ['stripe_plan' => $entity->id()]);
  }

}
