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

    $form['color_schemes'] = array(
      '#type' => 'details',
      '#title' => $this->t('Color Schemes'),
      '#description' => '[TODO] Write help text here.',
      '#open' => TRUE,
    );

    $form['color_schemes']['color_picker'] = array(
      '#type' => 'color',
      '#default_value' => '#ffffff',
      '#attributes' => [
        'class' => ['colorpicker'],
      ],
    );

    $form['palette'] = array(
      '#type' => 'vertical_tabs',
      '#prefix' => '[TODO] Write help text here for the color palette. This will list all the loaded SASS colors found in the default theme.',
    );

    $test_colors = [
      'blue' => [1,2,3,4],
      'red' => [5,6,7,8],
    ];

    $i = 0;
    foreach ($test_colors as $group => $items) {
      $form["palette[$i]"] = array(
        '#type' => 'details',
        '#title' => $group,
        '#tab_summary' => 'asdasdf',
        '#group' => 'palette',
        '#open' => TRUE,
      );
      $j = 0;
      foreach ($items as $item) {
        $form["palette[$i]"][$j] = array(
          '#type' => 'markup',
          '#markup' => $item,
        );
        $j++;
      }
      $i++;
    }

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
