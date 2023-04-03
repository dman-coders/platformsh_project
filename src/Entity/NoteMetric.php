<?php


namespace Drupal\platformsh_project\Entity;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * A metric entity that performs no special action,
 * it just exists as a repository for notes entered manually.
 * @ContentEntityType(
 *   id = "NOTE",
 *   label = @Translation("Note"),
 * )
 */
class NoteMetric extends Metric {

  public function label() {
    return "A note";
  }

  public function refresh() {
    $this->set('data', 'Refreshed ' . date("Y-m-d H:i:s"))
      ->save();
  }

}
