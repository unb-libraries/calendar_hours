/**
 * @file
 * A Backbone view for an HoursResponse.
 */

(function ($, Backbone) {

  const DEFAULT_DATE_FORMAT = 'Y-MM-DD';
  const DEFAULT_TIME_FORMAT = 'hh:mm A';
  const DEFAULT_DISPLAY = 'static';

  HoursCalendarView = Backbone.View.extend({
    /**
     * Backbone view for an HoursResponse.
     *
     * @constructs
     *
     * @augments Backbone.View
     */
    refreshInterval: 60000,

    initialize: function initialize() {
      this.model.requireDate(this.getDate());
      this.listenTo(this.model, 'change:hours', this.render);
      this.listenTo(this.model, 'change:closesAt', this.render);
      this.listenTo(this.model, 'change:reopensAt', this.render);
      this.listenTo(this.model, 'change:open', this.render);
      this.render();
      let that = this;
      setInterval(function() {
        that.model.requireDate(that.getDate());
        let secondsSinceMidnight = moment("00:00:00", "HH:mm:ss").diff(moment(), 'seconds');
        if (secondsSinceMidnight <= 0 && secondsSinceMidnight >= (that.refreshInterval * -1 / 1000)) {
          that.render();
        }
      }, this.refreshInterval);
    },

    getDate: function getDate() {
      var date = this.$el.data('ch-date');
      if (date === undefined) {
        var daysFromToday = this.$el.data('ch-days') || 0;
        date = moment().add(daysFromToday, 'days').format('Y-MM-DD');
      }
      return date;
    },

    getDisplayOptions: function getDisplayOptions() {
      var date = this.getDate();
      var displayToday = DEFAULT_DISPLAY;
      var el = this.$el;

      if (!this.$el.hasClass('ch-live-am') || this.model.isOpenSinceBeforeMidnight()) {
        if (this.$el.is('.ch-live', '.ch-live-c', '.ch-live-co') && this.getDate() === moment().format('Y-MM-DD')) {
          $.each(['ch-live', 'ch-live-c', 'ch-live-co'], function(index, liveClass) {
            if (el.hasClass(liveClass)) {
              displayToday = liveClass.substr(3);
            }
          });
        }
      }

      var timeFormat = this.$el.data('ch-format-time') || DEFAULT_TIME_FORMAT;
      var dateFormat = this.$el.data('ch-format-date') || DEFAULT_DATE_FORMAT;

      var opensNextFormat = timeFormat;
      if (this.$el.hasClass('ch-live-d') || this.$el.hasClass('ch-live-do') || ((this.$el.hasClass('ch-live-nsd') || this.$el.hasClass('ch-live-nsdo')) && moment(this.model.getReopensAt()).format('Y-MM-DD HH:mm') >= moment().add(1, 'days').format('Y-MM-DD HH:mm'))) {
        opensNextFormat = dateFormat + ', ' + timeFormat;
      }

      var closesNextFormat = timeFormat;
      if (this.$el.hasClass('ch-live-d') || this.$el.hasClass('ch-live-dc') || ((this.$el.hasClass('ch-live-nsd') || this.$el.hasClass('ch-live-nsdc')) && moment(this.model.getClosesAt()).format('Y-MM-DD HH:mm') >= moment().add(1, 'days').format('Y-MM-DD HH:mm'))) {
        closesNextFormat = dateFormat + ', ' + timeFormat;
      }

      return {
        timeFormat: timeFormat,
        dateFormat: dateFormat,
        noDate: !!this.$el.hasClass('ch-nd'),
        liveDateFormat: {
          opensNextFormat: opensNextFormat,
          closesNextFormat: closesNextFormat,
        },
        today: displayToday,
      }
    },

    // TODO: ch-live-am: renders all live options only if open and between now midnight and closing time, otherwise regular
    // TODO: render date in live updates, if closesNext/opensNext date is after current date
    render: function render() {
      var date = this.getDate();
      var displayOptions = this.getDisplayOptions();
      var hours = [];
      $.each(this.getHours()[date], function(index, block) {
        hours.push({
          from: moment(block.from).format(displayOptions.timeFormat),
          to: moment(block.to).format(displayOptions.timeFormat)
        });
      });

      this.$el.html(HoursCalendarTemplate({
        display: displayOptions,
        date: moment(date).format(displayOptions.dateFormat),
        status: this.model.isOpenNow() ? 'open' : 'closed',
        opensNext: moment(this.model.getReopensAt()).format(displayOptions.liveDateFormat.opensNextFormat),
        closesNext: moment(this.model.getClosesAt()).format(displayOptions.liveDateFormat.closesNextFormat),
        hours: hours,
      }));
    },

    getHours: function getHours() {
      return this.model.getHours();
    }
  });
})(jQuery, Backbone);