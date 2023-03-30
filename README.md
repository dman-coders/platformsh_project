# Platform Project

Provides the entity type definition of a `Project` and publishes a few actions relevant to working with them.

## Development

### Re-installation

To test if things are re-installing correctly, and removng traces of themselves,
it's handy to provide a full remove &replace.
But existing content may get jammed up ion the works.
Manually delete our stuff.

    drush entity:delete metric; \
    drush pm:uninstall -y platformsh_project; \
    drush pm:enable -y platformsh_project;
