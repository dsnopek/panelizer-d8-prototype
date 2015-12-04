<?php

/**
 * @file
 * Contains \Drupal\panelizer\Annotation\PanelizerEntity.
 */

namespace Drupal\panelizer\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Panelizer entity item annotation object.
 *
 * @see \Drupal\panelizer\Plugin\PanelizerEntityManager
 * @see plugin_api
 *
 * @Annotation
 */
class PanelizerEntity extends Plugin {

  /**
   * The plugin ID.
   *
   * This should be the same as the entity type id that this plugin is for.
   *
   * @var string
   */
  public $id;

}
