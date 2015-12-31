<?php
/**
 * @file
 * Contains \Drupal\panelizer\PanelizerDefaultPanelsStorage
 */

namespace Drupal\panelizer;

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
   * Constructs a PanelsStorage.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function load($id) {
    // TODO: Implement load() method.
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