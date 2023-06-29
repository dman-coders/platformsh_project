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

### References

This does a lot of setup at install time via `/config/install`, such as defining new views and VBO setup. Doing this as a snapshot in yaml is a bit fragile, but programatically defining what needs to be done in code would be even worse.
Same goes for block placement. It should have been easy to drop a `block.block.{id}.yml` into `install` to say "put my block on this type of page" *but* it turns out I'd need to hard-code the theme ID - which makes no sense in a contrib module.
So the block config gets set during hook_install() thanks to [a nice howto article](https://davidjguru.github.io/blog/drupal-techniques-placing-a-block-by-code) by David Rodriguez
