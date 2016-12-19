<?php

namespace Drupal\parade\Form;

use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form that configures Parade settings.
 */
class ParadeSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'parade_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'parade.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL) {
    // @todo Remove when form works.
    drupal_set_message('This page is heavily under development, and it may break.', 'warning', FALSE);

    $config = $this->config('parade.settings');

    // @todo Are these needed?
    $form['result'] = $form_state->get('result');
    $form['#tree'] = TRUE;

    // Build the parent form and make changes.
    $form = parent::buildForm($form, $form_state);
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
      '#submit' => ['::submitForm'],
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->config('parade.settings')->save();

    parent::submitForm($form, $form_state);
  }

}
