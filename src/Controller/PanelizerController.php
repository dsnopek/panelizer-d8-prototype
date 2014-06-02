<?php

/**
 * @file
 * Contains \Drupal\panelizer\Controller\PanelizerController.
 */

namespace Drupal\panelizer\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for Panelizer routes.
 */
class PanelizerController extends ControllerBase {
  /**
   * Allows the user to edit the current node's Panelizer Entity.
   *
   * @todo: This should list all the display modes and take the user to another
   *        page to actually edit it.
   *
   */
  public function nodePanelizer() {
    // TODO: Find the an existing PanelizerContentEntity for this node; if none
    //       exists, create one.
    // TODO: Show the UI from page_manager for editing the the first
    //       PageVariant on the PanelizerContentEntity.

    // For now, just show the form for adding a variant.
    $page = entity_create('panelizer');
    $page->save();
    $page_variant_id = $page->getPrimaryPageVariantId();
    return \Drupal::formBuilder()->getForm('\Drupal\page_manager\Form\PageVariantEditForm', $page, $page_variant_id);
  }
}
