<?php

namespace Drupal\platformsh_project\Entity;

/**
 * Defines the Organization node entity.
 */
class Organization extends ApiResource {

  /**
   * Field keys for this entity.
   *
   * @var array
   */
  protected array $fieldKeys = [];

  /**
   * Reference keys for this entity.
   *
   * @var array
   */
  protected array $referenceKeys = [];

  /**
   * The title key for this entity.
   *
   * @var string
   */
  protected string $titleKey = 'name';

  /**
   * Get the Platform.sh API organization.
   *
   * @param string $remoteEntityID
   *   The remote entity ID.
   *
   * @return bool
   *   Always FALSE as API doesn't support organization retrieval yet.
   */
  public function getResource($remoteEntityID): bool {
    return FALSE;
    // Platformsh\Client does not yet (2023) have a way to retrieve org info.
    // Not yet available?
    // $this->getApiClient()->getOrganization($remoteEntityID);
  }

}
