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
    console.log(this.context);
    jQuery(this.context).find('*[data-ch-id]').once('calendarHours').each(function(index, container) {
      var calendarId = jQuery(container).data('ch-id');
      that.models[calendarId] = that.models[calendarId] || that.loadOrCreateModel(calendarId);
      that.models[calendarId].maxAge = that.settings.maxAgeUntilUpdateFromRemote;
      that.views[index] = new HoursCalendarView({
        el: container,
        model: that.models[calendarId],
      });
      that.views[index].refreshInterval = that.settings.refreshInterval;
    });
    jQuery.each(this.models, function(calendarId, model) {
      model.setAutoRefresh(true, that.settings.maxAgeUntilUpdateFromRemote);
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
    }
    return model;
  },

};
