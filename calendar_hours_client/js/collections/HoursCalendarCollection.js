/**
 * @file
 * A Backbone Collection of HoursCalendars.
 */

(function ($, Backbone) {
  /**
   * Backbone collection of HoursCalendars.
   *
   * @constructor
   *
   * @augments Backbone.Collection
   */
  HoursCalendarCollection = Backbone.Collection.extend({

    model: HoursCalendarModel,

    localStorage: new Backbone.LocalStorage("hours-calendars"),

    remoteUrl: window.location.protocol + "//" + window.location.host + "/api/hours/",

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
})(jQuery, Backbone);