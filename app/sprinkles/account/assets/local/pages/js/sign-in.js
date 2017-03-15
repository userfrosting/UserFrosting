/**
 * Page-specific Javascript file.  Should generally be included as a separate asset bundle in your page template.
 * example: {{ assets.js('js/pages/sign-in-or-register') | raw }}
 *
 * This script depends on validation rules specified in components/page.js.twig.
 *
 * Target page: account/sign-in
 */
$(document).ready(function() {
    /**
     * If there is a redirect parameter in the query string, redirect to that page.
     * Otherwise, if there is a UF-Redirect header, redirect to that page.
     * Otherwise, redirect to the home page.
     */
    function redirectOnLogin(jqXHR) {
        var components = URI.parse(window.location.href);
        var query = URI.parseQuery(components['query']);

        if (query && query['redirect']) {
            window.location.replace(site.uri.public + '/' + query['redirect']);
        } else if (jqXHR.getResponseHeader('UF-Redirect')) {
            window.location.replace(jqXHR.getResponseHeader('UF-Redirect'));
        } else {
            window.location.replace(site.uri.public);
        }
    }

    $("#sign-in").ufForm({
        validators: page.validators.login,
        msgTarget: $("#alerts-page")
    }).on("submitSuccess.ufForm", function(event, data, textStatus, jqXHR) {
        redirectOnLogin(jqXHR);
    });
});
