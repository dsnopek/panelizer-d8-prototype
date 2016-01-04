<?php

/**
 * @file
 * Contains \Drupal\panelizer\PanelizerDefaultPanelsStorage
 */

namespace Drupal\panelizer;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant;
use Drupal\panels\Storage\PanelsStorageInterface;

/**
 * Panels storage service that stores Panels displays in Panelizer defaults.
 */
class PanelizerDefaultPanelsStorage implements PanelsStorageInterface {

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
   * {@inheritdoc}
   */
  public function load($id) {
    list ($entity_type_id, $bundle, $view_mode, $name) = explode(':', $id);
    return $this->panelizer->getDefaultPanelsDisplay($name, $entity_type_id, $bundle, $view_mode);
  }

  /**
   * {@inheritdoc}
   */
  public function save(PanelsDisplayVariant $panels_display) {
    $id = $panels_display->getStorageId();
    list ($entity_type_id, $bundle, $view_mode, $name) = explode(':', $id);
    return $this->panelizer->setDefaultPanelsDisplay($name, $entity_type_id, $bundle, $view_mode, $panels_display);
  }

  /**
   * {@inheritdoc}
   */
  public function access($id, $op, AccountInterface $account) {
    // @todo: Actually check access!
    return AccessResult::allowed();
  }

}