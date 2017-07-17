/**
 * Contains code that should be initialized in all UF pages.
 */
 
$(document).ready(function() {
   
    // Override Bootstrap's tendency to steal focus from child elements in modals (such as select2).
    // See https://github.com/select2/select2/issues/1436#issuecomment-21028474
    $.fn.modal.Constructor.prototype.enforceFocus = function() {};

    // Link all copy buttons
    $.uf.copy('.js-copy-trigger');

    // Display page alerts
    if ($("#alerts-page").length) {
        $("#alerts-page").ufAlerts();
        $("#alerts-page").ufAlerts('fetch').ufAlerts('render');
    }

    // Set any JS variables that might be missing from config.js.twig
    if (typeof site.uf_table === 'undefined') {
        site['uf_table'] = {
            use_loading_transition: true
        };
    }
});
