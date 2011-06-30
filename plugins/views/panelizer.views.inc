<?php

/**
 * @file Panelizer views integration code.
 */

function panelizer_views_plugins() {
  $path = drupal_get_path('module', 'panelizer') . '/plugins/views';
  return array(
    'row' => array(
      'panelizer_node_view' => array(
        'title' => t('Panelizer display'),
        'help' => t('Render nodes using the panels display for any that have been panelized.'),
        'handler' => 'panelizer_plugin_row_panelizer_node_view',
        'parent' => 'node',
        'base' => array('node'), // only works with 'node' as base.
        'path' => $path,
        'uses options' => TRUE,
        'type' => 'normal',
      ),
    ),
  );
}
