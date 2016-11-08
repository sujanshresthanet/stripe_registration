<?php

namespace Drupal\stripe_registration\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\user\UserInterface;

/**
 * Defines the Stripe plan entity.
 *
 * @ingroup stripe_registration
 *
 * @ContentEntityType(
 *   id = "stripe_plan",
 *   label = @Translation("Stripe plan"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\stripe_registration\StripePlanEntityListBuilder",
 *     "views_data" = "Drupal\stripe_registration\Entity\StripePlanEntityViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\stripe_registration\Form\StripePlanEntityForm",
 *       "add" = "Drupal\stripe_registration\Form\StripePlanEntityForm",
 *       "edit" = "Drupal\stripe_registration\Form\StripePlanEntityForm",
 *       "delete" = "Drupal\stripe_registration\Form\StripePlanEntityDeleteForm",
 *     },
 *     "access" = "Drupal\stripe_registration\StripePlanEntityAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\stripe_registration\StripePlanEntityHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "stripe_plan",
 *   admin_permission = "administer stripe plan entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/stripe-registration/stripe-plan/{stripe_plan}",
 *     "add-form" = "/admin/structure/stripe-registration/stripe-plan/add",
 *     "edit-form" = "/admin/structure/stripe-registration/stripe-plan/{stripe_plan}/edit",
 *     "delete-form" = "/admin/structure/stripe-registration/stripe-plan/{stripe_plan}/delete",
 *     "collection" = "/admin/structure/stripe-registration/stripe-plan",
 *   },
 *   field_ui_base_route = "stripe_plan.settings"
 * )
 */
class StripePlanEntity extends ContentEntityBase implements StripePlanEntityInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += array(
      'user_id' => \Drupal::currentUser()->id(),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['plan_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Stripe Plan ID'))
      ->setDescription(t('The Stripe ID for this plan.'))
      ->setSettings(array(
        'max_length' => 50,
        'text_processing' => 0,
      ))
      ->setDefaultValue('')
      ->setCardinality(1)
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => -4,
      ));

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Stripe plan entity.'))
      ->setSettings(array(
        'max_length' => 50,
        'text_processing' => 0,
      ))
      ->setDefaultValue('')
      ->setCardinality(1)
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => -4,
      ));

    $fields['livemode'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Live mode'))
      ->setDescription(t('If this plan is listed as live on Stripe.'))
      ->setSettings(array(
        'max_length' => 50,
        'text_processing' => 0,
        'on_label' => new TranslatableMarkup('Live'),
      ))
      ->setDefaultValue('')
      ->setCardinality(1)
      ->setDisplayOptions('form', array(
        'type' => 'boolean_checkbox',
        'weight' => -4,
      ));

    /** @var \Drupal\user\RoleInterface[] $roles */
    $roles = user_roles(TRUE);
    $role_options = [];
    foreach ($roles as $rid => $role) {
      $role_options[$rid] = $role->label();
    }
    // @todo Prevent administrator roles from being added here.
    $fields['roles'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Roles'))
      ->setDescription(t('Roles that will be granted to users actively subscribed to this plan. Warning: these roles will be removed from users who have cancelled or unpaid subscriptions for this plan!'))
      ->setSettings(array(
        'max_length' => 50,
        'text_processing' => 0,
        'allowed_values' => $role_options,
      ))
      ->setCardinality(-1)
      ->setRequired(FALSE)
      ->setDefaultValue('')
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
        'size' => 10,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'options_select',
        'weight' => -4,
        'size' => 10,
      ));

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));


    return $fields;
  }

}
