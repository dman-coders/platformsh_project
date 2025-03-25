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
  protected array $field_keys = [
    # 'plan', # Special case, handled by alterData()
    'default_domain',
    'region',
    'namespace',
  ];

  /**
   * Some fields in the API response are a reference to another entity.
   * This array maps entity type to the remote field name.
   *
   * $key_type => $key_name
   */
  protected array $reference_keys = [
    'user' => 'owner',
  ];

  protected string $title_key = 'title';

  /**
   * Get the Platformsh API project.
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
   * @param $raw_data
   *
   * @return mixed
   */
  protected function alterKeys($raw_data): mixed {
    if (isset($raw_data['owner_info'])) {
      if ($raw_data['owner_info']['type'] == 'organization') {
        // This slightly changes our schema def.
        // The 'owner' is of type 'organization',
        // there is no ref to a 'user',
        // and there is no `organization_id`.
        $this->reference_keys = [
          'organization' => 'owner',
        ];
      }
    }
    return $raw_data;
  }

  /**
   * The subscription plan is a nested value,
   * but I don't want to replicate a whole subscription object.
   * Just extract the value and put it in the field.
   *
   * @param ApiResourceBase  $resource
   *
   * @return mixed
   */
  protected function alterData($resource): mixed {
    $updated = [];
    if (isset($resource->getData()['subscription'])) {
      // Don't support subscription as an entity, just flatten the value dow top the plan name.
      $field_name = 'field_plan';
      if ($this->get($field_name) != $resource->getData()['subscription']) {
        $this->set($field_name, $resource->getData()['subscription']['plan']);
        $updated[$field_name] = TRUE;
      }
    }
    return $updated;
  }
  /**
   *
   */
  public function getUrl(): string {
    return "https://example.com/";
  }

}
