/**
 * @file
 * Contains Drupal.panelizer.panels_ipe.SaveTabView.
 */

(function ($, _, Backbone, Drupal) {

  'use strict';

  Drupal.panelizer.panels_ipe.SaveTabView = Backbone.View.extend(/** @lends Drupal.panelizer.panels_ipe.SaveTabView# */{

    /**
     * @type {function}
     */
    template: _.template(
      '<div class="panelizer-ipe-save-button"><a class="panelizer-ipe-save-custom" href="#">Save as custom</a></div>' +
      '<div class="panelizer-ipe-save-button"><a class="panelizer-ipe-save-default" href="#">Save as default</a></div>'
    ),

    /**
     * @type {Drupal.panels_ipe.AppModel}
     */
    model: null,

    /**
     * @type {object}
     */
    events: {
      'click .panelizer-ipe-save-custom': 'saveCustom',
      'click .panelizer-ipe-save-default': 'saveDefault'
    },

    /**
     * @type {function}
     */
    saveCustom: function () {
      this._save('panelizer_field');
    },

    /**
     * @type {function}
     */
    saveDefault: function () {
      this._save('panelizer_default');
    },

    /**
     * @type {function}
     */
    _save: function (storage_type) {
      var self = this,
          layout = this.model.get('layout');

      // Give the backend enough information to save in the correct way.
      layout.set('panelizer_save_as', storage_type);
      layout.set('panelizer_entity', drupalSettings.panelizer.entity);

      // Copied from AppView.clickSaveTab:
      if (this.model.get('saveTab').get('active')) {
        // Save the Layout and disable the tab.
        this.model.get('saveTab').set({loading: true});
        layout.save().done(function () {
          self.model.get('saveTab').set({loading: false, active: false});
          self.model.set('unsaved', false);
          //self.tabsView.render();

          // Change the storage type and id for the next save.
          drupalSettings.panels_ipe.panels_display.storage_type = storage_type;
          drupalSettings.panels_ipe.panels_display.storage_id = drupalSettings.panelizer.entity[storage_type + '_storage_id'];
        });
      }
    },

    /**
     * @constructs
     *
     * @augments Backbone.View
     *
     * @param {Object} options
     *   An object containing the following keys:
     * @param {Drupal.panels_ipe.AppModel} options.model
     *   The app state model.
     */
    initialize: function (options) {
      this.model = options.model;
    },

    /**
     * Renders the selection menu for picking Layouts.
     *
     * @return {Drupal.panelizer.panels_ipe.SaveTabView}
     *   Return this, for chaining.
     */
    render: function () {
      this.$el.html(this.template());
      return this;
    }

  });

}(jQuery, _, Backbone, Drupal));
