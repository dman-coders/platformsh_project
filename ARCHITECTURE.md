# Process flow

What needs to happen and be tested so that things fit together right.

Can convert this session 'script' into gherkin syntax maybe?

### Create a project

From `/admin/content`

[Add Content] -> `/node/add` "Add Content"

[Project] -> `/node/add/project` "Create Project"
Set Project Name & ID (the rest can be empty)

[Save] -> `/node/{node}` "Project Name"

System will update project info from API on save.

Confirm the project data has now been autofilled.

### Add a metric to project - from the project node page

If the project is known, the metric type still has to be selected. Different metric types are different entity bundles,
so they may present different creation forms.

* Confirm that the Action button is shown on the cannonic "project" page (view via "Admin actions" block & VBO)

[Add Metric] -> `/node/{node}/metric/add` "Choose metric type" (add_unknown_metric_to_project)

Behaviour in this context here is from a custom controller, my override of
`Drupal\Core\Entity\Controller\EntityController` is `MetricController::addPage()` . This passes the known node ID
through to the add metric form.

[Add {Ping} Metric] -> `/node/{node}/metric/add/{metric_type}` "" (add_known_metric_to_project)

- `MetricForm` `entity_form` is shown
- Target is autofilled
- Metric Type is auto-filled

[Save] to create and refresh the new metric definition

or

### Add metric to project - from the top content admin, not in context.

If neither the type nor project is known yet, both have to be selected during metric creation.

From `/admin/content/metrics` "Metrics management"
[Add some sort of metric] -> `/metric/add` `entity.metric.add_page` "Add Platform.sh project Metric"

* Behaviour here (select from available metric types) is normally provided by core, and published due to annotation
  @links on the Metric class.
* the controller is normally ...
* `Drupal\Core\Entity\Controller\EntityController::addPage()`
* as there is no entity type defined, so it returns an `entity_add_list` instead.

Choose metric type: [{metric_type}] -> `metric/add/{metric_type}` "Add {Metric Type}"

- `MetricForm` `entity_form` is shown
- Metric Type is auto-filled
- Target is chosen
  [Save] to create and refresh the new metric definition
