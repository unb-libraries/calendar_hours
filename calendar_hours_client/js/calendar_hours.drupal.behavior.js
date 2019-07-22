(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.calendarHours = {
    attach: function attach(context, settings) {
      Drupal.calendarHours.context = context;
      Drupal.calendarHours.settings = {
        baseUrl: drupalSettings.calendarHours.baseUrl,
        refreshInterval: drupalSettings.calendarHours.refreshInterval,
        maxAgeUntilUpdateFromRemote: drupalSettings.calendarHours.maxAge,
      };
      Drupal.calendarHours.initialize();
    },
  };

  Drupal.calendarHours = calendarHours;

})(jQuery, Drupal, drupalSettings);
