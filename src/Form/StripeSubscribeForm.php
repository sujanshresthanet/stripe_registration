<?php

namespace Drupal\stripe_registration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class StripeSubscribeForm.
 *
 * @package Drupal\stripe_registration\Form
 */
class StripeSubscribeForm extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'stripe_subscribe_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Form items are defined in stripe_registration_subscribe_form() so that
    // they may be dynamically added to one or more forms.
    $form['uid'] = [
      '#type' => 'hidden',
      '#default_value' => \Drupal::currentUser()->id(),
    ];

    return $form;
  }

  /**
    * {@inheritdoc}
    */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Submission is handled via stripe_registration_submit().
  }

}
