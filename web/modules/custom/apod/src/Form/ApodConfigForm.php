<?php

namespace Drupal\apod\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ApodConfigForm.
 *
 * @package Drupal\apod\Form
 */
class ApodConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'apod.api_config',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'apod_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('apod.api_config');
    $form['api_key'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('NASA API Key'),
      '#description' => $this->t('Enter the API key from NASA.'),
      '#maxlength' => 255,
      '#size' => 64,
      '#default_value' => $config->get('api_key'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    // Store the config.
    $this->config('apod.api_config')
      ->set('api_key', trim($form_state->getValue('api_key')))
      ->save();
  }

}
