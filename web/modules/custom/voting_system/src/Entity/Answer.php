<?php

declare(strict_types=1);

namespace Drupal\voting_system\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\user\EntityOwnerTrait;
use Drupal\voting_system\AnswerInterface;
use Drupal\file\Entity\File;
use Drupal\image\Plugin\Field\FieldType\ImageItem;

/**
 * Defines the answer entity class.
 *
 * @ContentEntityType(
 *   id = "voting_system_answer",
 *   label = @Translation("Answer"),
 *   label_collection = @Translation("Answers"),
 *   label_singular = @Translation("Answer"),
 *   label_plural = @Translation("Answers"),
 *   label_count = @PluralTranslation(
 *     singular = "@count Answers",
 *     plural = "@count Answers",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\voting_system\AnswerListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "access" = "Drupal\voting_system\AnswerAccessControlHandler",
 *     "form" = {
 *       "add" = "Drupal\voting_system\Form\AnswerForm",
 *       "edit" = "Drupal\voting_system\Form\AnswerForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *       "delete-multiple-confirm" = "Drupal\Core\Entity\Form\DeleteMultipleForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "voting_system_answer",
 *   admin_permission = "administer voting_system_answer",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "owner" = "uid",
 *   },
 *   links = {
 *     "collection" = "/admin/content/answer",
 *     "add-form" = "/answers/add",
 *     "canonical" = "/answers/{voting_system_answer}",
 *     "edit-form" = "/answers/{voting_system_answer}/edit",
 *     "delete-form" = "/answers/{voting_system_answer}/delete",
 *     "delete-multiple-form" = "/admin/content/answer/delete-multiple",
 *   },
 *   field_ui_base_route = "entity.voting_system_answer.settings",
 * )
 */
final class Answer extends ContentEntityBase implements AnswerInterface
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
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', true)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('view', true)
      ->addConstraint('UniqueField');

    $fields['description'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Answer'))
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', true)
      ->setDisplayOptions('view', [
        'type' => 'text_default',
        'label' => 'above',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('view', true);

    $fields['image'] = BaseFieldDefinition::create('file')
    ->setLabel(t('Image'))
    ->setDescription(t('Upload an image or select from the media library.'))
    ->setSettings([
      'file_directory' => 'images/[date:custom:Y]-[date:custom:m]',  // Define the directory where images will be stored
      'file_extensions' => 'png jpg jpeg',  // Limit file extensions
      'max_filesize' => '2 MB',  // Set maximum file size
      'alt_field' => TRUE,  // Enable alt text field
      'title_field' => FALSE,  // Disable title field
      'max_resolution' => '2048x2048',  // Set maximum resolution
    ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'image',
        'weight' => 0,
        'settings' => [
          'image_style' => 'thumbnail',  // Default image style
          'image_link' => 'content',  // Link image to content
        ],
      ])
      ->setDisplayOptions('form', [
        'type' => 'image_image',
        'weight' => 0,
        'settings' => [
          'file_extensions' => 'png jpg jpeg',  // Limit file extensions
          'max_filesize' => '2 MB',  // Set maximum file size
          'alt_field' => TRUE,  // Enable alt text field
          'title_field' => FALSE,  // Disable title field
          'max_resolution' => '2048x2048',  // Set maximum resolution
          'progress_indicator' => 'throbber',  // Set progress indicator
          'preview_image_style' => 'thumbnail',  // Preview style in the form
        ],
      ])
      ->setRequired(TRUE)  // Make the field required if necessary
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

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
        'weight' => 15,
      ])
      ->setDisplayConfigurable('form', true)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'author',
        'weight' => 15,
      ])
      ->setDisplayConfigurable('view', true);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Authored on'))
      ->setDescription(t('The time that the answer was created.'))
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
      ->setDescription(t('The time that the answer was last edited.'));

    return $fields;
  }
}
