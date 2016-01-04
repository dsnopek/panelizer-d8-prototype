<?php

/**
 * @file
 * Contains \Drupal\panelizer\PanelizerFieldPanelsStorage
 */

namespace Drupal\panelizer;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant;
use Drupal\panels\Storage\PanelsStorageInterface;

/**
 * Panels storage service that stores Panels displays in the Panelizer field.
 */
class PanelizerFieldPanelsStorage implements PanelsStorageInterface {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\panelizer\PanelizerInterface
   */
  protected $panelizer;

  /**
   * Constructs a PanelsStorage.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, PanelizerInterface $panelizer) {
    $this->entityTypeManager = $entity_type_manager;
    $this->panelizer = $panelizer;
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