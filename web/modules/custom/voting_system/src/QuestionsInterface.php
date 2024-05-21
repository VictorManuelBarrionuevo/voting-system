<?php

declare(strict_types=1);

namespace Drupal\voting_system;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a questions entity type.
 */
interface QuestionsInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
