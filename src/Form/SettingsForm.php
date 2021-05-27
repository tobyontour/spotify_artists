<?php

namespace Drupal\spotify_artists\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Spotify Artists settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'spotify_artists_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['spotify_artists.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['message'] = [
      '#markup' => $this->t('Visit to register an App'),
    ];
    $form['client_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client ID'),
      '#default_value' => $this->config('spotify_artists.settings')->get('client_id'),
      '#size' => 32,
      '#maxlength' => 32,
      '#required' => TRUE,
    ];
    $form['secret_key'] = [
      '#type' => 'password',
      '#title' => $this->t('Secret Key'),
      '#size' => 32,
      '#maxlength' => 32,
      '#required' => TRUE,
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (strlen($form_state->getValue('client_id')) != 32 && !preg_match('#[a-z0-0]#', $form_state->getValue('client_id'))) {
      $form_state->setErrorByName('client_id', $this->t('The client ID needs to be 32 characters long and contain only letters and numbers.'));
    }
    if (strlen($form_state->getValue('secret_key')) != 32 && !preg_match('#[a-z0-0]#', $form_state->getValue('secret_key'))) {
      $form_state->setErrorByName('secret_key', $this->t('The secret key needs to be 32 characters long and contain only letters and numbers.'));
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('spotify_artists.settings')
      ->set('client_id', $form_state->getValue('client_id'))
      ->set('secret_key', $form_state->getValue('secret_key'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
