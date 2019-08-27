var calendarHours = {
  collection: undefined,
  models: {},
  views: {},
  context: undefined,

  settings: {
    baseUrl: window.location.protocol + "//" + window.location.host + "/api/hours/",
    refreshInterval: 60000,
    maxAgeUntilUpdateFromRemote: 900,
  },

  initialize: function () {
    this.collection = this.collection || new HoursCalendarCollection();
    this.collection.remoteUrl = this.settings.baseUrl;
    var that = this;
    jQuery(this.context).find('*[data-ch-id]').each(function(index, container) {
      var calendarId = jQuery(container).data('ch-id');
      that.models[calendarId] = that.models[calendarId] || that.loadOrCreateModel(calendarId);
      that.views[index] = new HoursCalendarView({
        el: container,
        model: that.models[calendarId],
      });
      that.views[index].refreshInterval = that.settings.refreshInterval;
    });

    // date only containers, for multi-column tables
    jQuery(this.context).find('.ch-date-only').each(function(index, container) {
      var date = jQuery(container).data('ch-date');
      if (date === undefined) {
        var daysFromToday = jQuery(container).data('ch-days') || 0;
        date = moment().add(daysFromToday, 'days').format('Y-MM-DD');
      }
      var dateFormat = jQuery(container).data('ch-format-date');
      console.log(container);
      jQuery(container).html(moment(date).format(dateFormat));
    });

    jQuery.each(this.models,function (index, model) {
      model.refreshHours();
    });

  },

  loadOrCreateModel: function (calendarId) {
    var model = undefined;
    if (this.collection.has(calendarId)) {
      model = this.collection.get({id: calendarId});
    } else {
      if (this.collection.canRestore(calendarId)) {
        model = this.collection.restore(calendarId);
      } else {
        model = new HoursCalendarModel({
          id: calendarId,
        });
        this.collection.add(model);
        model.save();
      }
      model.maxAge = this.settings.maxAgeUntilUpdateFromRemote;
    }
    model.setAutoRefresh(this.settings.refreshInterval);
    return model;
  },

};
