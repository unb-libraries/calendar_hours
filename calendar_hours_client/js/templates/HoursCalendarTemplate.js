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

    "<% if (display.today === 'live') { %>" +
      "<span>" +
        "<% if (status === 'open') { %>" +
          "Closes <%= closesNext %>." +
        "<% } else { %>" +
          "Opens <%= opensNext %>." +
        "<% } %>" +
      "</span>" +
    "<% } else if (display.today === 'live-c' && status === 'open') { %>" +
      "<span>" +
          "Closes <%= closesNext %>." +
      "</span>" +
    "<% } else if (display.today === 'live-co' && status === 'open') { %>" +
      "<span>" +
        "Closes <%= closesNext %>. Opens <%= opensNext %>." +
      "</span>" +
    "<% } else { %>" +
      "<% if (hours.length > 1) { %>" +
        "<ul>" +
          "<% _.forEach(hours, function(block) { %>" +
            "<li>" +
              "<%= block.from %> - <%= block.to %>" +
            "</li>" +
          "<% }) %>" +
        "</ul>" +
      "<% } else if (hours.length > 0) { %>" +
        "<span><%= hours[0].from %> - <%= hours[0].to %></span>" +
      "<% } else { %>" +
        "<span>" +
          "CLOSED" +
        "</span>" +
      "<% } %>" +
    "<% } %>" +
  "<% if (wrapper === 'tr') { %>" +
    "</td>" +
  "<% } %>"
);