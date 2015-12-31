<?php
/**
 * @file
 * Contains \Drupal\panelizer\PanelizerInterface
 */

namespace Drupal\panelizer;


use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Interface for the Panelizer service.
 */
interface PanelizerInterface {

  /**
   * Gets the Panels display for a given entity and view mode.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param string $view_mode
   *   The entity view mode.
   * @param \Drupal\Core\Entity\Display\EntityViewDisplayInterface|NULL $display
   *   If the caller already has the correct display, it can optionally be
   *   passed in here so the Panelizer service doesn't have to look it up;
   *   otherwise, this argument can bo omitted.
   *
   * @return \Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant
   */
  public function getPanelsDisplay(EntityInterface $entity, $view_mode, EntityViewDisplayInterface $display = NULL);

}