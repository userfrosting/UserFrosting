
$(document).ready(function() {
    // define tablesorter pager options
    var pagerOptions = {
      // target the pager markup - see the HTML block below
      container: $('.pager'),
      // output string - default is '{page}/{totalPages}'; possible variables: {page}, {totalPages}, {startRow}, {endRow} and {totalRows}
      output: '{startRow} - {endRow} / {filteredRows} ({totalRows})',
      // if true, the table will remain the same height no matter how many records are displayed. The space is made up by an empty
      // table row set to a height to compensate; default is false
      fixedHeight: true,
      // remove rows from the table to speed up the sort of large tables.
      // setting this to false, only hides the non-visible rows; needed if you plan to add/remove rows with the pager enabled.
      removeRows: false,
      // go to page selector - select dropdown that sets the current page
      cssGoto: '.gotoPage'
    };
    
    // Initialize tablesorters
    $('.tablesorter-bootstrap').tablesorter({
        debug: false,
        theme: 'bootstrap',
        widthFixed: true,
        widgets: ['filter']
    }).tablesorterPager(pagerOptions);
    
    // Initialize bootstrap switches, if enabled
    if (jQuery().bootstrapSwitch){
        $('.bootstrapswitch').bootstrapSwitch();
    } else {
        console.error("The bootstrap-switch plugin has not been added.");
    }
    
    // Initialize select2 dropdowns, if enabled
    if (jQuery().select2){
        $('.select2').select2();
    } else {
        console.error("The select2 plugin has not been added.");
    }

    // Initialize form validation, if included
    if (jQuery().formValidation){
        $('form').formValidation({
            live: 'enabled',
            message: 'This value is not valid',
            button: {
                // The submit buttons selector
                selector: '[type="submit"]'
            }
        });
    }
        
});
