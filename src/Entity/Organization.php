<?php

namespace Drupal\platformsh_project\Entity;

/**
 * Defines the Organization node entity.
 */
class Organization extends ApiResource {

  protected array $field_keys = [];

  protected array $reference_keys = [];

  protected string $title_key = 'name';

  /**
   * @param $remoteEntityID
   *
   * @return false|\Platformsh\Client\Model\Organization
   */
  public function getResource($remoteEntityID): bool {
    return FALSE;
    // Platformsh\Client does not yet (2023) have a way to retrieve org info.
    // Not yet available?
    // $this->getApiClient()->getOrganization($remoteEntityID);
  }

}
