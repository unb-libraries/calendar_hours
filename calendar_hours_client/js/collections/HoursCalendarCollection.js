/**
 * @file
 * A Backbone Collection of HoursCalendars.
 */

(function ($, Backbone, Drupal, drupalSettings) {
  /**
   * Backbone collection of HoursCalendars.
   *
   * @constructor
   *
   * @augments Backbone.Collection
   */
  Drupal.calendarHours.HoursCalendarCollection = Backbone.Collection.extend({

    model: Drupal.calendarHours.HoursCalendarModel,

    localStorage: new Backbone.LocalStorage("hours-calendars"),

    has: function(id) {
      return this.get(id) !== undefined;
    },

    canRestore: function(calendarId) {
      var attrs = this.localStorage.find({id: calendarId});
      return attrs !== null;
    },

    restore: function(calendarId) {
      var attrs = this.localStorage.find({id: calendarId});
      var model = new this.model(attrs);
      this.add(model);
      return this.get(calendarId);
    }

  });
})(jQuery, Backbone, Drupal, drupalSettings);