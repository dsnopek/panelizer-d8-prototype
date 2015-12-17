<?php

/**
 * @file
 * Contains \Drupal\panelizer\Plugin\Field\FieldType\PanelizerFieldType.
 */

namespace Drupal\panelizer\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\MapDataDefinition;

/**
 * Plugin implementation of the 'panelizer' field type.
 *
 * @FieldType(
 *   id = "panelizer",
 *   label = @Translation("Panelizer"),
 *   description = @Translation("Panelizer"),
 *   default_widget = "panelizer",
 *   default_formatter = "panelizer"
 * )
 */
class PanelizerFieldType extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    // Prevent early t() calls by using the TranslatableMarkup.
    $properties['default'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Default name'))
      ->setSetting('case_sensitive', FALSE)
      ->setRequired(FALSE);
    $properties['panels_display'] = MapDataDefinition::create('map')
      ->setLabel(new TranslatableMarkup('Panels display'))
      ->setRequired(TRUE);

    return $properties;
  }

  /**
   * @inheritDoc
   */
  public static function mainPropertyName() {
    return 'panels_display';
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = [
      'columns' => [
        'default' => [
          'type' => 'varchar',
          'length' => '255',
          'binary' => FALSE,
        ],
        'panels_display' => [
          'type' => 'blob',
          'size' => 'normal',
          'serialize' => TRUE,
        ],
      ],
      'indexes' => [
        'default' => ['default'],
      ]
    ];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    /** @var \Drupal\panels\PanelsDisplayManagerInterface $panels_manager */
    $panels_manager = \Drupal::service('panels.display_manager');
    $sample_display = $panels_manager->createDisplay();

    $values['default'] = NULL;
    $values['panels_display'] = $panels_manager->exportDisplay($sample_display);
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('panels_display')->getValue();
    return empty($value);
  }

}
