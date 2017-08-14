<?php

namespace Drupal\stripe_registration\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountProxy;

/**
 * Class MySubscriptions.
 */
class MySubscriptions extends ControllerBase {

  /**
   * Redirect.
   *
   * @return string
   *   Return Hello string.
   */
  public function redirectToSubscriptions() {
    return $this->redirect('stripe_registration.user.subscriptions.viewall', ['user' => $this->currentUser()->id()]);
  }

}
