<?php

/**
 * @file
 * Contains \Drupal\panelizer\Plugin\PanelizerEntityBase.
 */

namespace Drupal\panelizer\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Panels\PanelsDisplayManager;
use Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant;

/**
 * Base class for Panelizer entity plugins.
 */
abstract class PanelizerEntityBase extends PluginBase implements PanelizerEntityInterface {

  /**
   * @var \Drupal\Panels\PanelsDisplayManager
   */
  protected $panelsManager;

  /**
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Panels\PanelsDisplayManager $panels_manager
   *   The Panels display manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, PanelsDisplayManager $panels_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->panelsManager = $panels_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultDisplay(EntityViewDisplayInterface $display, $bundle, $view_mode) {
    $panels_display = $this->panelsManager->createDisplay();

    // For now, we always use the IPE.
    // @todo: We should support editing without the IPE!
    $panels_display->setBuilder('ipe');

    // @todo: we should handle fields here, since that part'll probably be standard.

    return $panels_display;
  }

  /**
   * {@inheritdoc}
   */
  public function alterBuild(array &$build, EntityInterface $entity, PanelsDisplayVariant $display, $view_mode) {
    // By default, do nothing!
  }

}
