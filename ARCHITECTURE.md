

# Process flow

### Create a project

From `/admin/content`

[Add Content] -> `/node/add` "Add Content"

[Project] -> `/node/add/project` "Create Project"

[Save] -> `/node/{node}` "Project Label"

### Add metric to project - from project page

* Action button is shown on "project" page view via "Admin actions" block

[Add Metric] -> `/node/{node}/metric/add` "Choose metric type" (add_unknown_metric_to_project)

or

### Add metric to project - from project page

[Add {Ping} Metric] -> `/node/{node}/metric/add/{metric_type}` "" (add_known_metric_to_project)
- `MetricForm` `entity_form` is shown
- Target is autofilled
- Metric Type is auto-filled

[Save] to create and refresh the new metric definition


or
### Add metric to project - from the top content admin, not in context.

From `/admin/content/metrics` "Metrics management"
[Add some sort of metric] -> `/metric/add` `entity.metric.add_page` "Add Platform.sh project Metric"

* Behaviour here (select from available metric types) is provided by core, and published due to annotation @links on the Metric class.
* the controller is ...
* `Drupal\Core\Entity\Controller\EntityController::addPage()`
* as there is no entity type defined, so it returns an `entity_add_list` instead.

Choose metric type: [{metric_type}] -> `metric/add/{metric_type}` "Add {Metric Type}"
- `MetricForm` `entity_form` is shown
- Metric Type is auto-filled
- Target is chosen
[Save] to create and refresh the new metric definition
