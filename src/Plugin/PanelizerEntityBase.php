<?php

/**
 * @file
 * Contains \Drupal\panelizer\Plugin\PanelizerEntityBase.
 */

namespace Drupal\panelizer\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Panels\PanelsDisplayManager;
use Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for Panelizer entity plugins.
 */
abstract class PanelizerEntityBase extends PluginBase implements PanelizerEntityInterface, ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Panels\PanelsDisplayManager
   */
  protected $panelsManager;

  /**
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Panels\PanelsDisplayManager $panels_manager
   *   The Panels display manager.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, PanelsDisplayManager $panels_manager, EntityFieldManagerInterface $entity_field_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->panelsManager = $panels_manager;
    $this->entityFieldManager = $entity_field_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('panels.display_manager'),
      $container->get('entity_field.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultDisplay(EntityViewDisplayInterface $display, $bundle, $view_mode) {
    $panels_display = $this->panelsManager->createDisplay();

    $panels_display->setLayout('onecol');
    // @todo: For now we always use the IPE, but we should support not using the ipe.
    $panels_display->setBuilder('ipe');

    // Add all the visible fields to the Panel.
    $entity_type_id = $this->getPluginId();
    /**
     * @var string $field_name
     * @var \Drupal\Core\Field\FieldDefinitionInterface $field_definition
     */
    foreach ($this->entityFieldManager->getFieldDefinitions($entity_type_id, $bundle) as $field_name => $field_definition) {
      // Skip the Panelizer field.
      if ($field_definition->getType() == 'panelizer') {
        continue;
      }

      if ($component = $display->getComponent($field_name)) {
        $weight = $component['weight'];
        unset($component['weight']);

        $panels_display->addBlock([
          'id' => 'entity_field:' . $entity_type_id . ':' . $field_name,
          'label' => $field_definition->getLabel(),
          'provider' => 'ctools_block',
          'label_display' => '0',
          'formatter' => $component,
          'context_mapping' => [
            'entity' => '@panelizer.entity_context:' . $entity_type_id,
          ],
          'region' => 'middle',
          'weight' => $weight,
        ]);
      }
    }

    return $panels_display;
  }

  /**
   * {@inheritdoc}
   */
  public function alterBuild(array &$build, EntityInterface $entity, PanelsDisplayVariant $display, $view_mode) {
    // By default, do nothing!
  }

}
