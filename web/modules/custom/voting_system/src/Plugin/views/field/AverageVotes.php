<?php

declare(strict_types=1);

namespace Drupal\voting_system\Plugin\views\field;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Provides Average Votes field handler.
 *
 * @ViewsField("voting_system_average_votes")
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
final class AverageVotes extends FieldPluginBase
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
    $sum = 0;

    // Get the referenced votes.
    $votes = $entity->get('vote')->referencedEntities();

    // Iterate over each vote to calculate the count and sum.
    foreach ($votes as $vote) {
      // Check if the 'vote' field exists and has a valid value.
      if ($vote->hasField('vote') && !$vote->get('vote')->isEmpty()) {
        $count++;
        $sum += $vote->get('vote')->value;
      } else {
        // Debug: Log if the 'vote' field is missing or empty
        \Drupal::logger('voting_system')->debug('Vote has no vote field or it is empty.');
      }
    }

    // Calculate the average.
    $average = $count > 0 ? $sum / $count : 0;

    // Format the average to 2 decimal places.
    $average = number_format($average, 2);

    // Return the rendered value.
    return $average;
  }
}
