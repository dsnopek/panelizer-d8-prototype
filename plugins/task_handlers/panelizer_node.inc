<?php
/**
 * @file
 *
 * This is the task handler plugin to handle an entity view.
 */

// Plugin definition
$plugin = array(
  // is a 'context' handler type, meaning it supports the API of the
  // context handlers provided by ctools context plugins.
  'handler type' => 'context',
  // may NOT be added up front.
  'visible' => FALSE,
  // Administrative fields.
  'title' => t('Panelizer'),
  'admin summary' => 'panelizer_panelizer_task_admin_summary',
  'operations' => array(),
  // Callback to render the data.
  'render' => 'panelizer_panelizer_task_render',
  'test' => 'panelizer_panelizer_task_test',
);

/**
 * Callback to provide administrative summary of the task handler.
 */
function panelizer_panelizer_task_admin_summary($handler, $task, $subtask, $page, $show_title = TRUE) {
  $output = '';

  $output .= '<div class="clear-block">';
  if ($show_title) {
    // Get the operations
    $operations = page_manager_get_operations($page);

    // Get operations for just this handler.
    $operations = $operations['handlers']['children'][$handler->name]['children']['actions']['children'];
    $args = array('handlers', $handler->name, 'actions');
    $rendered_operations = page_manager_render_operations($page, $operations, array(), array('class' => array('actions')), 'actions', $args);

    $output .= '<div class="handler-title clear-block">';
    $output .= '<div class="actions handler-actions">' . $rendered_operations['actions'] . '</div>';
    $output .= '<span class="title-label">' . t('Panelizer') . '</span>';
    $output .= '</div>';
  }

  $access = t('This variant will be selected if the entity being viewed is panelized. This variant must be enabled and selected for panelizer to work!');

  $rows[] = array(
    array(
      'class' => array('page-summary-label'),
      'data' => t('Selection rule'),
    ),
    array(
      'class' => array('page-summary-data'),
      'data' => $access,
    ),
    array('class' => array('page-summary-operation'), ''),
  );

  $output .= theme('table', array('header' => array(), 'rows' => $rows, 'attributes' => array('class' => array('page-manager-handler-summary'))));
  $output .= '</div>';

  return $output;
}

/**
 * Render a entity that has been panelized.
 */
function panelizer_panelizer_task_render($handler, $base_contexts, $args, $test = TRUE) {
  // Get the context this is viewing; figure out what entity it is and load
  // the right plugin.
  ctools_include('context');
  $context = reset($base_contexts);
  if (!$context) {
    return FALSE;
  }

  if (empty($context->data)) {
    return FALSE;
  }

  if (strpos($context->plugin, ':') === FALSE) {
    return FALSE;
  }

  list($plugin_type, $entity_type) = explode(':', $context->plugin);
  if ($plugin_type != 'entity') {
    return FALSE;
  }

  if ($handler = panelizer_entity_plugin_get_handler($entity_type)) {
    return $handler->render_entity($context->data, $args);
  }
}

/**
 * Determine if the panelizer task handler should fire.
 *
 * This returns true if the configured entity is panelized and has
 * a display.
 */
function panelizer_panelizer_task_test($handler, $base_contexts) {
  if (empty($base_contexts)) {
    return;
  }

  $context = reset($base_contexts);
  if (empty($context->data)) {
    return;
  }

  $entity = &$context->data;
  return !empty($entity->panelizer->display);
}
