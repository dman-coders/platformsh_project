<?php

namespace Drupal\platformsh_project\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\platformsh_project\Entity\Metric;

/**
 * Plugin implementation of the 'status_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "status_formatter",
 *   label = @Translation("Status Description"),
 *   field_types = {
 *     "list_string"
 *   }
 * )
 */
class StatusFormatter extends FormatterBase {


  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $value = $item->value;
      $description = Metric::REQUIREMENT_DICTIONARY[$value] ?? $value;

      $elements[$delta] = [
        '#markup' => $description,
        '#cache' => [
          'contexts' => ['languages'],
        ],
      ];
    }

    return $elements;
  }

}
