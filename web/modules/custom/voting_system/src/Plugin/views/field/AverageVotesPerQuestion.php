<?php

declare(strict_types=1);

namespace Drupal\voting_system\Plugin\views\field;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Provides Average Votes per Question field handler.
 *
 * @ViewsField("voting_system_average_votes_per_question")
 *
 * @DCG
 * The plugin needs to be assigned to a specific table column through
 * hook_views_data() or hook_views_data_alter().
 * Put the following code to voting_system.views.inc file.
 * @code
 * function foo_views_data_alter(array &$data): void {
 *   $data['node']['foo_example']['field'] = [
 *     'title' => t('Example'),
 *     'help' => t('Custom example field.'),
 *     'id' => 'foo_example',
 *   ];
 * }
 * @endcode
 */
final class AverageVotesPerQuestion extends FieldPluginBase
{

  /**
   * {@inheritdoc}
   */
  protected function defineOptions(): array
  {
    $options = parent::defineOptions();
    $options['example'] = ['default' => ''];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state): void
  {
    parent::buildOptionsForm($form, $form_state);
    $form['example'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Example'),
      '#default_value' => $this->options['example'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function query(): void
  {
    // For non-existent columns (i.e. computed fields) this method must be
    // empty.
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values): string|MarkupInterface
  {
    $value = parent::render($values);

    // Get the entity object from the result row.
    $entity = $values->_entity;
    $count = 0;

    // Initialize counters for each vote value (1 to 5).
    $vote_counts = [
      1 => 0,
      2 => 0,
      3 => 0,
      4 => 0,
      5 => 0,
    ];

    // Get the referenced votes.
    $votes = $entity->get('vote')->referencedEntities();

    // Iterate over each vote to calculate the count.
    foreach ($votes as $vote) {
      // Check if the 'vote' field exists and has a valid value.
      if ($vote->hasField('vote') && !$vote->get('vote')->isEmpty()) {
        $vote_value = $vote->get('vote')->value;
        $count++;

        // Increment the appropriate vote value counter.
        if (isset($vote_counts[$vote_value])) {
          $vote_counts[$vote_value]++;
        }
      } else {
        // Debug: Log if the 'vote' field is missing or empty
        \Drupal::logger('voting_system')->debug('Vote has no vote field or it is empty.');
      }
    }

    // Calculate the percentage for each vote value.
    $vote_percentages = [];
    foreach ($vote_counts as $vote_value => $vote_count) {
      $vote_percentages[$vote_value] = $count > 0 ? ($vote_count / $count) * 100 : 0;
    }

    // Prepare the output (you can customize the format as needed).
    $output = '';
    foreach ($vote_counts as $vote_value => $vote_count) {
      $percentage = number_format($vote_percentages[$vote_value], 2);
      $output .= "Points $vote_value, $vote_count votes ($percentage%);\n";
    }

    // Return the rendered value.
    return $output;
  }
}
