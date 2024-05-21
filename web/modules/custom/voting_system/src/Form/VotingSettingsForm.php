<?php

declare(strict_types=1);

namespace Drupal\voting_system\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Voting System settings for this site.
 */
final class VotingSettingsForm extends ConfigFormBase
{

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string
  {
    return 'voting_system_voting_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array
  {
    return ['voting_system.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array
  {
    $config = $this->config('voting_system.settings');

    $form['deactivate_voting'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Deactivate the voting system globally'),
      '#default_value' => $config->get('deactivate_voting'),
    ];

    $form['deactivate_voting_per_question'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Deactivate votes for each question'),
      '#default_value' => $config->get('deactivate_voting_per_question'),
    ];

    return parent::buildForm($form, $form_state) + $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void
  {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void
  {
    $this->config('voting_system.settings')
      ->set('deactivate_voting', $form_state->getValue('deactivate_voting'))
      ->set('deactivate_voting_per_question', $form_state->getValue('deactivate_voting_per_question'))
      ->save();
    parent::submitForm($form, $form_state);
  }
}
