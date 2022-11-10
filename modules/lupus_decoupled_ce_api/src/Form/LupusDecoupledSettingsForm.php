<?php

namespace Drupal\lupus_decoupled_ce_api\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure lupus decoupled settings for this site.
 */
class LupusDecoupledSettingsForm extends ConfigFormBase {

  /**
   * Config name.
   */
  const CONFIG_NAME = 'lupus_decoupled_ce_api.settings';

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::CONFIG_NAME,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'lupus_decoupled_ce_api_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::CONFIG_NAME);

    $form['frontend_base_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Frontend Base URL'),
      '#default_value' => $config->get('frontend_base_url'),
      '#pattern' => 'https?://.*',
      '#placeholder' => 'https://your-frontend-site.com',
      '#description' => $this->t('The base URL of your frontend site.'),
    ];

    $form['frontend_routes_redirect'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('View content on decoupled frontend'),
      '#default_value' => $config->get('frontend_routes_redirect'),
      '#description' => $this->t('Opens frontend routes in the frontend by redirecting to the frontend.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $config = $this->config(static::CONFIG_NAME);
    $config->set('frontend_base_url', $form_state->getValue('frontend_base_url'));
    $config->set('frontend_routes_redirect', $form_state->getValue('frontend_routes_redirect'));
    $config->save();
  }

}