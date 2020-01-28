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

    restore: function(calendarId, options) {
      options = options || {'restorePastDates': false};
      var attrs = this.localStorage.find({id: calendarId});
      this.add(new this.model(attrs));
      var model = this.get(calendarId);
      if (options.restorePastDates === false) {
        model.removePastDates();
      }
      return model;
    },

  });
})(jQuery, Backbone);