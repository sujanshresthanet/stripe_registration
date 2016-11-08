<?php

namespace Drupal\stripe_registration;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;
use Drupal\Core\Routing\LinkGeneratorTrait;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of Stripe plan entities.
 *
 * @ingroup stripe_registration
 */
class StripePlanEntityListBuilder extends EntityListBuilder {

  use LinkGeneratorTrait;

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['plan_id'] = $this->t('Stripe plan ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\stripe_registration\Entity\StripePlanEntity */
    $row['plan_id'] = $entity->plan_id->value;
    $row['name'] = $entity->label();
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   *
   * Builds the entity listing as renderable array for table.html.twig.
   *
   */
  public function render() {
    $build = parent::render();
    $build['table']['#footer'] = [
      'data' => [
        [
          'data' => $this->t(
            'Visit the %link page to synchronize with plans from Stripe.',
            [
              '%link' => Link::createFromRoute('Stripe Registration configuration', 'stripe_api.admin')->toString(),
            ]
          ),
          'colspan' => count($build['table']['#header'])
        ],
      ],
    ];
    return $build;
  }

}
