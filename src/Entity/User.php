<?php

namespace Drupal\platformsh_project\Entity;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\node\Entity\Node;
use Drupal\platformsh_project\ApiService;
use Platformsh\Client\Model\ApiResourceBase;
use Platformsh\Client\PlatformClient;

/**
 * Defines the Project node entity.
 */
class User extends ApiResource {


  // Custom behavior here.

  protected array $field_keys = ['country', 'first_name', 'last_name'];

  protected array $reference_keys = [];

  protected string $title_key = 'company';

  /**
   * @param $remoteEntityID
   *
   * @return false|\Platformsh\Client\Model\user
   */
  public function getResource($remoteEntityID): bool|ApiResourceBase {
    return $this->getApiClient()->getUser($remoteEntityID);
  }

}
