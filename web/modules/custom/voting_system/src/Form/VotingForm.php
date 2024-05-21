<?php

declare(strict_types=1);

namespace Drupal\voting_system\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\voting_system\Entity\Questions;
use Drupal\Core\Url;

/**
 * Provides a Voting System form.
 */
final class VotingForm extends FormBase
{

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string
  {
    return 'voting_system_voting';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array
  {
    // Get the configuration.
    $config = $this->config('voting_system.settings');

    // Check if the voting system is deactivated globally.
    $deactivate_voting = $config->get('deactivate_voting');

    // Get the file URL generator service.
    $file_url_generator = \Drupal::service('file_url_generator');

    // Get all active questions.
    $questions = \Drupal::entityTypeManager()->getStorage('voting_system_questions')->loadByProperties(['status' => 1]);

    foreach ($questions as $question) {
      $question_id = $question->id();
      $form['question_' . $question_id] = [
        '#type' => 'fieldset',
        '#title' => $question->get('description')->value,
      ];

      // Initialize the options array.
      $options = [];

      // Get answers for this question.
      $question_entity = \Drupal::entityTypeManager()->getStorage('voting_system_questions')->load($question_id);
      $answers = $question_entity->get('answer')->referencedEntities();

      foreach ($answers as $answer) {
        $answer_id = $answer->id();
        $answer_label = $answer->get('description')->value;
        $answer_text_label = $answer->label(); // Get the answer's label field.

        // Load the image associated with the answer.
        $image_field = $answer->get('image')->entity;
        $image_url = '';
        if ($image_field) {
          $image_url = $file_url_generator->generateAbsoluteString($image_field->getFileUri());
        }

        // Build the answer label, text label, and image markup.
        $option_markup = '<div><strong>' . $answer_text_label . '</strong><p>' . $answer_label . '</p>';
        if ($image_url) {
          $option_markup .= '<img src="' . $image_url . '" width="100px" height="100px"/>';
        }
        $option_markup .= '</div>';

        $options[$answer_id] = $option_markup;
      }

      $form['question_' . $question_id]['answers_' . $question_id] = [
        '#type' => 'radios',
        '#title' => $this->t('Select an answer:'),
        '#options' => $options,
        '#required' => TRUE,
        '#attributes' => [
          'name' => 'answers_' . $question_id, // Unique name for each set of answers.
        ],
      ];

      // Show the vote field only if the voting system is not deactivated globally.
      if (!$deactivate_voting) {
        $form['question_' . $question_id]['vote_' . $question_id] = [
          '#type' => 'radios',
          '#title' => $this->t('Vote this question:'),
          '#options' => [
            1 => $this->t('1'),
            2 => $this->t('2'),
            3 => $this->t('3'),
            4 => $this->t('4'),
            5 => $this->t('5'),
          ],
          '#attributes' => [
            'class' => ['vote-radio-buttons'],
            'name' => 'vote_' . $question_id, // Unique name for each set of votes.
          ],
          '#prefix' => '<div class="vote-wrapper" style="display:none;">',
          '#suffix' => '</div>',
        ];
      }
    }

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Vote'),
    ];

    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    $form['#attached']['library'][] = 'voting_system/vote_form';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void
  {
    // @todo Validate the form here.
    // Example:
    // @code
    //   if (mb_strlen($form_state->getValue('message')) < 10) {
    //     $form_state->setErrorByName(
    //       'message',
    //       $this->t('Message should be at least 10 characters.'),
    //     );
    //   }
    // @endcode
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void
  {
    // Get the submitted values from the form.
    $values = $form_state->getValues();

    // Iterate over the submitted answers and votes.
    foreach ($values as $key => $value) {
      // Check if the field is a vote field.
      if (strpos($key, 'vote_') !== false) {
        // Get the question ID and answer ID.
        $question_id = substr($key, 5);

        // Load the question entity.
        $question = Questions::load($question_id);
        $answer_id = $values['answers_' . $question_id];

        // Load the answer entity.
        $answer = \Drupal::entityTypeManager()->getStorage('voting_system_answer')->load($answer_id);

        // Create a new vote entity.
        $vote = \Drupal\voting_system\Entity\Vote::create([
          'label' => 'Vote for Question ' . $question_id,
          'vote' => $value,
          'answer' => $answer,
        ]);
        $vote->save();

        // Add the vote to the question.
        $question->vote[] = $vote;
        $question->save();
      }
    }

    // Display a success message and redirect the user.
    $this->messenger()->addStatus($this->t('The vote has been submitted.'));
    $form_state->setRedirectUrl(Url::fromUri('internal:/voting-system'));
  }
}
