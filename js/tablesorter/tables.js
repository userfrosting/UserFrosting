// Parser for sorting integers, timestamps, etc based on metadata from <td> attributes
// Adapted from http://mottie.github.io/tablesorter/docs/example-parsers-advanced.html
$.tablesorter.addParser({
    // set a unique id
    id: 'metanum',
    is: function(s) {
      // return false so this parser is not auto detected
      return false;
    },
    format: function(s, table, cell, cellIndex) {
      var $cell = $(cell);
      // returns metadata, or cell text (s) if it doesn't exist
      return $cell.attr('data-num') || s;
      
    },
    // set type to numeric
    type: 'numeric'
});

$.tablesorter.addParser({
    // set a unique id
    id: 'metatext',
    is: function(s) {
      // return false so this parser is not auto detected
      return false;
    },
    format: function(s, table, cell, cellIndex) {
      var $cell = $(cell);
      // returns metadata, or cell text (s) if it doesn't exist
      return $cell.attr('data-text') || s;
      
    },

    type: 'text'
});



