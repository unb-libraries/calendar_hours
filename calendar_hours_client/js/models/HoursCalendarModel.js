/**
 * @file
 * A Backbone Model for an HoursResponse.
 */

(function ($, Backbone, Drupal, drupalSettings) {
  /**
   * Backbone model for an HoursResponse.
   *
   * @constructor
   *
   * @augments Backbone.Model
   */
  Drupal.calendarHours.HoursCalendarModel = Backbone.Model.extend({
    /**
     * @type {object}
     *
     * @prop id
     * @prop title
     * @prop startDate
     * @prop endDate
     * @prop hours
     */
    defaults: {
      id: "",
      title: "",
      startDate: moment().format('Y-MM-DD'),
      endDate: moment().format('Y-MM-DD'),
      reopensAt: "",
      closesAt: "",
      hours: {},
    },

    urlRoot: drupalSettings.calendarHours.baseUrl,

    url: function() {
      var url = this.urlRoot + this.get('id');
      var from = this.get('startDate');
      var to = this.get('endDate');
      return url + '?from=' + from + '&to=' + to + '&format=groupby:start-date';
    },

    requireDate: function(date) {
      var startDate = this.get('startDate');
      if (date < startDate) {
        this.set('startDate', date);
      }
      var endDate = this.get('endDate');
      if (date > endDate) {
        this.set('endDate', date);
      }
    },

    getHours: function() {
      return this.get('hours');
    },

    isOpenNow: function() {
      var today = moment().format('Y-MM-DD');
      if (!this.isOpenAt(today)) {
        return false;
      }
      return this.currentBlock() !== undefined;
    },

    isOpenSinceBeforeMidnight: function() {
      if (!this.isOpenNow()) {
        return false;
      }
      var currentBlock = this.currentBlock();
      var today = moment().format('Y-MM-DD');
      var yesterday = moment().add(-1, 'days').format('Y-MM-DD');

      return moment(currentBlock.from).format('Y-MM-DD') === yesterday && moment(currentBlock.to).format('Y-MM-DD') === today;
    },

    isOpenAt: function(date) {
      return this.get('hours')[date] !== undefined;
    },

    currentBlock: function() {
      var now = moment().format('X');
      var currentBlock = undefined;
      $.each(this.get('hours'), function (date, block) {
        let from = moment(block.from).format('X');
        let to = moment(block.to).format('X');
        if (from <= now && now <= to) {
          currentBlock = block;
        }
      });
      return currentBlock;
    },

    getReopensAt: function() {
      return this.get('reopensAt');
    },

    getClosesAt: function() {
      return this.get('closesAt');
    },

    refreshHours: function() {
      this.fetch();
    },

  });
})(jQuery, Backbone, Drupal, drupalSettings);