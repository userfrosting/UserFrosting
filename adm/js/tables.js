// Parser for sorting dates based on metadata from <td> attributes
// Adapted from http://mottie.github.io/tablesorter/docs/example-parsers-advanced.html
$.tablesorter.addParser({
    // set a unique id
    id: 'metadate',
    is: function(s) {
      // return false so this parser is not auto detected
      return false;
    },
    format: function(s, table, cell, cellIndex) {
      var $cell = $(cell);
      //console.log("In metadate parser");
      // returns date timestamp, or cell text (s) if it doesn't exist
      return $cell.attr('data-date') || s;
      
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
      //console.log("In metatext parser");
      // returns date timestamp, or cell text (s) if it doesn't exist
      return $cell.attr('data-text') || s;
      
    },

    type: 'text'
});



