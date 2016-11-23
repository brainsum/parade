<?php

namespace Drupal\marketo_poll\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'marketo_poll' field type.
 *
 * @FieldType(
 *   id = "marketo_poll",
 *   label = @Translation("Marketo Poll"),
 *   module = "marketo_poll",
 *   description = @Translation("Marketo Poll integration."),
 *   default_widget = "marketo_poll",
 *   default_formatter = "marketo_poll"
 * )
 */
class MarketoPoll extends FieldItemBase {
  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return array(
      'columns' => array(
        'subscription_url' => array(
          'description' => 'Subscription base URL',
          'type' => 'varchar',
          'length' => 2048,
        ),
        'poll_class' => array(
          'description' => 'The Poll Class.',
          'type' => 'varchar',
          'length' => 255,
        ),
        'poll_id' => array(
          'description' => 'The Poll ID.',
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
    $poll_class = $this->get('poll_class')->getValue();
    $poll_id = $this->get('poll_id')->getValue();
    return
      $subscription_url === NULL || $subscription_url === '' ||
      $poll_class === NULL || $poll_class === '' ||
      $poll_id === NULL || $poll_id === '';
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['subscription_url'] = DataDefinition::create('string')
      ->setLabel(t('Subscription base URL'));
    $properties['poll_class'] = DataDefinition::create('string')
      ->setLabel(t('Poll Class'));
    $properties['poll_id'] = DataDefinition::create('string')
      ->setLabel(t('Poll ID'));

    return $properties;
  }

}
