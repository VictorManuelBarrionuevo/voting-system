<?php

declare(strict_types=1);

namespace Drupal\voting_system;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining an answer entity type.
 */
interface AnswerInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
