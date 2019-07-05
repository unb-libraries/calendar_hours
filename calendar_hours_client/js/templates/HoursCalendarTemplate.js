HoursCalendarTemplate = _.template(
  "<div>" +
    "<% if (!display.noDate) { %>" +
      "<span><%= date %></span>" +
    "<% } %>" +
    "<ul>" +
      "<% if (display.today === 'live') { %>" +
        "<li>" +
          "<% if (status === 'open') { %>" +
            "Open until <%= closesNext %>." +
          "<% } else { %>" +
            "Closed until <%= opensNext %>." +
          "<% } %>" +
        "</li>" +
      "<% } else if (display.today === 'live-c' && status === 'open') { %>" +
        "<li>" +
            "Open until <%= closesNext %>." +
        "</li>" +
      "<% } else if (display.today === 'live-co' && status === 'open') { %>" +
        "<li>" +
          "Open until <%= closesNext %>. Reopens <%= opensNext %>." +
        "</li>" +
      "<% } else { %>" +
        "<% if (hours.length > 0) { %>" +
          "<% _.forEach(hours, function(block) { %>" +
            "<li>" +
              "<%= block.from %> - <%= block.to %>" +
            "</li>" +
          "<% }) %>" +
        "<% } else { %>" +
          "<li>" +
            "Closed" +
          "</li>" +
        "<% } %>" +
      "<% } %>" +
    "</ul>" +
  "</div>"
);