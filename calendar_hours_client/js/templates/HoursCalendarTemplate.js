HoursCalendarTemplate = _.template(
  "<div>" +
    "<% if (!display.noDate) { %>" +
      "<span><%= date %></span>" +
    "<% } %>" +
    "<ul>" +
      "<% if (display.today === 'live') { %>" +
        "<li>" +
          "<% if (status === 'open') { %>" +
            "Closes <%= closesNext %>." +
          "<% } else { %>" +
            "Opens <%= opensNext %>." +
          "<% } %>" +
        "</li>" +
      "<% } else if (display.today === 'live-c' && status === 'open') { %>" +
        "<li>" +
            "Closes <%= closesNext %>." +
        "</li>" +
      "<% } else if (display.today === 'live-co' && status === 'open') { %>" +
        "<li>" +
          "Closes <%= closesNext %>. Reopens <%= opensNext %>." +
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