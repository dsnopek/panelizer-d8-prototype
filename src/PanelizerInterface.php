<?php

/**
 * @file
 * Contains \Drupal\panelizer\PanelizerInterface
 */

namespace Drupal\panelizer;


use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant;

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
   * @return \Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant|NULL
   *   The Panels display if panelized; NULL otherwise.
   */
  public function getPanelsDisplay(FieldableEntityInterface $entity, $view_mode, EntityViewDisplayInterface $display = NULL);

  /**
   * Gets the default Panels displays for an entity type, bundle and view mode.
   *
   * @param string $entity_type_id
   *   The entity type id.
   * @param string $bundle
   *   The bundle.
   * @param string $view_mode
   *   The view mode.
   * @param \Drupal\Core\Entity\Display\EntityViewDisplayInterface|NULL $display
   *   If the caller already has the correct display, it can optionally be
   *   passed in here so the Panelizer service doesn't have to look it up;
   *   otherwise, this argument can bo omitted.
   *
   * @return \Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant[]|NULL
   *   An associative array of Panels displays, keyed by the machine name of
   *   the default if panelized; NULL otherwise. All panelized view modes will
   *   have at least one named 'default'.
   */
  public function getDefaultPanelsDisplays($entity_type_id, $bundle, $view_mode, EntityViewDisplayInterface $display = NULL);

  /**
   * Gets one default Panels display for an entity type, bundle and view mode.
   *
   * @param string $name
   *   The entity type id.
   * @param string $entity_type_id
   *   The entity type id.
   * @param string $bundle
   *   The bundle.
   * @param string $view_mode
   *   The view mode.
   * @param \Drupal\Core\Entity\Display\EntityViewDisplayInterface|NULL $display
   *   If the caller already has the correct display, it can optionally be
   *   passed in here so the Panelizer service doesn't have to look it up;
   *   otherwise, this argument can bo omitted.
   *
   * @return \Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant
   *   The default Panels display named 'default'.
   */
  public function getDefaultPanelsDisplay($name, $entity_type_id, $bundle, $view_mode, EntityViewDisplayInterface $display = NULL);

  /**
   * @param $name
   * @param $entity_type_id
   * @param $bundle
   * @param $view_mode
   * @param \Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant $panels_display
   * @return mixed
   */
  public function setDefaultPanelsDisplay($name, $entity_type_id, $bundle, $view_mode, PanelsDisplayVariant $panels_display);

  /**
   * Checks if the given entity type, bundle and view mode are panelized.
   *
   * @param string $entity_type_id
   *   The entity type id.
   * @param string $bundle
   *   The bundle.
   * @param string $view_mode
   *   The view mode.
   * @param \Drupal\Core\Entity\Display\EntityViewDisplayInterface|NULL $display
   *   If the caller already has the correct display, it can optionally be
   *   passed in here so the Panelizer service doesn't have to look it up;
   *   otherwise, this argument can bo omitted.
   *
   * @return bool
   *   TRUE if panelized; otherwise FALSE.
   */
   public function isPanelized($entity_type_id, $bundle, $view_mode, EntityViewDisplayInterface $display = NULL);

}
