<?php

/**
 * @file
 * Contains \Drupal\panelizer\Plugin\PanelizerEntity;
 */

namespace Drupal\panelizer\Plugin\PanelizerEntity;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\panelizer\Plugin\PanelizerEntityBase;
use Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant;

/**
 * Panelizer entity plugin for integrating with nodes.
 *
 * @PanelizerEntity("node")
 */
class PanelizerNode extends PanelizerEntityBase {

  /**
   * {@inheritdoc}
   */
  public function getDefaultDisplay(EntityViewDisplayInterface $display, $bundle, $view_mode) {
    $panels_display = parent::getDefaultDisplay($display, $bundle, $view_mode);

    if ($display->getComponent('links')) {
      // @todo: add block for node links.
    }

    // Add Language field text element to node render array.
    if ($display->getComponent('langcode')) {
      // @todo: add block for node language.
    }

    return $panels_display;
  }

  /**
   * {@inheritdoc}
   */
  public function alterBuild(array &$build, EntityInterface $entity, PanelsDisplayVariant $display, $view_mode) {
    /** @var $entity \Drupal\node\Entity\Node */
    parent::alterBuild($build, $entity, $display, $view_mode);

    if ($entity->id()) {
      $build['#contextual_links']['node'] = array(
        'route_parameters' =>array('node' => $entity->id()),
        'metadata' => array('changed' => $entity->getChangedTime()),
      );
    }
  }

}