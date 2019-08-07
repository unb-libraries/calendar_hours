HoursCalendarTemplate = _.template(
  "<% if (!display.noDate) { %>" +
    "<% if (wrapper === 'tr') { %>" +
      "<td><%= date %></td>" +
    "<% } %>" +
    "<% if (wrapper === 'div' || wrapper === 'p' || wrapper === 'span') { %>" +
      "<span><%= date %></span>" +
    "<% } %>" +
  "<% } %>" +
  "<% if (wrapper === 'tr') { %>" +
    "<td>" +
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
        "Closes <%= closesNext %>. Opens <%= opensNext %>." +
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
  "<% if (wrapper === 'tr') { %>" +
    "</td>" +
  "<% } %>"
);