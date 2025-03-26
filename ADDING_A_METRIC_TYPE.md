# Adding a metric type

A metric is a data entity that reflects the state of some sort of measurable observation.
From the simplest "Does the project respond to a ping?" to a more detailed "What recommendations does the Lighthouse
performance audit recommend?"

This module is set up with the expectations that many more metric definitions will be added over time, as new questions
need to be asked.

To define a new metric:

Refer to `entity_api.php` `Defining an entity type`

## Create the class

Create a class that inherits from `Drupal\platformsh_project\Entity\Metric`.
This should go into `{projectname}/src/Entity/theMetric.php`

To publish this entity as being available for use,
It seems that annotations are tricky, so alternatively
add the metadata for this class in the platformsh_project_entity_bundle_info()

```php
/**
 */
class PingMetric extends Metric {

  public function refresh() {
    $this->set('data', 'pinged ' . date("Y-m-d H:i:s"))
  ->save();
  }

}
```

The only required method is `refresh()`. This is expected to do whatever it takes to execute its audit, and update
the `Metric` Content entity with the latest value(s), and save it.

## Publish the metric type to Drupal

> There should be a better way for this to happen,
> We should be able to enumerate the class members, but ...

### Publish the existence of the new metric bundle in the install yamls

`platformsh_project/config/install/platformsh_project.metric_type.ping.yml`

```yaml
status: true
id: "ping"
label: "Ping"
```

### Attach the custom class to the new entity type Metric bundle.

Edit `platformsh_project/platformsh_project.module:platformsh_project_entity_bundle_info_alter()`.

Extend the list of `Metric` bundle definitions to point to the new class.

```php
  if (isset($bundles['metric']['ping'])) {
    $bundles['metric']['ping']['class'] = PingMetric::class;
  }
```

### Publish the entity info

In HOOK_entity_bundle_info(), add information about this bundle and its class.

```php
function platformsh_project_entity_bundle_info(): array {
...
  $bundles['metric']['note'] = [
    'label' => t('Note'),
    'description' => t("An arbitrary user-added note"),
    'class' => Drupal\platformsh_project\Entity\NoteMetric::class,
  ];
```

### Re-install the module

!? There must be a way to avoid this.

Surely there must be some sort of introspection available underneath all this Symfony garbage.
I should just be able to introduce a new Class of type X, and have the system incorporate that into our structure.

---
EntityTypeManager::getDefinitions() will be able to return all classes of type `Entity` from all modules.
It exposes all the annotations from all the ContentEntityType classes.
Annotations are NOT merged with parent annotations - need to be repeated for all sub-classes!.

Our Metric bundles can be filtered from there, though there is no "get all Metrics" function.

Anything with `bundle_entity_type' = 'metric_type'` would seem to be ours.
