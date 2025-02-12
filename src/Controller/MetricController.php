<?php

namespace Drupal\platformsh_project\Controller;

use Drupal;
use Drupal\Core\Entity\Controller\EntityController;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
   * Redirects to the add form if there's only one bundle available.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   *
   * @return RedirectResponse|array
   *   If there's only one available bundle, a redirect response.
   *   Otherwise, a render array with the add links for each bundle.
   */
  public function addPage($entity_type_id) {
    // Generic entity parameters setup copied from parent method.
    // We will always be $bundle_entity_type_id=metric
    // and won't have to worry about cases where no bundle exists.
    $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);
    $bundles = $this->entityTypeBundleInfo->getBundleInfo($entity_type_id);
    $bundle_key = $entity_type->getKey('bundle');
    $bundle_entity_type_id = $entity_type->getBundleEntityType();

    // Find the current target project - what was the `project` ID in
    //  path: '/node/{project}/metric/add'.
    /** @var RouteMatchInterface $route_match */
    $route_match = Drupal::routeMatch();
    /** @var NodeInterface $node */
    $node = $route_match->getParameter('project');
    if (!$node instanceof NodeInterface) {
      // Problem.
    }
    $project_id = $node->id();

    $build = [
      '#theme' => 'entity_add_list',
      '#bundles' => [],
    ];
    if ($bundle_entity_type_id) {
      $bundle_argument = $bundle_entity_type_id;
      $bundle_entity_type = $this->entityTypeManager->getDefinition($bundle_entity_type_id);
      $bundle_entity_type_label = $bundle_entity_type->getSingularLabel();
      $build['#cache']['tags'] = $bundle_entity_type->getListCacheTags();

      // Build the message shown when there are no bundles. (redundant)
      $link_text = $this->t('Add a new @entity_type.', ['@entity_type' => $bundle_entity_type_label]);
      $link_route_name = 'entity.' . $bundle_entity_type->id() . '.add_form';
      $build['#add_bundle_message'] = $this->t('There is no @entity_type yet. @add_link', [
        '@entity_type' => $bundle_entity_type_label,
        '@add_link' => Link::createFromRoute($link_text, $link_route_name)
          ->toString(),
      ]);
      // Filter out the bundles the user doesn't have access to.  (redundant)
      $access_control_handler = $this->entityTypeManager->getAccessControlHandler($entity_type_id);
      foreach ($bundles as $bundle_name => $bundle_info) {
        $access = $access_control_handler->createAccess($bundle_name, NULL, [], TRUE);
        if (!$access->isAllowed()) {
          unset($bundles[$bundle_name]);
        }
        $this->renderer->addCacheableDependency($build, $access);
      }
      // Add descriptions from the bundle entities.
      $bundles = $this->loadBundleDescriptions($bundles, $bundle_entity_type);
    }
    else {
      $bundle_argument = $bundle_key;
    }

    // Our own alternative to 'entity.' . $entity_type_id . '.add_form'
    // is '/node/{project}/metric/add/{metric_type}'.
    $form_route_name = 'metric.add_known_metric_to_project';

    // Redirect if there's only one bundle available. (redundant)
    if (count($bundles) == 1) {
      $bundle_names = array_keys($bundles);
      $bundle_name = reset($bundle_names);
      return $this->redirect($form_route_name, [
        'project' => $project_id,
        $bundle_argument => $bundle_name,
      ]);
    }
    // Prepare the #bundles array for the template.
    // Create our links that embed the project ID in the URL as well.
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

}
