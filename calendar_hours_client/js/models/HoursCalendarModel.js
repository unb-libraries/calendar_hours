/**
 * @file
 * A Backbone Model for an HoursResponse.
 */

(function ($, Backbone) {
  /**
   * Backbone model for an HoursResponse.
   *
   * @constructor
   *
   * @augments Backbone.Model
   */
  HoursCalendarModel = Backbone.Model.extend({
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
      startDate: moment().subtract(1, 'days').format('Y-MM-DD'),
      endDate: moment().add(1, 'days').format('Y-MM-DD'),
      reopensAt: "",
      closesAt: "",
      hours: {},
      open: undefined,
      lastRefreshed: undefined,
    },

    maxAge: 0,
    autoRefresh: undefined,

    url: function() {
      var url = this.collection.remoteUrl + this.get('id');
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
      var today = moment().format('Y-MM-DD');
      if (date === today) {
        var tomorrow = moment().add(1, 'days').format('Y-MM-DD');
        this.requireDate(tomorrow);
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
      $.each(this.get('hours'), function (date, blocks) {
        $.each(blocks, function(index, block) {
          let from = moment(block.from).format('X');
          let to = moment(block.to).format('X');
          if (from <= now && now <= to) {
            currentBlock = block;
          }
        });
      });
      return currentBlock;
    },

    getReopensAt: function() {
      return this.get('reopensAt');
    },

    getClosesAt: function() {
      return this.get('closesAt');
    },

    setAutoRefresh: function(interval) {
      if (!this.autoRefresh) {
        var that = this;
        this.autoRefresh = setInterval(function() {
          that.refreshHours();
        }, interval);
      }
    },

    refreshHours: function() {
      if (this.shallRefreshFromRemote()) {
        this.fetchFromRemote();
      } else {
        this.set('open', this.isOpenNow());
        this.save();
      }
    },

    shallRefreshFromRemote: function() {
      return this.get("lastRefreshed") === undefined
        || moment(this.get("lastRefreshed")) <= moment().subtract(this.maxAge, 'seconds')
        || this.get('hours')[this.get('startDate')] === undefined
        || this.get('hours')[this.get('endDate')] === undefined;
    },

    fetchFromRemote: function() {
      let lastRefreshed = moment().format();
      $.get({
        "url": this.url(),
        "context": this,
        "success": function(jsonResponse) {
          var hours = this.mergeHours(jsonResponse.hours);
          this.set('hours', hours);
          this.set('closesAt', jsonResponse.closesAt);
          this.set('reopensAt', jsonResponse.reopensAt);
          this.set('open', this.isOpenNow());
          this.set('lastRefreshed', lastRefreshed);
          this.save();
        },
      });
    },

    mergeHours: function(newHours) {
      var mergedHours = {};
      var date = moment(this.get('startDate')).set({'hour': 0, 'minute': 0, 'second': 0});
      var endDate = moment(this.get('endDate')).set({'hour': 0, 'minute': 0, 'second': 0});
      while (endDate.diff(date, 'days') >= 0) {
        var formattedDate = date.format('Y-MM-DD');
        if (newHours[formattedDate] !== undefined) {
          mergedHours[formattedDate] = newHours[formattedDate];
        } else {
          mergedHours[formattedDate] = [];
        }
        date = date.add(1, 'days');
      }
      return mergedHours;
    },

    removePastDates: function() {
      var today = moment().set({'hour': 0, 'minute': 0, 'second': 0});
      var hours = this.get('hours');
      jQuery.each(hours, function(formattedDate, blocks) {
        var date = moment(formattedDate).set({'hour': 0, 'minute': 0, 'second': 0});
        if (date.diff(today, 'days') < -1) {
          delete hours[formattedDate];
        }
      });
      this.set('startDate', today.subtract(1, 'days').format('Y-MM-DD'));
      this.set('hours', hours);
      this.save();
    }

  });
})(jQuery, Backbone);
