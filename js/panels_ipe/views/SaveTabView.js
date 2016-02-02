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
      alert('saveCustom');
    },

    /**
     * @type {function}
     */
    saveDefault: function () {
      alert('saveDefault');
    },

    /**
     * @constructs
     *
     * @augments Backbone.View
     *
     * @param {Object} options
     *   An object containing the following keys:
     *   - @todo...
     */
    initialize: function (options) {
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
