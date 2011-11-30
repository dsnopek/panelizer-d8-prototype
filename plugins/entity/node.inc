<?php
/**
 * @file
 * Definition of the node plugin.
 */

$plugin = array(
  'handler' => 'PanelizerEntityNode',
  'entity path' => 'node/%node',
  'hooks' => array(
    'menu' => TRUE,
    'permission' => TRUE,
    'panelizer_defaults' => TRUE,
  ),
);
