<?php

namespace Drupal\platformsh_project\Entity;

use Drupal\Core\Entity\Annotation\ContentEntityType;

/**
 * A metric entity that performs no special action,
 * it just exists as a repository for notes entered manually.
 *
 *  Each bundle definition beeds to be declared in the
 *  platformsh_project_entity_bundle_info() also.
 *  Cannot use annotations without things getting snarled up.
 */
class NoteMetric extends Metric {

  /**
   *
   */
  public function label() {
    return "A note";
  }

  /**
   *
   */
  public function refresh() {
    $this->set('data', 'Refreshed ' . date("Y-m-d H:i:s"))
      ->save();
  }

}
