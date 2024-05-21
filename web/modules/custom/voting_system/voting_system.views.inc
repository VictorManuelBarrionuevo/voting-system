<?php

use Drupal\views\Views;

/**
 * Implements hook_views_data_alter().
 */
function voting_system_views_data_alter(array &$data): void
{
    $data['voting_system_questions']['total_votes']['field'] = [
        'title' => t('Total Votes'),
        'help' => t('Custom Total Votes field.'),
        'id' => 'voting_system_total_votes',
    ];
    $data['voting_system_questions']['average_votes']['field'] = [
        'title' => t('Average Votes'),
        'help' => t('Custom Average Votes field.'),
        'id' => 'voting_system_average_votes',
    ];
    $data['voting_system_questions']['average_votes_per_question']['field'] = [
        'title' => t('Average Votes per Question'),
        'help' => t('Custom Average Votes per Question field.'),
        'id' => 'voting_system_average_votes_per_question',
    ];
}
