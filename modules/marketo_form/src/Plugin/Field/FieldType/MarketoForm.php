<?php

namespace Drupal\marketo_form\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'marketo_form' field type.
 *
 * @FieldType(
 *   id = "marketo_form",
 *   label = @Translation("Marketo Form"),
 *   module = "marketo_form",
 *   description = @Translation("Marketo Form integration."),
 *   default_widget = "marketo_form",
 *   default_formatter = "marketo_form"
 * )
 */
class MarketoForm extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return array(
      'columns' => array(
        'subscription_url' => array(
          'description' => t('Subscription base URL'),
          'type' => 'varchar',
          'length' => 2048,
        ),
        'munchkin_id' => array(
          'description' => t('The Munchkin ID.'),
          'type' => 'varchar',
          'length' => 255,
        ),
        'form_id' => array(
          'description' => t('The form ID.'),
          'type' => 'varchar',
          'length' => 255,
        ),
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $subscription_url = $this->get('subscription_url')->getValue();
    $munchkin_id = $this->get('munchkin_id')->getValue();
    $form_id = $this->get('form_id')->getValue();
    return
      $subscription_url === NULL || $subscription_url === '' ||
      $munchkin_id === NULL || $munchkin_id === '' ||
      $form_id === NULL || $form_id === '';
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['subscription_url'] = DataDefinition::create('string')
      ->setLabel(t('Subscription base URL'));
    $properties['munchkin_id'] = DataDefinition::create('string')
      ->setLabel(t('Munchkin ID'));
    $properties['form_id'] = DataDefinition::create('string')
      ->setLabel(t('Form ID'));

    return $properties;
  }

}
