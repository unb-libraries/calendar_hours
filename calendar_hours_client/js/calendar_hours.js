(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.calendarHours = {
    attach: function attach(context, settings) {

      Drupal.calendarHours.collection = Drupal.calendarHours.collection || new Drupal.calendarHours.HoursCalendarCollection();

      // initialize views
      $(context).find('*[data-ch-id]').once('calendarHours').each(function(index, container) {
          var calendarId = $(container).data('ch-id');

          var model = undefined;
          if (Drupal.calendarHours.collection.has(calendarId)) {
            model = Drupal.calendarHours.collection.get({id: calendarId});
            console.log("Loaded from Collection.");
          } else {
            if (Drupal.calendarHours.collection.canRestore(calendarId)) {
              model = Drupal.calendarHours.collection.restore(calendarId);
              console.log("Restored from LocalStorage.");
            } else {
              model = new Drupal.calendarHours.HoursCalendarModel({id: calendarId});
              Drupal.calendarHours.collection.add(model);
              model.save();
              console.log("Created from scratch.");
            }
          }

          Drupal.calendarHours.views[index] = new Drupal.calendarHours.HoursCalendarView({
            el: container,
            model: model
          });
      });

      // refresh models
      $.each(Drupal.calendarHours.collection.models, function(index, model) {
        model.refreshHours();
        setInterval(function() {
          model.refreshHours();
        }, drupalSettings.calendarHours.refreshInterval);
      });

    }
  };

  Drupal.calendarHours = Drupal.calendarHours || {
    views: {},
  };

})(jQuery, Drupal, drupalSettings);
