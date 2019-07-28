/*! AdminLTE userfrosting.js
 * ================
 * Userfrosting JS file for AdminLTE v2.4.15 This file
 * should be included in all pages. It controls some layout
 * options and implements functions related to Userfrosting.
 *
 * @Author  Louis Charette
 * @Author  Amos Folz
 * @Support <https://github.com/userfrosting>
 * @license MIT <http://opensource.org/licenses/MIT>
 */

//Make sure jQuery has been loaded before app.js
if (typeof jQuery === "undefined") {
    throw new Error("AdminLTE requires jQuery");
}

/* initMenu()
 * ======
 * Activate the menu based on the url and href attr.
 *
 * @type Function
 * @Usage: $.initMenu('ul.sidebar-menu a');
 */
$.initMenu = function(searchElement) {
    var _this = this;
    var element = $(searchElement).filter(function() {
        // Strip out everything after the hash, if present
        var url_head = window.location.href.split('#', 1)[0];
        return this.href == url_head; // || url.href.indexOf(this.href) == 0   // Include this if you want to color all parent URIs as well
    }).parent();
    $(element).addClass('active');
    $(element).parents('.treeview').addClass('active');
    $(element).parents('.treeview-menu').addClass('menu-open');
};

$(function() {
    //Init menu and trees
    $.initMenu('ul.sidebar-menu a');

    // Apply select2 to all js-select2 elements
    $('.js-select2').select2({
        minimumResultsForSearch: Infinity
    });

    // Apply iCheck to all js-icheck elements
    $('.js-icheck').iCheck({
        checkboxClass: 'icheckbox_square-blue',
        radioClass: 'iradio_square-blue',
        increaseArea: '20%' // optional
    });

    // Remember the sidebar collapse state
    // See: https://github.com/almasaeed2010/AdminLTE/issues/896#issuecomment-264723101
    $('.sidebar-toggle').click(function(event) {
        event.preventDefault();
        if (Boolean(sessionStorage.getItem('sidebar-toggle-collapsed'))) {
            sessionStorage.setItem('sidebar-toggle-collapsed', '');
        } else {
            sessionStorage.setItem('sidebar-toggle-collapsed', '1');
        }
    });

});