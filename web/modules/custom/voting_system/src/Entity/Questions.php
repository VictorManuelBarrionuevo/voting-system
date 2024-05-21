<?php

declare(strict_types=1);

namespace Drupal\voting_system\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\user\EntityOwnerTrait;
use Drupal\voting_system\QuestionsInterface;

/**
 * Defines the questions entity class.
 *
 * @ContentEntityType(
 *   id = "voting_system_questions",
 *   label = @Translation("Questions"),
 *   label_collection = @Translation("Questionss"),
 *   label_singular = @Translation("Questions"),
 *   label_plural = @Translation("Questionss"),
 *   label_count = @PluralTranslation(
 *     singular = "@count Questionss",
 *     plural = "@count Questionss",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\voting_system\QuestionsListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "access" = "Drupal\voting_system\QuestionsAccessControlHandler",
 *     "form" = {
 *       "add" = "Drupal\voting_system\Form\QuestionsForm",
 *       "edit" = "Drupal\voting_system\Form\QuestionsForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *       "delete-multiple-confirm" = "Drupal\Core\Entity\Form\DeleteMultipleForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "voting_system_questions",
 *   admin_permission = "administer voting_system_questions",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "owner" = "uid",
 *   },
 *   links = {
 *     "collection" = "/admin/content/questions",
 *     "add-form" = "/questions/add",
 *     "canonical" = "/questions/{voting_system_questions}",
 *     "edit-form" = "/questions/{voting_system_questions}/edit",
 *     "delete-form" = "/questions/{voting_system_questions}/delete",
 *     "delete-multiple-form" = "/admin/content/questions/delete-multiple",
 *   },
 *   field_ui_base_route = "entity.voting_system_questions.settings",
 * )
 */
final class Questions extends ContentEntityBase implements QuestionsInterface
{
  use EntityChangedTrait;
  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage): void
  {
    parent::preSave($storage);
    if (!$this->getOwnerId()) {
      // If no owner has been set explicitly, make the anonymous user the owner.
      $this->setOwnerId(0);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array
  {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['label'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setRequired(true)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', true)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('view', true)
      ->addConstraint('UniqueField');

    $fields['description'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Question'))
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', true)
      ->setDisplayOptions('view', [
        'type' => 'text_default',
        'label' => 'above',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('view', true);

    $fields['answer'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Answer'))
      ->setSetting('target_type', 'voting_system_answer')
      ->setTranslatable(false)
      ->setRevisionable(true)
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('view', [
        'label' => 'visible',
        'type' => 'list_default',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 0,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
        ],
      ])
      ->setDisplayConfigurable('view', true)
      ->setDisplayConfigurable('form', true);

    $fields['vote'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Vote'))
      ->setSetting('target_type', 'voting_system_vote')
      ->setTranslatable(false)
      ->setRevisionable(true)
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('view', [
        'label' => 'visible',
        'type' => 'list_default',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 0,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
        ],
      ])
      ->setDisplayConfigurable('view', true)
      ->setDisplayConfigurable('form', true);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Author'))
      ->setSetting('target_type', 'user')
      ->setDefaultValueCallback(self::class . '::getDefaultEntityOwner')
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => 60,
          'placeholder' => '',
        ],
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', true)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('view', true);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Status'))
      ->setDefaultValue(true)
      ->setSetting('on_label', 'Enabled')
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => false,
        ],
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', true)
      ->setDisplayOptions('view', [
        'type' => 'boolean',
        'label' => 'above',
        'weight' => 0,
        'settings' => [
          'format' => 'enabled-disabled',
        ],
      ])
      ->setDisplayConfigurable('view', true);

    $fields['show_total_votes'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Show total votes'))
      ->setDefaultValue(true)
      ->setSetting('on_label', 'Show total votes')
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => false,
        ],
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', true)
      ->setDisplayOptions('view', [
        'type' => 'boolean',
        'label' => 'above',
        'weight' => 0,
        'settings' => [
          'format' => 'enabled-disabled',
        ],
      ])
      ->setDisplayConfigurable('view', true);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Authored on'))
      ->setDescription(t('The time that the questions was created.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'timestamp',
        'weight' => 20,
      ])
      ->setDisplayConfigurable('form', true)
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
        'weight' => 20,
      ])
      ->setDisplayConfigurable('view', true);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the questions was last edited.'));

    return $fields;
  }
}
