/*! AdminLTE userfrosting.js
 * ================
 * Userfrosting JS file for AdminLTE v2. This file
 * should be included in all pages. It controls some layout
 * options and implements functions related to Userfrosting.
 *
 * @Author  Louis Charette
 * @Author  Amos Folz
 * @Support <https://github.com/userfrosting>
 * @license MIT <http://opensource.org/licenses/MIT>
 */


// Add class 'active' to

var url = window.location;
// for sidebar menu entirely but not cover treeview
$('ul.sidebar-menu a').filter(function() {
    return this.href != url;
}).parent().removeClass('active');

// for sidebar menu entirely but not cover treeview
$('ul.sidebar-menu a').filter(function() {
    return this.href == url;
}).parent().addClass('active');

// for treeview
$('ul.treeview-menu a').filter(function() {
    return this.href == url;
}).parentsUntil(".sidebar-menu > .treeview-menu").addClass('active');









$(function() {
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