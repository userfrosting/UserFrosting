/**
 * Page-specific Javascript file.  Should generally be included as a separate asset bundle in your page template.
 * example: {{ assets.js('js/pages/sign-in-or-register') | raw }}
 *
 * This script depends on __
 *
 * Target page: users
 */

$(document).ready(function() {
    
    $("#widget-users").ufTable({
        dataUrl: site.uri.public + "/api/users",
        DEBUG: true
    });
    

});
