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

    // Load the colorpicker library.
    $form['#attached']['library'] = ['parade/colorpicker'];

    // $form['color_schemes'] = array(
    //   '#type' => 'checkboxes',
    //   '#title' => t('LDAP Server'),
    //   '#options' => $ldap_servers,
    //   '#multiple' => FALSE,
    //   '#default_value' => $config->get('ldap_server') ? $config->get('ldap_server') : '',
    //   '#description' => 'asdasdasd',
    // );

    $form['styling'] = array(
      '#type' => 'details',
      '#title' => $this->t('Color Schemes'),
      '#open' => TRUE,
    );

    $form['styling']['color_picker'] = array(
      '#type' => 'color',
      '#default_value' => '#ffffff',
      '#title' => $this->t('Choose color...'),
      '#attributes' => [
        'class' => ['colorpicker'],
      ],
    );

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
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Validate video URL.
    // if (!UrlHelper::isValid($form_state->getValue('video'), TRUE)) {
    //   $form_state->setErrorByName('video', $this->t("The video url '%url' is invalid.", array('%url' => $form_state->getValue('video'))));
    // }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->config('parade.settings')
      ->set('color_schemes', $values['color_schemes'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}
