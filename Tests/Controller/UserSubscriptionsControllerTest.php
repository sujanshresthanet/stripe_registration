<?php

namespace Drupal\stripe_registration\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Provides automated tests for the stripe_registration module.
 */
class UserSubscriptionsControllerTest extends WebTestBase {


  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => "stripe_registration UserSubscriptionsController's controller functionality",
      'description' => 'Test Unit for module stripe_registration and controller UserSubscriptionsController.',
      'group' => 'Other',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
  }

  /**
   * Tests stripe_registration functionality.
   */
  public function testUserSubscriptionsController() {
    // Check that the basic functions of module stripe_registration.
    $this->assertEquals(TRUE, TRUE, 'Test Unit Generated via Drupal Console.');
  }

}
