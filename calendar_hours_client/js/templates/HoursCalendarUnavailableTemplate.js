HoursCalendarUnavailableTemplate = _.template(
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

  "<span>" +
    "Unavailable" +
  "</span>" +

  "<% if (wrapper === 'tr') { %>" +
    "</td>" +
  "<% } %>"
);