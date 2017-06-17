/*! AdminLTE userfrosting.js
 * ================
 * Userfrosting JS file for AdminLTE v2. This file
 * should be included in all pages. It controls some layout
 * options and implements functions related to Userfrosting.
 *
 * @Author  Louis Charette
 * @Support <https://github.com/userfrosting>
 * @license MIT <http://opensource.org/licenses/MIT>
 */

//Make sure jQuery has been loaded before app.js
if (typeof jQuery === "undefined") {
  throw new Error("AdminLTE requires jQuery");
}

/* Tree_UF()
* ======
* Overwrite the default behavior for UF menu compatibility
*
* @type Function
* @Usage: $.AdminLTE.tree_UF('.sidebar')
*/
$.AdminLTE.tree_UF = function (menu) {
  var _this = this;
  var animationSpeed = $.AdminLTE.options.animationSpeed;
  $(document).off('click', menu + ' li a').on('click', menu + ' li a', function (e) {

    //Get the clicked link and the next element
    var $this = $(this);
    var checkElement = $this.next();

    //Check if the next element is a menu and is visible
    if ((checkElement.is('.treeview-menu')) && (checkElement.is(':visible')) && (!$('body').hasClass('sidebar-collapse'))) {
      //Close the menu
      checkElement.slideUp(animationSpeed, function () {
        checkElement.removeClass('menu-open');
        //Fix the layout in case the sidebar stretches over the height of the window
        //_this.layout.fix();
      });
      checkElement.parent("li").removeClass("active");
    }
    //If the menu is not visible
    else if ((checkElement.is('.treeview-menu')) && (!checkElement.is(':visible'))) {
      //Get the parent menu
      var parent = $this.parents('ul').first();
      //Close all open menus within the parent
      var ul = parent.find('ul:visible').slideUp(animationSpeed);
      //Remove the menu-open class from the parent
      ul.removeClass('menu-open');
      //Get the parent li
      var parent_li = $this.parent("li");

      //Open the target menu and add the menu-open class
      checkElement.slideDown(animationSpeed, function () {

        //Add the class active to the parent li
        checkElement.addClass('menu-open');
        parent.find('li.treeview.active').removeClass('active'); //<<-- Overwrite here. Otherwise, the menu href based active will be removed
        parent_li.addClass('active');

        //Fix the layout in case the sidebar stretches over the height of the window
        _this.layout.fix();
      });
    }
    //if this isn't a link, prevent the page from being redirected
    if (checkElement.is('.treeview-menu')) {
      e.preventDefault();
    }
  });
};

/* initMenu()
* ======
* Activate the menu based on the url and href attr.
*
* @type Function
* @Usage: $.AdminLTE.initMenu('.sidebar')
*/
$.AdminLTE.initMenu = function (searchElement) {
    var _this = this;
    var element = $(searchElement).filter(function() {
        // Strip out everything after the hash, if present
        var url_head = window.location.href.split('#', 1)[0];
        return this.href == url_head;  // || url.href.indexOf(this.href) == 0   // Include this if you want to color all parent URIs as well
    }).parent();
    $(element).addClass('active');
    $(element).parents('.treeview').addClass('active');
    $(element).parents('.treeview-menu').addClass('menu-open');
};

$(function() {
    //Init menu and trees
    $.AdminLTE.initMenu('ul.sidebar-menu a');
    $.AdminLTE.tree_UF('.sidebar');

    // Apply select2 to all js-select2 elements
    $('.js-select2').select2({ minimumResultsForSearch: Infinity });

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