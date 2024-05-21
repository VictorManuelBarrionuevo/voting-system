<?php

declare(strict_types=1);

namespace Drupal\voting_system\Plugin\views\field;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides Total Votes field handler.
 *
 * @ViewsField("voting_system_total_votes")
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
final class TotalVotes extends FieldPluginBase
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
    // Retrieve the module configuration.
    $config = \Drupal::config('voting_system.settings');

    // Check if voting per question is deactivated globally.
    if ($config->get('deactivate_voting_per_question')) {
      // If deactivated, return an empty string.
      return 'Result Hidden';
    }

    // Continue with the existing logic if voting per question is not deactivated.
    $value = parent::render($values);

    // Get the entity object from the result row.
    $entity = $values->_entity;
    $count = 0;
    $votesNotNull = $entity->get('vote')->referencedEntities();
    foreach ($votesNotNull as $vote) {
      // Check if the 'vote' field exists and has a valid value.
      if (isset($vote->get('vote')->value) && $vote->get('vote')->value !== null) {
        \Drupal::logger('voting_system')->debug($vote->get('vote')->value);
        $count++;
      } else {
        // Debug: Log if the 'vote' field is missing or empty
        \Drupal::logger('voting_system')->debug('Vote has no vote field or it is empty.');
      }
    }

    // Get the referenced votes.
    $votes = count($entity->get('vote')->referencedEntities());

    // Return the rendered value.
    return strval($count);
  }
}
