<?php

namespace Drupal\platformsh_project\Entity;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\node\Entity\Node;
use Drupal\platformsh_api\ApiService;
use Platformsh\Client\Model\ApiResourceBase;
use Platformsh\Client\PlatformClient;

/**
 * Defines the generic resource that may be returned by an API lookup.
 *
 * @see ApiResourceBase
 */
abstract class ApiResource extends Node {

  use MessengerTrait;

  // Static properties that each subclass should define to say what is special
  // to it. Basically reflects the content data model that we care about.

  /**
   * Fields that get copied directly from the remote object into local field\n   * storage as-is.
   *
   * @var string[]
   */
  protected array $fieldKeys = [];

  /**
   * Fields that are GUID references, and need to be set to refer to other\n   * entities.
   *
   * This maps a local object type (like 'User')
   * against the remote field name (like `owner`)
   * to state the rule that:
   * the value of the 'owner' field
   * becomes a reference to a "User" object.
   *
   * @var string[]
   */
  protected array $referenceKeys = [];

  /**
   * What is the key that should be used as the title of this object?
   *
   * @var string
   */
  protected string $titleKey = 'title';

  /**
   * The API service.
   *
   * @var \Drupal\platformsh_api\ApiService
   */
  protected ApiService $apiService;

  /**
   * The Platform.sh API client.
   *
   * @var \Platformsh\Client\PlatformClient
   */
  protected PlatformClient $apiClient;

  /**
   * The remote entity ID.
   *
   * @var string
   */
  protected string $remoteEntityID;

  /**
   * Fetch the actual data using the PHP API.
   *
   * This is expected to be overridden by the subclass to match the
   * \Platformsh\Client methods.
   *
   * @param string $remoteEntityID
   *   The remote entity ID.
   *
   * @return \Platformsh\Client\Model\ApiResourceBase|false
   *   The API resource or FALSE on failure.
   *
   * @see ApiResourceBase::get
   */
  abstract public function getResource($remoteEntityID): bool|ApiResourceBase;

  /**
   * Add key mapping logic specific to the content types.
   *
   * For when the attribute names in the returned object
   * don't directly match the field names in the entity.
   * This is expected to be overridden by the subclass if extra
   * if/then logic is needed.
   *
   * @param array $rawData
   *   The raw data from the API.
   *
   * @return array
   *   The altered data.
   */
  protected function alterKeys(array $rawData): array {
    return $rawData;
  }

  /**
   * Localised extra data processing to run during refreshFromAPI.
   *
   * @param \Platformsh\Client\Model\ApiResourceBase $resource
   *   The API resource.
   *
   * @return array
   *   Array of updated fields.
   */
  protected function alterData($resource): array {
    return [];
  }

  /**
   * Get the Platform.sh API client.
   *
   * @return \Platformsh\Client\PlatformClient
   *   The API client.
   */
  protected function getApiClient(): PlatformClient {
    $this->apiService = \Drupal::service('platformsh_api.fetcher');
    $this->apiClient = $this->apiService->getApiClient();
    return $this->apiClient;
  }

  /**
   * Generic resource fetcher.
   *
   * This will update and save the entity data directly,
   * if any change is required.
   *
   * Should be able to fetch any model from the API.
   * Anything that is common to most models can be implemented here.
   * Anything that is unique to a model can be in its own
   * corresponding class.
   *
   * @return bool
   *   TRUE on success, FALSE on failure.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function refreshFromApi(): bool {
    // Everything hinges on the field_id, which must be set by now.
    $remoteEntityID = $this->get('field_id')->value;
    if (empty($remoteEntityID)) {
      $this->messenger()->addError("No valid remote ID");
      return FALSE;
    }

    /** @var \Platformsh\Client\Model\ApiResourceBase $resource
     *   Either a project or a user or whatever.
     */
    try {
      // Calling the API may fail for many reasons. Stay paranoid.
      // Network timeout is also possible, and I can't seem to catch it.
      // Avoid infinite hanging on CLI if I can,
      // by enforcing a max execution time.
      // This is fatal, but better than infinite hang.
      $max_execution_time = ini_get('max_execution_time');
      ini_set('max_execution_time', 10);
      \Drupal::logger('platformsh_project')
        ->info(sprintf("Making a request to the API for resource `%s`. (May timeout if network issues)", $remoteEntityID));
      $resource = $this->getResource($remoteEntityID);
      ini_set('max_execution_time', $max_execution_time);

      // The API may return without error, but still not have data
      // - if project is invalid.
      if (empty($resource)) {
        $this->messenger()
          ->addError("API call returned empty. Probably an invalid entity ID. Update failed.");
        return FALSE;
      }
    }
    catch (\Exception $e) {
      $this->messenger()->addError("API call failed: " . $e->getMessage());
      return FALSE;
    }

    $viable_fields = $this->getFields();
    $raw_dump = $resource->getData();
    // Recording whether a change actually happened is tedious,
    // but it will keep history much cleaner
    // as we can skip making updates with no changes.
    // Keep a detailed changelog in $updated.
    $updated = [];

    // Excise the links for brevity, and log the diagnostics.
    unset($raw_dump['_links']);
    $json_dump = json_encode($raw_dump, JSON_PRETTY_PRINT);
    $this->messenger()->addStatus($json_dump);

    try {
      if ($this->getTitle() != $resource->getProperty($this->titleKey)) {
        $this->setTitle($resource->getProperty($this->titleKey));
        $updated['title'] = TRUE;
      }
    }
    catch (\Exception $e) {
      // This should never happen, but it did, so...
      $this->messenger()
        ->addError("Resource doesn't have a " . $this->titleKey . " property");
      $this->messenger()->addError(print_r($resource->getPropertyNames(), 1));
    }

    // Store the raw data for review.
    if (in_array('field_data', array_keys($viable_fields))) {
      if ($this->get('field_data') != $json_dump) {
        $this->set('field_data', $json_dump);
        $updated['field_data'] = TRUE;
      }
    }

    // There may be special cases that the class needs to deal with.
    $raw_dump = $this->alterKeys($raw_dump);

    // Now set the values we extracted.
    // fieldKeys and referenceKeys are the field mapping rules
    // for converting between the object from the API and the local storage
    // model.
    foreach ($this->fieldKeys as $key_name) {
      $field_name = 'field_' . $key_name;
      if (
        isset($raw_dump[$key_name])
        &&
        in_array($field_name, array_keys($viable_fields))
        &&
        ($this->get($field_name) != $resource->getData()[$key_name])
      ) {
        $this->set($field_name, $resource->getData()[$key_name]);
        $updated[$field_name] = TRUE;
      }
      else {
        // Field miss-match. Either our the expected data did not come back,
        // or our content type doesn't have a place to store it.
        // This may happen as API and content model evolves.
        $this->messenger()
          ->addWarning("Missing field. Expected field $key_name was not found in the returned data.");
      }
    }

    $updated += $this->alterData($resource);
    $updated += $this->autocreateTargetEntities($raw_dump);

    // $node->set('field_' . 'updated_at' , $response->getData()['updated_at']);
    // Take care, as this action may be called on hook_entity_presave.\n    // Avoid a loop.
    if (!empty($updated) && !$this->isNew()) {
      try {
        $this->save();
      }
      catch (EntityStorageException $e) {
        $this->messenger()->addError("Failed to save node " . $e->getMessage());
      }
    }
    else {
      $this->messenger()->addStatus("No extra save necessary");
    }
    return TRUE;
  }

  /**
   * For each external entity that a Project refers to, ensure the named\n   * target exists.
   *
   * We have a list of `referenceKeys`, eg 'owner'.
   * If this resources data contains a value for `owner`, this will be a GUID.
   * We need to see if we already recognise that GUID, and if so, link to it.
   * If not, create a placeholder for it, and link to that.
   *
   * @param array $rawData
   *   The raw data from the API.
   *
   * @return array
   *   A list of what, if anything, was updated.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function autocreateTargetEntities(array $rawData): array {
    $updated = [];
    // References need extra help.
    foreach ($this->referenceKeys as $key_type => $key_name) {
      /** @var \Drupal\Core\Field\FieldItemListInterface $value */
      $target_guid = $rawData[$key_name];
      if (empty($target_guid)) {
        continue;
      }

      // Fetch or create the target first.
      $target = $this->apiService::getEntityById($target_guid);

      if (empty($target)) {
        // Attempt auto create.
        $entityTypeId = 'node';
        $targetData = [
          // it's aliased as 'type'. yay.
          'bundle' => $key_type,
          'type' => $key_type,
          'title' => $target_guid,
          'field_id' => $target_guid,
        ];
        $target = $this->autocreateTargetEntity($entityTypeId, $targetData);
      }

      if (!empty($target)) {
        $target_info = ['target_id' => $target->id()];
        if ($this->get('field_' . $key_name) != $target->id()) {
          $updated['field_' . $key_name] = TRUE;
          // @todo check this logic
        }
        $this->set('field_' . $key_name, $target_info);
      }
      else {
        throw new \InvalidArgumentException("Could not find or auto create target entity " . $target_guid->getString());
      }
    }
    return $updated;
  }

  /**
   * Create a new node entity of the requested type.
   *
   * @param string $entityTypeId
   *   The entity type ID.
   * @param array $values
   *   The values for the new entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The created entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function autocreateTargetEntity(string $entityTypeId, array $values) {
    $this->messenger()
      ->addStatus("Auto-creating an {$values['bundle']} called {$values['field_id']}");
    $entity = \Drupal::entityTypeManager()
      ->getStorage($entityTypeId)
      ->create($values);
    $entity->save();
    return $entity;
  }

}
