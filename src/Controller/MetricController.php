<?php

namespace Drupal\platformsh_project\Controller;

use Drupal\Core\Entity\Controller\EntityController;
use Drupal\Core\Link;
use Drupal\node\NodeInterface;

/**
 * Provides the add-page callback for a Metric add form.
 *
 * Can handle the request to "Add Metric" if
 * the type of metric is not given (falls back to a metric type selection list)
 * the project the metric is for is not given.
 *   If defined, it will be auto-filled, if not, it becomes a required field in
 * the UI.
 *
 * It provides:
 * - An override to the add-page callback (the list page where you choose a
 * bundle) to embed the pre-chosen entity ID (the related project) as a
 * parameter to the URL.
 *
 * Natively, when just using the EntityController,
 * a `/metric/add` URL would redirect to the `/metric/add/ping` route.
 * But we need to ensure that a `/node/23/metric/add` page will redirect to a
 * `/node/23/metric/add/ping` route.
 */
class MetricController extends EntityController {

  /**
   * Displays add links for the available bundles.
   *
   * "Add Metric to project"
   *
   * Redirects to the add form if there's only one bundle available.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse|array
   *   If there's only one available bundle, a redirect response.
   *   Otherwise, a render array with the add links for each bundle.
   */
  public function addPage($entity_type_id): \Symfony\Component\HttpFoundation\RedirectResponse|array {
    // Most of the page build is identical to parent "Add X" page.
    // We just need to add a parameter to the path.
    // $entity_type_id is always 'metric' here.
    $build = parent::addPage($entity_type_id);

    // Find the current target project - what was the `project` ID in
    // path: '/node/{project}/metric/add'.
    /** @var \Drupal\Core\Routing\RouteMatchInterface $route_match */
    $route_match = \Drupal::routeMatch();
    /** @var \Drupal\node\NodeInterface $node */
    $node = $route_match->getParameter('project');
    if (!$node instanceof NodeInterface) {
      throw new \Exception('No valid project node was specified. could not load project id:' . $route_match->getParameter('project'));
    }
    $project_id = $node->id();

    // Our own alternative to 'entity.' . $entity_type_id . '.add_form'
    // is '/node/{project}/metric/add/{metric_type}'.
    $form_route_name = 'metric.add_known_metric_to_project';

    // Prepare the #bundles array for the template.
    // Create our links that embed the project ID in the URL as well.
    // Overwrites the parent-provided #bundles array.
    $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);
    $bundles = $this->entityTypeBundleInfo->getBundleInfo($entity_type_id);
    // $bundle_entity_type_id = $entity_type->getBundleEntityType();
    // $bundle_argument = $bundle_entity_type_id;
    // These will always be known, can hardcode them.
    // $bundle_entity_type_id = 'metric_type';
    $bundle_argument = 'metric_type';

    foreach ($bundles as $bundle_name => $bundle_info) {
      $build['#bundles'][$bundle_name] = [
        'label' => $bundle_info['label'],
        'description' => $bundle_info['description'] ?? '',
        'add_link' => Link::createFromRoute($bundle_info['label'], $form_route_name, [
          'project' => $project_id,
          $bundle_argument => $bundle_name,
        ]),
      ];
    }

    return $build;
  }

  public function addPageTitle($project, $metric_type) {
    // These parameters come fully loaded.
    $project_title = $project ? $project->label() : 'project';
    $metric_label = $metric_type->label();

    return $this->t('Add a @metric_type metric to "@project" project', [
      '@metric_type' => $metric_label,
      '@project' => $project_title,
    ]);
  }

}
