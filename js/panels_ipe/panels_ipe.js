/**
 * @file
 * Entry point for the Panelizer IPE customizations.
 */

(function ($, _, Backbone, Drupal) {

  'use strict';

  Drupal.panelizer = Drupal.panelizer || {};

  /**
   * @namespace
   */
  Drupal.panelizer.panels_ipe = {};

  /**
   * Make customizations to the Panels IPE for Panelizer.
   */
  Backbone.on('PanelsIPEInitialized', function() {
    // Disable the normal save event.
    Drupal.panels_ipe.app_view.stopListening(Drupal.panels_ipe.app.get('saveTab'), 'change:active');

    // Add a new view for the save button to the TabsView.
    Drupal.panels_ipe.app_view.tabsView.tabViews['save'] = new Drupal.panelizer.panels_ipe.SaveTabView({
      model: Drupal.panels_ipe.app_view.model
    });
  });

}(jQuery, _, Backbone, Drupal));
