# Platform Project

Provides the entity type definition of a `Project` and publishes a few actions relevant to working with them.

## Requirements

* drupal/admin_actions , drupal/vbo
  are used to provide the UI actions blocks seen on 'Project' and 'Metric' pages.

## Utility

### drush extension

    drush platformsh:create-content {PROJECT_ID}


## Development

### Testing

There is a drush utility to support testing.
This will add a couple of known projects to the site.

    drush platformsh:create-test-content

### Re-installation

To test if things are re-installing correctly, and removing traces of themselves,
it's handy to provide a full remove & replace.
The platformsh_project provides a hook_uninstall that should remove all of its entities and content.

But to *manually* delete our stuff.

    drush entity:delete metric; \
    drush pm:uninstall -y platformsh_project; \
    drush pm:enable -y platformsh_project;
