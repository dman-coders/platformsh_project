<?php

namespace Drupal\platformsh_project;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface defining a entity type.
 */
interface MetricInterface extends ContentEntityInterface, EntityChangedInterface {

}
