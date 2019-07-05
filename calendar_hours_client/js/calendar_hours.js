(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.calendarHours = {
    attach: function attach(context, settings) {

      // initialize views
      $(context).find('*[data-ch-id]').once('calendarHours').each(function(index, view) {
          var calendarId = $(view).data('ch-id');
          var model = Drupal.calendarHours.models[calendarId];
          if (model === undefined) {
            model = Drupal.calendarHours.models[calendarId] = new Drupal.calendarHours.HoursCalendarModel({
              id: calendarId,
            });
          }
          Drupal.calendarHours.views[index] = new Drupal.calendarHours.HoursCalendarView({
            el: view,
            model: model
          });
      });

      // refresh models
      $.each(Drupal.calendarHours.models, function(calendarId, model) {
        model.refreshHours();
        setInterval(function() {
          model.refreshHours();
        }, drupalSettings.calendarHours.refreshInterval);
      });

    }
  };

  Drupal.calendarHours = Drupal.calendarHours || {
    models: {},
    views: {},
  };

})(jQuery, Drupal, drupalSettings);
