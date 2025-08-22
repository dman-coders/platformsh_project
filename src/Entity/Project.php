<?php

namespace Drupal\platformsh_project\Entity;

use Platformsh\Client\Model\ApiResourceBase;

/**
 * Defines the Project node entity.
 */
class Project extends ApiResource {

  // These array list the mappings between the fields in the
  // API response, and the local content model.
  // It would be good if they match closely.
  /**
   * BUT the API changed schema remotely, so we have to translate sometimes.
   */
  protected array $fieldKeys = [
    // 'plan', # Special case, handled by alterData()
    'default_domain',
    'region',
    'namespace',
  ];

  /**
   * Some fields in the API response are a reference to another entity.
   *
   * This array maps entity type to the remote field name.
   *
   * @var array
   *   $key_type => $key_name.
   */
  protected array $referenceKeys = [
    'user' => 'owner',
  ];

  /**
   * The title key for this entity.
   *
   * @var string
   */
  protected string $titleKey = 'title';

  /**
   * Get the Platformsh API project.
   *
   * @param string $remoteEntityID
   *   The remote entity ID.
   *
   * @return \Platformsh\Client\Model\ApiResourceBase|false
   *   The API resource or FALSE on failure.
   */
  public function getResource($remoteEntityID): bool|ApiResourceBase {
    return $this->getApiClient()->getProject($remoteEntityID);
  }

  /**
   * There is something unexpected in the return data for a Project.
   *
   * If the owner_info:type = organization
   * then the owner ID refers to an organization ID, not a user.
   * Which means the 'owner' is ...
   * https://api.platform.sh/docs/#tag/Project/operation/get-projects
   * OK, `owner` is now deprecated (but still works sometimes)
   *
   * version old:
   * "owner": "c11a1480-894c-4363-897a-52549ea0e286",
   * "organization": "01GSXM6C326HKKNCV8Z0C0Z7WY"
   *
   * version newer:
   * "owner": "94d2a9e5-c20c-45fc-bffd-50f738c13459",
   * # ^- owner is now an org GUID
   * "owner_info": {"type": "organization"},
   * "organization_id": "01FF4NDBVSTHNZSTTWQVPBMMD8" # <- visible org slug
   *
   *
   * I see both 'organization` and `organization_id`
   * coming back from different projects,
   * different regions?
   * different API versions?.
   *
   * @param array $rawData
   *   The raw data from the API.
   *
   * @return array
   *   The altered data.
   */
  protected function alterKeys(array $rawData): array {
    if (isset($rawData['owner_info'])) {
      if ($rawData['owner_info']['type'] == 'organization') {
        // This slightly changes our schema def.
        // The 'owner' is of type 'organization',
        // there is no ref to a 'user',
        // and there is no `organization_id`.
        $this->referenceKeys = [
          'organization' => 'owner',
        ];
      }
    }
    return $rawData;
  }

  /**
   * The subscription plan is a nested value.
   *
   * I don't want to replicate a whole subscription object.
   * Just extract the value and put it in the field.
   *
   * @param \Platformsh\Client\Model\ApiResourceBase $resource
   *   The API resource.
   *
   * @return array
   *   Array of updated fields.
   */
  protected function alterData($resource): array {
    $updated = [];
    if (isset($resource->getData()['subscription'])) {
      // Don't support subscription as an entity, just flatten the value.
      $fieldName = 'field_plan';
      if ($this->get($fieldName) != $resource->getData()['subscription']) {
        $this->set($fieldName, $resource->getData()['subscription']['plan']);
        $updated[$fieldName] = TRUE;
      }
    }
    return $updated;
  }

  /**
   * Get the URL for this project.
   *
   * @return string
   *   The project URL.
   */
  public function getUrl(): string {
    return "https://example.com/";
  }

}
