<?php

/**
 * @file
 * Contains \Drupal\panelizer\Plugin\PanelizerEntityBase.
 */

namespace Drupal\panelizer\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\panels\PanelsDisplay;

/**
 * Base class for Panelizer entity plugins.
 */
abstract class PanelizerEntityBase extends PluginBase implements PanelizerEntityInterface {

  /**
   * {@inheritdoc}
   */
  public function getDefaultDisplay(EntityViewDisplayInterface $display, $bundle, $view_mode) {
    $panels_display = new PanelsDisplay();
    // @todo: we should handle fields here, since that part'll probably be standard.
    return $panels_display;
  }

  /**
   * {@inheritdoc}
   */
  public function alterBuild(array &$build, EntityInterface $entity, PanelsDisplay $display, $view_mode) {
    // By default, do nothing!
  }

}
