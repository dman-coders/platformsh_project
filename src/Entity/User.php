<?php

namespace Drupal\platformsh_project\Entity;

use Platformsh\Client\Model\ApiResourceBase;

/**
 * Defines the Project node entity.
 */
class User extends ApiResource {

  /**
   * Custom behavior here.
   */
  protected array $fieldKeys = ['country', 'first_name', 'last_name'];

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
  protected string $titleKey = 'username';

  /**
   * Get the Platform.sh API user.
   *
   * @param string $remoteEntityID
   *   The remote entity ID.
   *
   * @return \\Platformsh\\Client\\Model\\ApiResourceBase|false
   *   The API resource or FALSE on failure.
   */
  public function getResource($remoteEntityID): bool|ApiResourceBase {
    return $this->getApiClient()->getUser($remoteEntityID);
  }

}
