<?php

/**
 * @file
 * Contains \Drupal\panelizer\PanelizerDefaultPanelsStorage
 */

namespace Drupal\panelizer\Plugin\PanelsStorage;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\panelizer\PanelizerInterface;
use Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant;
use Drupal\panels\Storage\PanelsStorageBase;
use Drupal\panels\Storage\PanelsStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Panels storage service that stores Panels displays in Panelizer defaults.
 *
 * @PanelsStorage("panelizer_default")
 */
class PanelizerDefaultPanelsStorage extends PanelsStorageBase implements ContainerFactoryPluginInterface {

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
    list ($entity_type_id, $bundle, $view_mode, $name) = explode(':', $id);
    if ($panels_display = $this->panelizer->getDefaultPanelsDisplay($name, $entity_type_id, $bundle, $view_mode)) {
      if ($op == 'read' || $this->panelizer->hasDefaultPermission('change content', $entity_type_id, $bundle, $view_mode, $name, $account)) {
        return AccessResult::allowed();
      }
    }

    return AccessResult::forbidden();
  }

}