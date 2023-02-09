# Platform Project

Provides the entity type definition of a `Project` and publishes a few actions relevant to working with them.

## Internals

* Defines the content model for a 'Project', "Organisation' and 'User' entity.

* Drupal Revisions are used to track changes in project properties over time.

* On first creation of a project (hook presave), the API is queried to fill in any missing data.

* Related Organisation and Owner entites are auto-created as needed

* An action to "refresh from API" is exposed, and can be triggered via bulk operations.
