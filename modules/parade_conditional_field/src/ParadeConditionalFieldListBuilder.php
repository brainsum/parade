<?php

namespace Drupal\parade_conditional_field;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of Parade conditional field entities.
 */
class ParadeConditionalFieldListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   *
   * @todo remove route_match way, add
   */
  public function load() {
    $route_match = \Drupal::service('current_route_match');
    $paragraphs_type = $route_match->getParameter('paragraphs_type')->id;

    $entities = $this->storage->loadByProperties(['bundle' => $paragraphs_type]);

    // Sort the entities using the entity class's sort() method.
    // See \Drupal\Core\Config\Entity\ConfigEntityBase::sort().
    uasort($entities, [$this->entityType->getClass(), 'sort']);
    return $entities;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['bundle'] = $this->t('Bundle');
    $header['delta'] = '#';
    $header['layouts'] = $this->t('Layout(s)');
    $header['view_mode'] = $this->t('View mode');
    $header['classes'] = $this->t('Classes');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['bundle'] = $entity->getBundle();
    $row['delta'] = $entity->getNumericId();
    $row['layouts'] = implode(', ', $entity->getLayouts());
    $row['view_mode'] = $entity->getViewMode();
    $row['classes'] = implode(', ', $entity->getClasses());
    // You probably want a few more properties here...
    return $row + parent::buildRow($entity);
  }

}
