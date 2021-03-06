<?php

/**
 * @file
 * Contains \Drupal\panelizer\PanelizerFieldPanelsStorage
 */

namespace Drupal\panelizer\Plugin\PanelsStorage;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\panelizer\PanelizerInterface;
use Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant;
use Drupal\panels\Storage\PanelsStorageBase;
use Drupal\panels\Storage\PanelsStorageInterface;

/**
 * Panels storage service that stores Panels displays in the Panelizer field.
 *
 * @PanelsStorage("panelizer_field")
 */
class PanelizerFieldPanelsStorage extends PanelsStorageBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\panelizer\PanelizerInterface
   */
  protected $panelizer;

  /**
   * Constructs a PanelizerDefaultPanelsStorage.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\panelizer\PanelizerInterface $panelizer
   *   The Panelizer service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, PanelizerInterface $panelizer) {
    $this->entityTypeManager = $entity_type_manager;
    $this->panelizer = $panelizer;
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('panelizer')
    );
  }

  /**
   * Gets the underlying entity from storage.
   *
   * @param $id
   *   The storage service id.
   *
   * @return \Drupal\Core\Entity\EntityInterface|NULL
   */
  protected function loadEntity($id) {
    list ($entity_type, $id, , $revision_id) = explode(':', $id);

    $storage = $this->entityTypeManager->getStorage($entity_type);
    if ($revision_id) {
      $entity = $storage->loadRevision($revision_id);
    }
    else {
      $entity = $storage->load($id);
    }

    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function load($id) {
    if ($entity = $this->loadEntity($id)) {
      list (,,$view_mode) = explode(':', $id);
      return $this->panelizer->getPanelsDisplay($entity, $view_mode);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(PanelsDisplayVariant $panels_display) {
    // TODO: Implement save() method.
  }

  /**
   * {@inheritdoc}
   */
  public function access($id, $op, AccountInterface $account) {
    // TODO: Implement access() method.
  }

}